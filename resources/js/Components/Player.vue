<script setup>
import { ref, toRefs, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { NavigationPlugin, OverlayPlugin } from '@/Plugins/xgplayer';
import { storeToRefs, useUserStore } from '@/Stores';
import Player from 'xgplayer';
import '@/../css/player.css';

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

const userStore = useUserStore();
const { isLogged } = storeToRefs(userStore);

const player = ref(null);
const playerContainer = ref(null);
const videoUrl = ref(
  '//cloud.kodik-storage.com/useruploads/ba2c1fec-cdae-410c-855c-9f60f790937e/b16c171865ad9e40772fc683b382a591:2024102721/720.mp4:hls:manifest.m3u8'
);

onMounted(() => {
  nextTick(initPlayer);
  window.addEventListener('beforeunload', destroyPlayer);
});

onBeforeUnmount(() => {
  destroyPlayer();
  window.removeEventListener('beforeunload', destroyPlayer);
});

const initPlayer = () => {
  player.value = new Player({
    el: playerContainer.value,
    url: videoUrl.value,
    poster: poster.value && {
      poster: poster.value,
      isEndedShow: false,
    },
    height: '100%',
    width: '100%',
    cssFullscreen: false,
    controls: {
      mode: 'normal',
    },
    keyboard: {
      keyCodeMap: {
        fullscreen: {
          keyCode: 70,
          action: (_, player) => {
            player.getPlugin('fullscreen').handleFullscreen();
          },
        },
        mute: {
          keyCode: 77,
          action: (_, player) => {
            player.muted = !player.muted;
          },
        },
      },
    },
    plugins: [OverlayPlugin],
  });

  player.value.registerPlugin(NavigationPlugin, {
    titleId: titleId.value,
    isLogged: isLogged.value,
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
