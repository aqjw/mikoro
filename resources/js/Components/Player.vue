<script setup>
import '@/../css/player.css';
import usePlaybackManager from '@/Composables/usePlaybackManager';
import {
  OverlayPlugin,
  PlaylistPlugin,
  SettingsPlugin,
  VolumePlugin,
} from '@/Plugins/xgplayer';
import { onBeforeUnmount, onMounted, ref, toRefs } from 'vue';

import Player from 'xgplayer';
import AdPlugin, { AdEvents } from 'xgplayer-ads';
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
  isSingleEpisode: {
    type: Boolean,
    default: false,
  },
});

const { poster, titleId, isSingleEpisode } = toRefs(props);

const player = ref(null);
const playerContainer = ref(null);
const loading = ref(true);

onMounted(async () => {
  loading.value = true;
  const playbackManager = await usePlaybackManager(titleId.value);

  playbackManager
    .loadLinks()
    .then(() => {
      initPlayer(playbackManager.links.value, playbackManager);
    })
    .catch((error) => {
      // TODO: Handle error
      console.error('error while load video links', error);
    })
    .finally(() => {
      loading.value = false;
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
      isShowIcon: false,
      defaultDefinition: 1080,
      list: definitionList,
    },
    poster: poster.value && {
      poster: poster.value,
      isEndedShow: false,
    },
    height: '100%',
    width: '100%',
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
      list: [
        { text: '2x', rate: 2 },
        { text: '1.5x', rate: 1.5 },
        { text: '1.25x', rate: 1.25 },
        { text: 'Normal', rate: 1 },
        { text: '0.75x', rate: 0.75 },
        { text: '0.5x', rate: 0.5 },
        { text: '0.25x', rate: 0.25 },
      ],
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
    ad: {
      adType: 'ima',
      controls: true,
      debug: true,
      ima: {
        // locale: 'ru_RU',
        adTagUrl:
          'https://pubads.g.doubleclick.net/gampad/ads?sz=640x480&iu=/124319096/external/single_ad_samples&ciu_szs=300x250&impl=s&gdfp_req=1&env=vp&output=vast&unviewed_position_start=1&cust_params=deployment%3Ddevsite%26sample_ct%3Dlinear&correlator=',
      },
    },
    plugins: [AdPlugin, OverlayPlugin, HlsPlugin],
    ignores: ['cssFullscreen', 'volume', 'play', 'replay'],
  });

  player.value.registerPlugin(VolumePlugin);
  player.value.registerPlugin(SettingsPlugin);
  player.value.registerPlugin(PlaylistPlugin, {
    isSingleEpisode: isSingleEpisode.value,
    playbackManager: playbackManager,
  });

  // TODO: hide start plugin
  const adPlugin = player.value.getPlugin(AdPlugin.pluginName);

  adPlugin.on(AdEvents.IMA_AD_MANAGER_READY, () => {
    adPlugin.uiManager.adUIPlugins = adPlugin.uiManager.adUIPlugins.filter(
      ([targetPlugin, _]) => targetPlugin.pluginName !== 'playbackrate'
    );
  });

  adPlugin.on(AdEvents.AD_PLAY, () => {
    console.log('AdEvents.AD_PLAY');
    player.value.getPlugin(OverlayPlugin.pluginName).hide();
    // player.value.getPlugin('start').toggleTo('play');
    // console.log('11111', player.value.getPlugin('start').config.mode);
  });

  // TODO:  OverlayPlugin - show
  //   player.value.on(Events.ENDED, (e) => {
  //     console.log('Events.ENDED', e);
  //     player.value.getPlugin(OverlayPlugin.pluginName).show();
  //     // player.value.getPlugin('start').toggleTo('pause');
  //     // console.log('22222', player.value.getPlugin('start').config.mode);
  //   });

  adPlugin.on('ima_content_pause_requested', () => {
    console.log('Реклама началась');
  });

  adPlugin.on('ima_content_resume_requested', () => {
    console.log('Реклама закончилась');
  });
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
  <div ref="playerContainer" class="relative h-full">
    <span
      class="!hidden !h-full !h-4 !h-7 !w-8 duration-200 !h-5 !h-6 !w-6 !w-8 !h-8 !w-[initial] !h-[initial] border border-2 !border-white !border-white/50 rounded-md"
      >tailwindcss use classes</span
    >
    <div v-if="loading" class="absolute-center">
      <v-progress-circular color="primary" indeterminate :size="40" :width="2" />
    </div>
  </div>
</template>
