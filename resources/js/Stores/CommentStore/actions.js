import { formatHtmlToBbcode, formatBbcodeToHtml } from '@/Utils';
import { useAppStore } from '@/Stores';

export default {
  $setTitleId(id) {
    this.titleId = id;
  },
  $resetAll() {
    this.$resetItems();
    this.$resetPage();
    this.titleId = null;
  },
  $resetPage() {
    this.page = 1;
  },
  $resetItems() {
    this.items = [];
    this.has_more = true;
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
    if (comment !== null && this.replyTo?.real_id == comment.id) {
      comment = null;
    }

    if (comment) {
      this.$setEdit(null);

      const appStore = useAppStore();
        const maxDepth = appStore.getConfig.comments.maxDepth;
      const parentId = this._findValidParentId(this.items, comment.id, maxDepth);
      comment.real_id = parentId || comment.id;
      comment.draft = { text: '', html: '' };
    }

    this.replyTo = comment;
  },
  $loadComments(events) {
    axios
      .get(route('upi.title.comments.get', this.titleId), {
        params: this.params,
      })
      .then(({ data }) => {
        if (this.page === 1) {
          this.$resetItems();
        }

        this.page++;
        this.items.push(...data.items);
        this.total = data.total;
        this.has_more = data.has_more;

        events.success();
      })
      .catch(({ response }) => {
        events.error(response);
      })
      .finally(() => {
        events.finish();
      });
  },
  $loadReplies(commentId, events) {
    const comment = this._findCommentById(this.items, commentId);
    const replies = comment?.replies || [];

    if (replies.length === 0) {
      events.finish();
      return;
    }

    const lastValidReply = [...replies].reverse().find((reply) => !reply.is_new);
    if (!lastValidReply) {
      events.error(new Error('No valid lastId found (all replies are marked as new).'));
      events.finish();
      return;
    }

    const lastId = lastValidReply.id;

    axios
      .get(
        route('upi.title.comments.replies', {
          comment: commentId,
          last: lastId,
        })
      )
      .then(({ data }) => {
        const newReplies = data.items.filter(
          (newReply) => !replies.some((reply) => reply.id === newReply.id)
        );
        comment.replies.push(...newReplies);
        events.success();
      })
      .catch(({ response }) => {
        events.error(response);
      })
      .finally(() => {
        events.finish();
      });
  },
  $storeComment(params, events) {
    axios
      .post(route('upi.title.comments.store', this.titleId), params)
      .then(({ data }) => {
        const newComment = data.comment;
        const parentId = newComment.parent_id;

        newComment.is_new = true;

        if (parentId) {
          const addReplyToParent = (items, parentId, reply) => {
            return items.map((item) => {
              if (item.id === parentId) {
                item.replies.push(reply);
              } else if (item.replies) {
                item.replies = addReplyToParent(item.replies, parentId, reply);
              }
              return item;
            });
          };

          this.items = addReplyToParent(this.items, parentId, newComment);
        } else {
          this.items.unshift(newComment);
        }

        events.success();
      })
      .catch(({ response }) => {
        events.error(response);
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
      .catch(({ response }) => {
        events.error(response);
      })
      .finally(() => {
        events.finish();
      });
  },
  $deleteComment(commentId, events) {
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
        events.success();
      })
      .catch(({ response }) => {
        events.error(response);
      })
      .finally(() => {
        events.finish();
      });
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

      .catch(({ response }) => {
        events.error(response);
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
      .catch(({ response }) => {
        events.error(response);
      })
      .finally(() => {
        events.finish();
      });
  },
  //
  _findCommentById(items, commentId) {
    for (const item of items) {
      if (item.id === commentId) {
        return item;
      }

      if (item.replies && item.replies.length > 0) {
        const result = this._findCommentById(item.replies, commentId);
        if (result) return result;
      }
    }

    return null;
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
};
