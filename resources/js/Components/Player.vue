<script setup>
import '@/../css/player.css';
import usePlaybackManager from '@/Composables/usePlaybackManager';
import { OverlayPlugin, PlaylistPlugin, VolumePlugin } from '@/Plugins/xgplayer';
import { onBeforeUnmount, onMounted, ref, toRefs } from 'vue';
import Player from 'xgplayer';
import HlsPlugin from 'xgplayer-hls';

const props = defineProps({
  poster: {
    type: String,
    default: null,
  },
  titleId: {
    type: Number,
    required: true,
  },
});

const { poster, titleId } = toRefs(props);

const player = ref(null);
const playerContainer = ref(null);

onMounted(async () => {
  const playbackManager = await usePlaybackManager(titleId.value);

  playbackManager
    .loadLinks()
    .then(() => {
      initPlayer(playbackManager.links.value, playbackManager);
    })

    .catch((error) => {
      // TODO: Handle error
      console.error('error while load video links', error);
    });

  window.addEventListener('beforeunload', destroyPlayer);
});

onBeforeUnmount(() => {
  destroyPlayer();
  window.removeEventListener('beforeunload', destroyPlayer);
});

const initPlayer = (definitionList, playbackManager) => {
  const time = playbackManager.playbackState.value?.time ?? 0;

  player.value = new Player({
    el: playerContainer.value,
    definition: {
      defaultDefinition: '1080p',
      list: definitionList,
    },
    poster: poster.value && {
      poster: poster.value,
      isEndedShow: false,
    },
    height: '100%',
    width: '100%',
    cssFullscreen: false,
    startTime: time,
    controls: {
      mode: 'normal',
      initShow: time > 0,
    },
    start: {
      isShowPause: true,
      isShowEnd: true,
    },
    playbackRate: {
      isShowIcon: false,
    },
    isHideTips: true,
    progress: {
      miniMoveStep: 2,
      miniStartStep: 2,
    },
    keyboard: {
      keyCodeMap: {
        key_f: {
          keyCode: 70,
          action: (e, player) => {
            player.getPlugin('fullscreen').handleFullscreen();
          },
        },
        key_m: {
          keyCode: 77,
          action: (e, player) => {
            player.muted = !player.muted;
          },
        },
        right: {
          action: (e, player) => {
            player.getPlugin('keyboard').seek(e);
          },
          pressAction: () => {},
        },
      },
    },
    plugins: [OverlayPlugin, HlsPlugin],
  });

  player.value.unRegisterPlugin('volume');
  player.value.unRegisterPlugin('play');
  player.value.unRegisterPlugin('replay');
  player.value.registerPlugin(VolumePlugin);
  player.value.registerPlugin(PlaylistPlugin, { playbackManager });

  player.value.usePluginHooks('error', 'errorRetry', () => {
    return new Promise((resolve) => {
      playbackManager
        .reloadLinks()
        .then(() => {
          player.value.definitionList = playbackManager.links.value;
          resolve(false);
        })
        .catch((error) => {
          // TODO: handle error
          console.error(error);
          resolve(true);
        });
    });
  });
};

const destroyPlayer = () => {
  if (player.value) {
    player.value.destroy();
  }
};
</script>

<template>
  <div ref="playerContainer"></div>
</template>
