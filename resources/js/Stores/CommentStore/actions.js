import { formatHtmlToBbcode, formatBbcodeToHtml } from '@/Utils';
import { DEFAULT_SORTING_STATE } from './state';

export default {
  $setTitleId(id) {
    this.titleId = id;
  },
  $reset() {
    this.$resetItems();
    this.$resetSorting();
    this.titleId = null;
  },
  $resetItems() {
    this.page = 1;
    this.items = [];
  },
  $resetSorting() {
    this.sorting = DEFAULT_SORTING_STATE;
  },
  $resetDraft() {
    this.draft.text = '';
    this.draft.html = '';
  },
  $setEdit(comment) {
    if (comment !== null && this.edit?.id == comment.id) {
      comment = null;
    }

    if (comment) {
      this.$setReplyTo(null);

      comment.draft = {
        text: '',
        html: formatBbcodeToHtml(comment.body),
      };
    }

    this.edit = comment;
  },
  $setReplyTo(comment) {
    // TODO: save id and author.name only
    if (comment !== null && this.replyTo?.real_id == comment.id) {
      comment = null;
    }

    if (comment) {
      this.$setEdit(null);

      const maxDepth = window.config.comments.max_depth;
      const parentId = this._findValidParentId(this.items, comment.id, maxDepth);
      comment.real_id = parentId || comment.id;
      comment.draft = {
        text: '',
        html: '',
      };
    }

    this.replyTo = comment;
  },
  _findValidParentId(items, commentId, maxDepth, currentDepth = 1) {
    for (const item of items) {
      if (item.id === commentId) {
        return currentDepth >= maxDepth ? item.parent_id : item.id;
      }

      if (item.replies && item.replies.length > 0) {
        const result = this._findValidParentId(
          item.replies,
          commentId,
          maxDepth,
          currentDepth + 1
        );
        if (result) return result;
      }
    }

    return null;
  },

  $loadComments(hasMoreClosure) {
    axios
      .get(route('upi.title.comments.get', this.titleId), {
        params: this.params,
      })
      .then(({ data }) => {
        this.page++;
        this.items.push(...data.items);
        hasMoreClosure(data.has_more);
      })
      .catch((error) => {
        console.error(error);
      });
  },
  $storeComment(params, events) {
    axios
      .post(route('upi.title.comments.store', this.titleId), params)
      .then(({ data }) => {
        const parent_id = data.comment.parent_id;
        if (parent_id) {
          const addReplyToParent = (items, parent_id, reply) => {
            return items.map((item) => {
              if (item.id === parent_id) {
                item.replies.push(reply);
              } else if (item.replies) {
                item.replies = addReplyToParent(item.replies, parent_id, reply);
              }
              return item;
            });
          };

          this.items = addReplyToParent(this.items, parent_id, data.comment);
        } else {
          this.items.unshift(data.comment);
        }

        events.success();
      })
      .catch((error) => {
        events.error(error);
      })
      .finally(() => {
        events.finish();
      });
  },
  $updateComment(events) {
    const body = formatHtmlToBbcode(this.edit.draft.html);
    axios
      .patch(route('upi.title.comments.update', this.edit.id), { body })
      .then(({ data }) => {
        const updateCommentInTree = (items) => {
          return items.map((item) => {
            if (item.id === this.edit.id) {
              item.body = body;
              item.updated_at = new Date();
            } else if (item.replies) {
              item.replies = updateCommentInTree(item.replies);
            }
            return item;
          });
        };

        this.items = updateCommentInTree(this.items);

        events.success();
        this.$setEdit(null);
      })
      .catch((error) => {
        events.error(error);
      })
      .finally(() => {
        events.finish();
      });
  },
  $deleteComment(commentId) {
    axios
      .delete(route('upi.title.comments.delete', commentId))
      .then(() => {
        const deleteRecursive = (items, id) => {
          return items.filter((item) => {
            if (item.id === id) return false;
            if (item.replies) {
              item.replies = deleteRecursive(item.replies, id);
            }
            return true;
          });
        };

        this.items = deleteRecursive(this.items, commentId);
      })
      .catch((error) => {
        console.error(error);
      })
      .finally(() => {});
  },
  $toggleReaction(commentId, reactionId, events) {
    const endpoint = route('upi.title.comments.toggle_reaction', {
      comment: commentId,
      reaction: reactionId,
    });

    axios
      .post(endpoint)
      .then(({ data }) => {
        const updateReactions = (items) => {
          return items.map((item) => {
            if (item.id === commentId) {
              const hasReaction = item.userReactions.includes(reactionId);
              if (hasReaction) {
                item.userReactions = item.userReactions.filter(
                  (rid) => rid !== reactionId
                );
              } else {
                item.userReactions.push(reactionId);
              }
              item.reactions = data.reactions;
            }

            if (item.replies) {
              item.replies = updateReactions(item.replies);
            }

            return item;
          });
        };

        this.items = updateReactions(this.items);
        events.success();
      })

      .catch((error) => {
        events.error(error);
      })
      .finally(() => {
        events.finish();
      });
  },
  $report(commentId, reasonId, events) {
    axios
      .post(
        route('upi.title.comments.report', {
          comment: commentId,
          reason: reasonId,
        })
      )
      .then(() => {
        events.success();
      })
      .catch((error) => {
        events.error(error);
      })
      .finally(() => {
        events.finish();
      });
  },
};
