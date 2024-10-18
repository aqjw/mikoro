<script setup>
import { nextTick, onMounted, ref, watch } from 'vue';
import MenuFilterItem from '../Menu/MenuFilterItem.vue';
import { storeToRefs, useCatalogStore } from '@/Stores';

const emit = defineEmits(['updated', 'reset']);

const catalogStore = useCatalogStore();
const {
  sorting: sortingState,
  filters: filtersState,
  options,
  activeFiltersCount,
  canReset,
} = storeToRefs(catalogStore);

const sorting = ref(JSON.parse(JSON.stringify(sortingState.value)));
const filters = ref(JSON.parse(JSON.stringify(filtersState.value)));

let updatingQuietly = false;

watch(
  sorting,
  () => {
    if (!updatingQuietly) {
      sortingState.value = JSON.parse(JSON.stringify(sorting.value));
      emit('updated', true);
    }
  },
  { deep: true }
);

watch(
  filters,
  () => {
    if (!updatingQuietly) {
      filtersState.value = JSON.parse(JSON.stringify(filters.value));
      emit('updated', false);
    }
  },
  { deep: true }
);

watch(
  sortingState,
  () => {
    updateQuietly(() => {
      sorting.value = JSON.parse(JSON.stringify(sortingState.value));
    });
  },
  { deep: true }
);

watch(
  filtersState,
  () => {
    updateQuietly(() => {
      filters.value = JSON.parse(JSON.stringify(filtersState.value));
    });
  },
  { deep: true }
);

onMounted(() => {
  catalogStore.$loadOptions();
});

const updateQuietly = (callback) => {
  updatingQuietly = true;
  callback();
  nextTick(() => (updatingQuietly = false));
};

const toggleSortingDir = () => {
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
          @click="emit('reset')"
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
                title="Страны"
                :options="options.filters.countries"
                :items="filters.countries"
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
            </v-card-text>
          </v-card>
        </v-menu>
      </div>
    </div>
  </div>
</template>
