import { useVideoProgressStore } from '@/Stores';
import { computed } from 'vue';
//
//
const usePlaybackManager = async (titleId) => {
  const videoProgressStore = useVideoProgressStore();

  // fetch user Playback State
  await videoProgressStore.$fetchPlaybackState(titleId);

  const loadLinks = (force = false) => {
    return new Promise((success, error) => {
      if (!force && videoProgressStore.hasLinks(titleId)) {
        success();
        return;
      }

      videoProgressStore.$fetchLinks(titleId, { success, error });
    });
  };

  const reloadLinks = () => {
    return loadLinks(true);
  };

  const loadEpisodes = () => {
    return new Promise((success, error) => {
      if (videoProgressStore.hasEpisodes(titleId)) {
        success();
        return;
      }

      videoProgressStore.$fetchEpisodes(titleId, { success, error });
    });
  };

  const setPlaybackState = (params) => {
    videoProgressStore.$setPlaybackState(titleId, params);
  };

  const savePlaybackState = () => {
    videoProgressStore.$savePlaybackState(titleId);
  };

  const _formatLink = ([definition, url]) => ({
    definition: `${definition}p`,
    url,
    text: `${definition}P`,
  });

  return {
    links: computed(() => {
      const links = videoProgressStore.getLinks(titleId);
      return Object.entries(links).map(_formatLink).reverse();
    }),
    episodes: computed(() => videoProgressStore.getEpisodes(titleId)),
    playbackState: computed(() => videoProgressStore.getPlaybackState(titleId)),
    //
    loadLinks,
    reloadLinks,
    loadEpisodes,
    setPlaybackState,
    savePlaybackState,
  };
};

export default usePlaybackManager;
