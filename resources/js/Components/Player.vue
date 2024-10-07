<script setup>
import { computed, ref, toRefs, watch, onMounted } from 'vue';
import Player from 'xgplayer';
import '@/../css/player.css';
import NavigationPlugin from '@/Plugins/xgplayer/NavigationPlugin';
import OverlayPlugin from '@/Plugins/xgplayer/OverlayPlugin';

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

const videoPlayer = ref(null);

onMounted(() => {
  const player = new Player({
    el: videoPlayer.value,
    url: 'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
    // definition: {
    //   list: [
    //     {
    //       definition: '320p', // definition key
    //       url:
    //         'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
    //       text: {
    //         zh: 'SD', // Chinese showText
    //         en: '320P', // English showText
    //       },
    //     },
    //     {
    //       definition: '480p', // definition key
    //       url:
    //         'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4', // play url
    //       text: {
    //         zh: 'HD', // Chinese showText
    //         en: '480P', // English showText
    //       },
    //     },
    //   ],
    // },
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
            console.log('player.muted', player.muted);
            player.muted = !player.muted;
          },
        },
      },
    },
      plugins: [OverlayPlugin],
  });

  player.registerPlugin(NavigationPlugin, {
    titleId: titleId.value,
  });
});
</script>

<template>
  <div ref="videoPlayer"></div>
</template>
