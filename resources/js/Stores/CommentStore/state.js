export default () => ({
  draft: {
    text: '',
    html: '',
  },
  edit: null,
  replyTo: null,
  //
  titleId: null,
  page: 1,
  total: 0,
  items: [],
  has_more: true,
  sorting: 'latest',

  sorting_options: [
    { value: 'latest', title: 'Новые' },
    { value: 'oldest', title: 'Старые' },
    { value: 'reactions', title: 'Популярные' },
  ],
});
