<script setup>
import { nextTick, computed, onMounted, ref } from 'vue';
import { useTheme } from 'vuetify';
import { useToast } from 'vue-toast-notification';
import { Link } from '@inertiajs/vue3';
import { getBookmarkIcon, getBookmarkColor, handleResponseError } from '@/Utils';
import DateManager from '@/Plugins/DateManager';
import InfiniteScroll from '@/Components/InfiniteScroll.vue';

const props = defineProps({
  userId: {
    type: Number,
    required: true,
  },
});

const theme = useTheme();
const isDark = computed(() => theme.global.name.value === 'dark');

const $toast = useToast();

const infiniteScroll = ref(null);
const loading = ref(true);
const total = ref(0);
const page = ref(1);
const items = ref([]);
const hasMore = ref(true);

const isEmpty = computed(() => {
  return !loading.value && total.value == 0;
});

onMounted(() => {
  nextTick(() => {
    infiniteScroll.value?.load();
  });
});

const onLoad = (finish) => {
  loading.value = true;

  axios
    .get(route('upi.profile.activity_histories', { user: props.userId }), {
      params: { page: page.value },
    })
    .then(({ data }) => {
      populateItems(data.items);
      total.value = data.total;
      hasMore.value = data.has_more;
      page.value += 1;
    })
    .catch(({ response }) => {
      $toast.error(handleResponseError(response));
    })
    .finally(() => {
      loading.value = false;
      finish();
    });
};

const populateItems = (rawItems) => {
  items.value.push(
    ...rawItems.map((item) => {
      const isEpisode = item.type === 'episode';
      const title = isEpisode
        ? item.context
          ? `Серия ${item.context}`
          : 'Фильм'
        : 'Закладка';

      return {
        icon: isEpisode ? 'mdi-check-all' : 'mdi-bookmark',
        title,
        body: item.title.title,
        bookmark: !isEpisode ? item.context : null,
        url: route('title', item.title.slug),
        timestamp: item.created_at,
      };
    })
  );
};
</script>

<template>
  <div v-if="!isEmpty">
    <div class="text-lg mb-2 font-semibold justify-start flex">История</div>

    <InfiniteScroll
      ref="infiniteScroll"
      :items="items"
      :has-more="hasMore"
      :on-load="onLoad"
      class="max-h-[500px] overflow-y-auto"
    >
      <v-timeline
        align="start"
        density="compact"
        fill-dot
        dot-color="indigo-lighten-2"
        size="x-small"
      >
        <v-timeline-item v-for="(item, index) in items" :key="index" :icon="item.icon">
          <div class="text-sm flex justify-between mb-1">
            <div>{{ item.title }}</div>
            <div class=" text-gray-600 dark:text-gray-400 italic">{{ DateManager.toHuman(item.timestamp, { parts: 1 }) }} ago</div>
          </div>

          <v-chip
            v-if="item.bookmark"
            :prepend-icon="getBookmarkIcon(item.bookmark)"
            :color="getBookmarkColor(item.bookmark, isDark)"
            density="compact"
            label
          >
            {{ item.bookmark }}
          </v-chip>

          <div>
            <Link :href="item.url" class="link">{{ item.body }}</Link>
          </div>
        </v-timeline-item>
      </v-timeline>
    </InfiniteScroll>
  </div>
</template>
