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
  $setReplyTo(data) {
    if (data !== null && this.replyTo?.id == data.id) {
      data = null;
    }
    this.replyTo = data;
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
  $storeComment(body, closures) {
    const parent_id = this.replyTo?.id;
    axios
      .post(route('upi.title.comments.store', this.titleId), { body, parent_id })
      .then(({ data }) => {
        this.items.unshift(data.comment);
        closures.success();
      })
      .catch((error) => {
        console.error(error);
        closures.error();
      })
      .finally(() => {
        closures.finish();
      });
  },
  $deleteComment(commentId) {
    axios
      .delete(route('upi.title.comments.delete', commentId))
      .then(() => {
        this.items = this.items.filter(({ id }) => id != commentId);
      })
      .catch((error) => {
        console.error(error);
      })
      .finally(() => {
        //
      });
  },
  $toggleReaction(commentId, reactionId, closures) {
    const endpoint = route('upi.title.comments.toggle_reaction', {
      comment: commentId,
      reaction: reactionId,
    });

    axios
      .post(endpoint)
      .then(({ data }) => {
        this.items = this.items.map((item) => {
          if (item.id === commentId) {
            const hasReaction = item.userReactions.includes(reactionId);

            if (hasReaction) {
              item.userReactions = item.userReactions.filter((rid) => rid !== reactionId);
            } else {
              item.userReactions.push(reactionId);
            }

            item.reactions = data.reactions;
          }
          return item;
        });
        closures.success();
      })

      .catch((error) => {
        console.error(error);
        closures.error();
      })
      .finally(() => {
        closures.finish();
      });
  },
};
