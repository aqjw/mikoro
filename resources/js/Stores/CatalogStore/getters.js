import { DEFAULT_SORTING_STATE } from './state';

export default {
  params() {
    return {
      page: this.page,
      filters: this.filters,
      sorting: this.sorting,
    };
  },
  activeFiltersCount() {
    return Object.values(this.filters).filter(
      (filter) => filter.incl.length || filter.excl.length
    ).length;
  },
  canReset() {
    return (
      this.activeFiltersCount ||
      JSON.stringify(DEFAULT_SORTING_STATE) !== JSON.stringify(this.sorting)
    );
  },
};
