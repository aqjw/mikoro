export const DEFAULT_SORTING_STATE = {
  option: 'latest',
  dir: 'desc',
};

export default () => ({
  page: 1,
  items: [],
  sorting: { ...DEFAULT_SORTING_STATE },
  filters: {
    genres: { incl: [], excl: [] },
    studios: { incl: [], excl: [] },
    translations: { incl: [], excl: [] },
    years: { incl: [], excl: [] },
    statuses: { incl: [], excl: [] },
  },

  options: {
    sorting: [
      { value: 'latest', title: 'Последние поступления' },
      { value: 'rating', title: 'По рейтингу' },
      // TODO: no comments yet
      // { value: 'comments', title: 'По комментариям' },
      { value: 'episodes_count', title: 'По количеству эпизодов' },
      { value: 'seasons_count', title: 'По количеству сезонов' },
    ],
    filters: {
      genres: [],
      studios: [],
      translations: [],
      years: [],
      statuses: [],
    },
  },
});
