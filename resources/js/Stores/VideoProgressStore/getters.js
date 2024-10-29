export default {
  getPlaybackState: (state) => (titleId) => state.playbackState[titleId],
  getPlaybackStateEpisodeId: (state) => (titleId) =>
    state.playbackState[titleId]?.episode_id,
  //
  getLinks: (state) => (titleId) => state.videoLinks[titleId],
  hasLinks: (state) => (titleId) => Boolean(state.videoLinks[titleId]),
  //
  getEpisodes: (state) => (titleId) => state.episodes[titleId],
  hasEpisodes: (state) => (titleId) => Boolean(state.episodes[titleId]),
};
