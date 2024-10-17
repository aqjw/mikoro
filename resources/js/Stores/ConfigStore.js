import { defineStore } from 'pinia';

export const useConfigStore = defineStore('configStore', {
  state: () => ({
    bookmarks: [],
    comments: {
      reactions: [],
      report_reasons: [],
      max_depth: 3,
      replies_per_page: 2,
      min_characters: 10,
      max_characters: 1000,
    },
  }),
  getters: {
    //
  },
  actions: {
    $setConfig(payload) {
      this.bookmarks = payload.bookmarks;
      this.comments = payload.comments;
    },
  },
});
