const DEFAULT_EXPIRATION_MINUTES = 60 * 24 * 365; // a year

const useSession = {
  set(key, value, expirationMinutes = DEFAULT_EXPIRATION_MINUTES) {
    const now = new Date();
    const expirationTime = now.getTime() + expirationMinutes * 60 * 1000;
    const dataToStore = { value, expirationTime };

    localStorage.setItem(key, JSON.stringify(dataToStore));
  },

  get(key, defaultValue = null) {
    const item = localStorage.getItem(key);
    if (item) {
      const data = JSON.parse(item);
      const now = new Date().getTime();

      if (data.expirationTime && data.expirationTime < now) {
        useSession.remove(key);
        return defaultValue;
      }

      return data.value;
    }
    return defaultValue;
  },

  getOrSet(key, defaultValue, expirationMinutes = DEFAULT_EXPIRATION_MINUTES) {
    const storedValue = useSession.get(key);
    if (storedValue !== null) {
      return storedValue;
    }

    useSession.set(key, defaultValue, expirationMinutes);
    return defaultValue;
  },

  remove(key) {
    localStorage.removeItem(key);
  },
};

export default useSession;
