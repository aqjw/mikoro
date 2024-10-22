<script setup>
import { computed, ref, toRefs, watch, onMounted, nextTick } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { storeToRefs, useAppStore } from '@/Stores';
import PartTab from './Parts/PartTab.vue';
import { getBookmarkIcon } from '@/Utils';

const appStore = useAppStore();
const { getConfig } = storeToRefs(appStore);

const props = defineProps({
  bookmark: {
    type: String,
    default: 'planed',
  },
});

const tab = ref(props.bookmark);
const bookmarks = ref(
  getConfig.value.bookmarks.map((item) => {
    item.key = item.name;
    return item;
  })
);

watch(tab, () => {
  window.history.replaceState({}, '', route('bookmarks', tab.value));
});

const refreshTab = (tab) => {
  bookmarks.value = bookmarks.value.map((item) => {
    if (item.name == tab) {
      item.key = +new Date();
    }
    return item;
  });
};
</script>

<template>
  <Head title="Bookmark" />

  <AppLayout>
    <div>
      <div class="text-xl font-medium mb-4">Bookmarks</div>
      <div class="bg-second rounded-lg shadow-lg overflow-hidden">
        <v-card elevation="0">
          <v-tabs v-model="tab" density="compact" slider-color="primary">
            <v-tab
              v-for="(item, index) in bookmarks"
              :key="index"
              :value="item.name"
              class="text-none"
              :prepend-icon="getBookmarkIcon(item.name)"
            >
              {{ item.name }}
            </v-tab>
          </v-tabs>

          <v-card-text class="!p-0">
            <v-tabs-window v-model="tab">
              <v-tabs-window-item
                v-for="(item, index) in bookmarks"
                :key="item.key || index"
                :value="item.name"
              >
                <PartTab
                  :bookmark="item.name"
                  :bookmarks="bookmarks"
                  @refresh="refreshTab"
                />
              </v-tabs-window-item>
            </v-tabs-window>
          </v-card-text>
        </v-card>
      </div>
    </div>
  </AppLayout>
</template>
