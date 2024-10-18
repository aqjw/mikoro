<script setup>
import CardTitle from '@/Components/Card/CardTitle.vue';
import SectionFilterSort from '@/Components/Sections/SectionFilterSort.vue';
import TitleRating from '@/Components/TitleRating.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, ref, watch, nextTick } from 'vue';
import { storeToRefs, useCatalogStore } from '@/Stores';
import InfiniteScroll from '@/Components/InfiniteScroll.vue';

const UPDATE_DELAY_MS = 1000;

const props = defineProps({
  filter: {
    type: Object,
    default: null,
  },
});

const catalogStore = useCatalogStore();
const { items, total, has_more } = storeToRefs(catalogStore);

const infiniteScroll = ref(null);
const timer = ref(null);
const loading = ref(false);

const onFilterSortUpdated = (isSorting) => {
  const loadItems = () => {
    catalogStore.$resetPage();
    infiniteScroll.value?.load();
  };

  clearTimeout(timer.value);
  if (isSorting) {
    loadItems();
    return;
  }

  timer.value = setTimeout(loadItems, UPDATE_DELAY_MS);
};

const onReset = () => {
  catalogStore.$resetAll();
  infiniteScroll.value?.reload();

  if (route().current().startsWith('catalog.')) {
    router.visit(route('home'));
  }
};

onMounted(() => {
  if (props.filter?.key) {
    catalogStore.$resetAll();
    catalogStore.$setFilterValue(props.filter.key, props.filter.value);
    nextTick(() => infiniteScroll.value?.load());
  } else if (items.value.length === 0) {
    nextTick(() => infiniteScroll.value?.load());
  }
});

const onLoad = (finish) => {
  loading.value = true;
  catalogStore.$loadItems({
    success: () => {},
    error: () => {},
    finish: () => {
      loading.value = false;
      finish();
    },
  });
};
</script>

<template>
  <Head title="Anime" />

  <AppLayout>
    <div class="mb-4">
      <SectionFilterSort @updated="onFilterSortUpdated" @reset="onReset" />

      <div class="my-4 flex justify-between">
        <div>
          <span class="mr-2">Всего найдено тайтлов:</span>
          <span class="font-semibold">{{ total }}</span>
        </div>
        <div>
          <v-progress-circular
            v-if="loading"
            color="primary"
            indeterminate
            :size="20"
            :width="2"
          />
        </div>
      </div>

      <InfiniteScroll
        ref="infiniteScroll"
        :items="items"
        :has-more="has_more"
        :on-load="onLoad"
      >
        <div class="grid grid-cols-4 gap-4 mb-4">
          <div
            v-for="(item, index) in items"
            :key="index"
            class="hover:scale-105 duration-200"
          >
            <Link :href="route('title', item.slug)">
              <CardTitle :item="item" />
            </Link>
          </div>
        </div>
      </InfiniteScroll>
    </div>
  </AppLayout>
</template>
