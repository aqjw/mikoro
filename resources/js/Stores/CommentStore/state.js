export const DEFAULT_SORTING_STATE = {
  option: 'latest',
  dir: 'desc',
};

export default () => ({
  replyTo: null,
  //
  titleId: null,
  page: 1,
  items: [],
  sorting: { ...DEFAULT_SORTING_STATE },

  options: {
    sorting: [
      { value: 'latest', title: 'Последние поступления' },
      { value: 'rating', title: 'По рейтингу' },
      // TODO: no comments yet
      // { value: 'comments', title: 'По комментариям' },
      { value: 'episodes_count', title: 'По количеству эпизодов' },
      { value: 'seasons_count', title: 'По количеству сезонов' },
    ],
  },
});
