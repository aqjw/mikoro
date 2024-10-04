<script setup>
import CardTitle from '@/Components/Card/CardTitle.vue';
import FilterSort from '@/Components/Layout/FilterSort.vue';
import TitleRating from '@/Components/TitleRating.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import { useCatalogStore } from '@/Stores/CatalogStore';
import { storeToRefs } from 'pinia';

const UPDATE_DELAY_MS = 1000;

const props = defineProps({
  filter: {
    type: Object,
    default: null,
  },
});

const catalogStore = useCatalogStore();
const { items, params } = storeToRefs(catalogStore);

const infiniteScroll = ref(null);
const timer = ref(null);
const doneCallback = ref(null);

const onFilterSortUpdated = (isSorting) => {
  clearTimeout(timer.value);
  if (isSorting) {
    catalogStore.resetItems();
    doneCallback.value?.('ok');
    return;
  }

  timer.value = setTimeout(() => {
    catalogStore.resetItems();
    doneCallback.value?.('ok');
  }, UPDATE_DELAY_MS);
};

const onReset = () => {
  catalogStore.resetSorting();
  catalogStore.resetFilters();
  catalogStore.resetItems();
  doneCallback.value?.('ok');

  if (route().current().startsWith('catalog.')) {
    router.visit(route('home'))
  }
};

onMounted(() => {
  if (props.filter?.key) {
    catalogStore.setFilterValue(props.filter.key, props.filter.value);
  }
});

const onLoadItems = ({ done }) => {
  doneCallback.value = done;
  catalogStore.loadItems((hasMore) => {
    done(hasMore ? 'ok' : 'empty');
  });
};
</script>

<template>
  <Head title="Anime" />

  <AppLayout bg-transparent>
    <div>
      <FilterSort @updated="onFilterSortUpdated" @reset="onReset" />

      <v-infinite-scroll
        ref="infiniteScroll"
        :items="items"
        :onLoad="onLoadItems"
        :mode="items.length > 0 ? 'manual' : 'intersect'"
        class="p-6 -m-6"
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

        <template v-slot:load-more="{ props }">
          <v-btn
            v-bind="props"
            variant="flat"
            class="text-none"
            color="primary"
            density="comfortable"
          >
            Load More
          </v-btn>
        </template>

        <template v-slot:loading>
          <v-progress-circular indeterminate :size="50" :width="2" color="primary" />
        </template>

        <template v-slot:empty>
          <div v-if="items.length === 0">Ничего не найдено</div>
        </template>
      </v-infinite-scroll>
    </div>
  </AppLayout>
</template>
