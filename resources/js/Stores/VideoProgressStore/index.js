import { defineStore } from 'pinia';
import actions from './actions.ts';
import getters from './getters';
import state from './state';

export const useVideoProgressStore = defineStore('videoProgressStore', {
  state,
  getters,
  actions,
});
