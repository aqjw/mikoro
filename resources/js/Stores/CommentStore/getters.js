export default {
  params() {
    return {
      page: this.page,
      sorting: this.sorting,
    };
  },
  draftTextLength() {
    return this.draft.text.length;
  },
  editTextLength() {
    if (!this.edit) {
      return 0;
    }
    return this.edit.draft.text.length;
  },
  replyToTextLength() {
    if (!this.replyTo) {
      return 0;
    }
    return this.replyTo.draft.text.length;
  },
};
