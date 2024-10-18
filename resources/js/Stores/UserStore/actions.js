// import { useNavigationStore } from '@/Stores';

export default {
  setUser(payload) {
    this.user = payload;
    // useNavigationStore().setBadge('chat', payload?.unreadMessagesCount ?? 0);
  },
//   updateSettings(key, value) {
//     const endpoint = route('profile.update-settings');
//     axios
//       .post(endpoint, { key, value })
//       .then(() => {})
//       .catch((error) => {
//         console.error(error);
//       });
//   },
};
