import { DEFAULT_SORTING_STATE } from './state';

export default {
  resetItems() {
    this.page = 1;
    this.items = [];
  },
  resetSorting() {
    this.sorting = DEFAULT_SORTING_STATE;
  },
  resetFilters() {
    Object.keys(this.filters).forEach((key) => {
      this.filters[key] = { incl: [], excl: [] };
    });
  },
  setFilterValue(key, value) {
    this.filters[key].incl = [value];
  },
  loadOptions() {
    axios
      .get(route('upi.title.filters'))
      .then(({ data }) => {
        this.options.filters.genres = data.genres;
        this.options.filters.studios = data.studios;
        this.options.filters.translations = data.translations;
        this.options.filters.statuses = data.statuses;
        this.options.filters.years = data.years;
      })
      .catch((error) => {
        console.error(error);
      });
  },
  loadItems(hasMoreClosure) {
    axios
      .get(route('upi.title.catalog'), {
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
};
