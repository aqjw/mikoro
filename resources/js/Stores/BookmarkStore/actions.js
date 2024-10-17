export default {
  $loadItems(bookmarkId, params, events) {
    axios
      .get(route('upi.bookmarks.get', bookmarkId), { params })
      .then(({ data }) => {
        events.success(data);
      })
      .catch(({ response }) => {
        events.error(response);
      })
      .finally(() => {
        events.finish();
      });
  },
  $toggle(titleId, value, events) {
    axios
      .post(route('upi.bookmarks.toggle', titleId), { value })
      .then(() => {
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
