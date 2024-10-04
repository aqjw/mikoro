import { defineStore } from 'pinia';
import state from './state';
import getters from './getters';
import actions from './actions';

export const useCatalogStore = defineStore('catalogStore', {
  state,
  getters,
  actions,
});
