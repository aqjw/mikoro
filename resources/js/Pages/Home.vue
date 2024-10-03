<script setup>
import CardTitle from '@/Components/Card/CardTitle.vue';
import FilterSort from '@/Components/Layout/FilterSort.vue';
import TitleRating from '@/Components/TitleRating.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head,Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, watch } from 'vue';

const props = defineProps({
  genre: [Number, String],
  translation: [Number, String],
  studio: [Number, String],
  year: [Number, String],
  status: [Number, String],
});

console.log('props', props);

const doneCallback = ref(null);

const items = ref([]);
const page = ref(1);
const params = ref({});

const filterSortUpdated = (newVal) => {
  params.value = newVal;
  reset();
};

const reset = (newVal) => {
  if (doneCallback.value) {
    page.value = 1;
    items.value = [];
    doneCallback.value('ok');
  }
};

const loadItems = ({ done }) => {
  doneCallback.value = done;

  axios
    .get(route('upi.title.catalog'), {
      params: {
        page: page.value,
        ...params.value,
      },
    })
    .then(({ data }) => {
      page.value++;
      items.value.push(...data.items);
      done(data.has_more ? 'ok' : 'empty');
    });
};
</script>

<template>
  <Head title="Anime" />

  <AppLayout bg-transparent>
    <div>
      <FilterSort @updated="filterSortUpdated" />

      <v-infinite-scroll
        ref="infiniteScroll"
        :items="items"
        :onLoad="loadItems"
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

        <template v-slot:empty></template>
      </v-infinite-scroll>
    </div>
  </AppLayout>
</template>
