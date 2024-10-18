<script setup>
import { ref } from 'vue';
import DateManager from '@/Plugins/DateManager';
import { router } from '@inertiajs/vue3';
import TitleRating from '@/Components/TitleRating.vue';
import { format } from '@/Plugins/DateManager';
import { formatCompactNumber, handleResponseError, getBookmarkIcon } from '@/Utils';
import { useToast } from 'vue-toast-notification';
import { useBookmarkStore } from '@/Stores';
import { computed } from 'vue';

const props = defineProps({
  bookmark: {
    type: String,
    required: true,
  },
  bookmarks: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['refresh']);

const $toast = useToast();
const bookmarkStore = useBookmarkStore();

const items = ref([]);
const total = ref(0);
const page = ref(1);
const itemsPerPage = ref(0);
const loading = ref(false);
const tableKey = ref(0);
const sortBy = ref([{ key: 'updated_at', order: 'desc' }]);

const headers = [
  {
    title: 'Название',
    align: 'start',
    key: 'title',
  },
  {
    title: 'Выпущено',
    align: 'start',
    key: 'released_at',
    nowrap: true,
    value: (item) => DateManager.format(item.released_at, 'y LLLL'),
  },
  {
    title: 'Рейтинг',
    align: 'start',
    key: 'rating',
    nowrap: true,
  },
  {
    title: 'Кол-во серий',
    align: 'start',
    key: 'last_episode',
    value: (item) => (item.single_episode ? 'Фильм' : `${item.last_episode} серий`),
  },
  {
    title: 'Добавлено',
    align: 'start',
    key: 'updated_at',
    nowrap: true,
    value: (item) => DateManager.format(item.updated_at),
  },
  {
    title: '',
    sortable: false,
    key: 'actions',
  },
];

const isEmpty = computed(() => {
  return !loading.value && !items.value.length;
});

const firstLoading = computed(() => {
  return loading.value && !items.value.length;
});

const loadItems = ({ page, sortBy }) => {
  if (loading.value) {
    return;
  }

  loading.value = true;
  bookmarkStore.$loadItems(
    props.bookmark,
    { page, sortBy },
    {
      success: (data) => {
        items.value = data.items;
        total.value = data.total;
        itemsPerPage.value = data.items_per_page;
      },
      error: (error) => {
        $toast.error(handleResponseError(error));
      },
      finish: () => {
        loading.value = false;
      },
    }
  );
};

const handleRowClick = (item) => {
  router.visit(route('title', item.slug));
};

const toggleItem = (bookmark, item) => {
  const same = props.bookmark === bookmark.name;
  const bookmarkId = same ? null : bookmark.id;

  item.loading = true;
  bookmarkStore.$toggle(item.title_id, bookmarkId, {
    success: () => {
      tableKey.value = +new Date();
      if (!same) {
        emit('refresh', bookmark.name);
      }
    },
    error: (error) => {
      $toast.error(handleResponseError(error));
    },
    finish: () => {
      item.loading = false;
    },
  });
};
</script>

<template>
  <div>
    <v-data-table-server
      :key="tableKey"
      fixed-header
      hover
      density="compact"
      class="max-h-[calc(100vh-220px)]"
      item-value="title"
      v-model:page="page"
      :items-per-page="itemsPerPage"
      :headers="headers"
      :items="items"
      :items-length="total"
      :loading="loading"
      :hide-default-footer="isEmpty || firstLoading"
      :hide-default-header="isEmpty || firstLoading"
      v-model:sort-by="sortBy"
      @update:options="loadItems"
    >
      <template v-slot:body="{ items }">
        <tr v-if="firstLoading">
          <td :colspan="headers.length">
            <div class="flex justify-center items-center p-6">
              <v-progress-circular color="primary" indeterminate :size="25" :width="2" />
            </div>
          </td>
        </tr>

        <tr v-if="isEmpty">
          <td :colspan="headers.length">
            <div class="text-center p-6">Ничего не найдено</div>
          </td>
        </tr>

        <tr
          v-else
          v-for="item in items"
          :key="item.id"
          v-ripple
          class="cursor-pointer"
          @click="handleRowClick(item)"
        >
          <td>
            <div class="break-anywhere py-2">{{ item.title }}</div>
          </td>

          <td class="whitespace-nowrap">
            {{ DateManager.format(item.released_at, 'y LLLL') }}
          </td>

          <td>
            <div v-if="item.shikimori_rating" class="whitespace-nowrap">
              <TitleRating :value="item.shikimori_rating" />
              <span class="text-xs ml-2">
                ({{ formatCompactNumber(item.shikimori_votes) }})
              </span>
            </div>
            <div v-else>–</div>
          </td>

          <td class="whitespace-nowrap">
            <div>{{ item.single_episode ? 'Фильм' : `${item.last_episode} серий` }}</div>
          </td>

          <td class="whitespace-nowrap">
            {{ DateManager.format(item.updated_at) }}
          </td>

          <td>
            <v-menu location="bottom end">
              <template v-slot:activator="{ props }">
                <v-btn
                  icon="mdi-dots-vertical"
                  variant="text"
                  density="comfortable"
                  size="small"
                  :loading="item.loading"
                  v-bind="props"
                />
              </template>
              <v-list density="compact" class="spacerless">
                <v-list-item
                  v-for="(b_item, index) in bookmarks"
                  :key="index"
                  :value="b_item.name"
                  :active="b_item.name === bookmark"
                  color="primary"
                  @click="toggleItem(b_item, item)"
                >
                  <template #prepend>
                    <v-icon :icon="getBookmarkIcon(b_item.name)" />
                  </template>
                  <v-list-item-title>
                    {{ b_item.name }}
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </td>
        </tr>
      </template>
    </v-data-table-server>
  </div>
</template>

<style>
.v-data-table-footer__items-per-page {
  display: none !important;
}
</style>
