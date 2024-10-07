import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

Array.prototype.last = function () {
  return this[this.length - 1];
};
