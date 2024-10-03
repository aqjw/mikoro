<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import MenuFilterItem from '../Menu/MenuFilterItem.vue';

const emit = defineEmits(['updated']);

const EMIT_DELAY_MS = 500;
const DEFAULT_SORT = {
  option: 'latest',
  dir: 'desc',
};

const sorting = ref(JSON.parse(JSON.stringify(DEFAULT_SORT)));
const timer = ref(null);
const filters = ref({
  genres: { incl: [], excl: [] },
  studios: { incl: [], excl: [] },
  translations: { incl: [], excl: [] },
  years: { incl: [], excl: [] },
  statuses: { incl: [], excl: [] },
});

const options = ref({
  sorting: [
    { value: 'latest', title: 'Последние поступления' },
    { value: 'rating', title: 'По рейтингу' },
    // TODO: no comments yet
    // { value: 'comments', title: 'По комментариям' },
    { value: 'episodes_count', title: 'По количеству эпизодов' },
    { value: 'seasons_count', title: 'По количеству сезонов' },
  ],
  filters: {
    genres: [],
    studios: [],
    translations: [],
    years: [],
    statuses: [],
  },
});

watch(
  [sorting, filters],
  () => {
    clearTimeout(timer.value);
    timer.value = setTimeout(
      () =>
        emit('updated', {
          sorting: sorting.value,
          filters: filters.value,
        }),
      EMIT_DELAY_MS
    );
  },
  { deep: true }
);

onMounted(() => {
  loadFilterOptions();
});

const canReset = computed(() => {
  const filtersNotEmpty = Object.values(filters.value).some(
    (filter) => filter.incl.length || filter.excl.length
  );
  return (
    JSON.stringify(DEFAULT_SORT) !== JSON.stringify(sorting.value) || filtersNotEmpty
  );
});

const activeFiltersCount = computed(() => {
  return Object.values(filters.value).filter(
    (filter) => filter.incl.length || filter.excl.length
  ).length;
});

const reset = () => {
  sorting.value = JSON.parse(JSON.stringify(DEFAULT_SORT));
  Object.keys(filters.value).forEach((key) => {
    filters.value[key] = { incl: [], excl: [] };
  });
};

const loadFilterOptions = () => {
  axios.get(route('upi.title.filters')).then(({ data }) => {
    options.value.filters.genres = data.genres;
    options.value.filters.studios = data.studios;
    options.value.filters.translations = data.translations;
    options.value.filters.statuses = data.statuses;
    options.value.filters.years = data.years;
  });
};

const toggleSortingDir = () => {
  sorting.value.dir = sorting.value.dir === 'asc' ? 'desc' : 'asc';
};

const updateFilterItems = () => {
  sorting.value.dir = sorting.value.dir === 'asc' ? 'desc' : 'asc';
};
</script>

<template>
  <div class="grid grid-cols-4 gap-4">
    <div>
      <v-select
        :items="options.sorting"
        v-model="sorting.option"
        variant="solo"
        color="primary"
        density="compact"
        rounded="lg"
        hide-details
      />
    </div>

    <div class="d-flex items-center">
      <v-btn
        density="comfortable"
        :icon="sorting.dir === 'asc' ? 'mdi-sort-ascending' : 'mdi-sort-descending'"
        variant="tonal"
        rounded="lg"
        color="grey"
        @click="toggleSortingDir"
      />
    </div>

    <div></div>

    <div class="d-flex items-end justify-end">
      <div class="d-flex items-center gap-2">
        <v-btn
          v-if="canReset"
          variant="text"
          color="red"
          density="comfortable"
          class="text-none"
          @click="reset"
        >
          Сбросить
        </v-btn>

        <v-menu
          rounded
          location="bottom end"
          origin="auto"
          offset="10"
          :close-on-content-click="false"
        >
          <template v-slot:activator="{ props }">
            <v-btn rounded="lg" class="text-none" v-bind="props">
              <v-badge
                v-if="activeFiltersCount > 0"
                color="green"
                :content="activeFiltersCount"
                inline
              />
              <span>Фильтры</span>
            </v-btn>
          </template>

          <v-card width="300px" max-height="500px">
            <v-card-text>
              <MenuFilterItem
                title="Жанр"
                :options="options.filters.genres"
                :items="filters.genres"
              />

              <v-divider
                opacity="1"
                class="my-4 -mx-4 border-gray-200 dark:border-gray-500/30"
              />

              <MenuFilterItem
                title="Озвучка"
                label-key="title"
                :options="options.filters.translations"
                :items="filters.translations"
              />
              <v-divider
                opacity="1"
                class="my-4 -mx-4 border-gray-200 dark:border-gray-500/30"
              />

              <MenuFilterItem
                title="Студия"
                :options="options.filters.studios"
                :items="filters.studios"
              />

              <v-divider
                opacity="1"
                class="my-4 -mx-4 border-gray-200 dark:border-gray-500/30"
              />

              <MenuFilterItem
                title="Год"
                :options="options.filters.years"
                :items="filters.years"
              />

              <v-divider
                opacity="1"
                class="my-4 -mx-4 border-gray-200 dark:border-gray-500/30"
              />

              <MenuFilterItem
                title="Статус"
                :options="options.filters.statuses"
                :items="filters.statuses"
              />
            </v-card-text>
          </v-card>
        </v-menu>
      </div>
    </div>
  </div>
</template>
