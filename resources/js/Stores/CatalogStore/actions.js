import { DEFAULT_SORTING_STATE } from './state';

export default {
  $resetAll() {
    this.$resetSorting();
    this.$resetFilters();
    this.$resetPage();
  },
  $resetPage() {
    this.page = 1;
  },
  $resetItems() {
    this.items = [];
    this.has_more = true;
  },
  $resetSorting() {
    this.sorting = DEFAULT_SORTING_STATE;
  },
  $resetFilters() {
    Object.keys(this.filters).forEach((key) => {
      this.filters[key] = { incl: [], excl: [] };
    });
  },
  $setFilterValue(key, value) {
    this.filters[key].incl = [value];
  },
  $loadOptions() {
    axios
      .get(route('upi.title.filters'))
      .then(({ data }) => {
        this.options.filters.genres = data.genres;
        this.options.filters.studios = data.studios;
        this.options.filters.countries = data.countries;
        this.options.filters.translations = data.translations;
        this.options.filters.years = data.years;
      })
      .catch((error) => {
        console.error(error);
      });
  },
  $loadItems(events) {
    axios
      .get(route('upi.title.catalog'), {
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
};
