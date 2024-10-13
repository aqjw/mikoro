export const DEFAULT_SORTING_STATE = {
  option: 'latest',
  dir: 'desc',
};

export default () => ({
  page: 1,
  total: 0,
  items: [],
  has_more: true,
  sorting: { ...DEFAULT_SORTING_STATE },
  filters: {
    genres: { incl: [], excl: [] },
    studios: { incl: [], excl: [] },
    countries: { incl: [], excl: [] },
    translations: { incl: [], excl: [] },
    years: { incl: [], excl: [] },
    statuses: { incl: [], excl: [] },
  },

  options: {
    sorting: [
      { value: 'latest', title: 'Последние поступления' },
      { value: 'rating', title: 'По рейтингу' },
      { value: 'comments_count', title: 'По комментариям' },
      { value: 'episodes_count', title: 'По количеству эпизодов' },
      { value: 'seasons_count', title: 'По количеству сезонов' },
    ],
    filters: {
      genres: [],
      studios: [],
      countries: [],
      translations: [],
      years: [],
      statuses: [],
    },
  },
});
