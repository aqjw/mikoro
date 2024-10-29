<script setup>
import { ref, computed } from 'vue';
import { storeToRefs, useAppStore } from '@/Stores';
import { getBookmarkIcon, getBookmarkColor } from '@/Utils';
import { useTheme } from 'vuetify';
import PartBookmarkTab from './PartBookmarkTab.vue';

defineProps({
  userId: {
    type: Number,
    required: true,
  },
});

const appStore = useAppStore();
const theme = useTheme();
const { getConfig } = storeToRefs(appStore);
const isDark = computed(() => theme.global.name.value === 'dark');

const tab = ref('notifications');
const bookmarks = ref(getConfig.value.bookmarks);
</script>

<template>
  <div>
    <div class="bg-second rounded-lg shadow-lg">
      <v-tabs
        v-model="tab"
        density="compact"
        slider-color="primary"
        class="rounded-lg overflow-hidden"
      >
        <v-tab
          v-for="(item, index) in bookmarks"
          :key="index"
          :value="item.name"
          class="text-none"
          :prepend-icon="getBookmarkIcon(item.name)"
          :base-color="getBookmarkColor(item.name, isDark)"
          :slider-color="getBookmarkColor(item.name, isDark)"
        >
          {{ item.name }}
        </v-tab>
      </v-tabs>
    </div>

    <v-card-text class="!p-0 mt-2">
      <v-tabs-window v-model="tab">
        <v-tabs-window-item
          v-for="(item, index) in bookmarks"
          :key="item.key || index"
          :value="item.name"
          class="p-4 pt-0"
        >
          <PartBookmarkTab :bookmark="item.name" :user-id="userId" />
        </v-tabs-window-item>
      </v-tabs-window>
    </v-card-text>
  </div>
</template>
