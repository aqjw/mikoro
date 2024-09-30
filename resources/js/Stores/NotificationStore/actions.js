export default {
    setNotifications(payload) {
    this.lastItems = payload.lastItems;
    this.unreadCount = payload.unreadCount;
  },
};
