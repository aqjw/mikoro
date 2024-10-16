export default {
  setNotificationsUnread(payload) {
    this.unreadCount = payload;
  },
  $loadItems(events) {
    axios
      .get(route('upi.notifications.get'))
      .then(({ data }) => {
        this.items = data.items;
        this.unreadCount = data.unread_count;
        events.success();
      })
      .catch(({ response }) => {
        events.error(response);
      })
      .finally(() => {
        events.finish();
      });
  },
  $markRead(notificationId, events) {
    axios
      .post(route('upi.notifications.read', notificationId))
      .then(({ data }) => {
        this.items = this.items.filter(({ id }) => id !== notificationId);
        this.unreadCount -= 1;

        events.success();
      })
      .catch(({ response }) => {
        events.error(response);
      })
      .finally(() => {
        events.finish();
      });
  },
  $markReadAll(events) {
    axios
      .post(route('upi.notifications.read_all'))
      .then(({ data }) => {
        this.items = [];
        this.unreadCount = 0;

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
