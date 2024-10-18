import { defineStore } from 'pinia';

export const useAppStore = defineStore('appStore', {
  state: () => ({
    config: {
      bookmarks: window.config.bookmarks || [],
      comments: {
        reactions: window.config.comments.reactions || [],
        reportReasons: window.config.comments.report_reasons || [],
        maxDepth: window.config.comments.max_depth || 3,
        repliesPerPage: window.config.comments.replies_per_page || 10,
        minCharacters: window.config.comments.min_characters || 1,
        maxCharacters: window.config.comments.max_characters || 1000,
      },
    },
  }),
  getters: {
    getConfig() {
      return this.config;
    },
  },
});
