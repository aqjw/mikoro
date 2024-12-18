<script setup>
import { nextTick, ref, watch } from 'vue';
import { formatCompactNumber, handleResponseError, getBookmarkIcon } from '@/Utils';
import DialogLoginRequires from '@/Components/Dialogs/DialogLoginRequires.vue';
import { useToast } from 'vue-toast-notification';
import { storeToRefs, useAppStore, useUserStore, useBookmarkStore } from '@/Stores';
import { toRefs } from 'vue';

const props = defineProps({
  title: {
    type: Object,
    required: true,
  },
});

const $toast = useToast();
const appStore = useAppStore();
const bookmarkStore = useBookmarkStore();
const userStore = useUserStore();
const { getConfig } = storeToRefs(appStore);
const { isLogged } = storeToRefs(userStore);

const loginRequires = ref(null);
const loading = ref(false);
const selected = ref(props.title.bookmark);
const timer = ref(null);
const skipWatch = ref(false);

watch(selected, () => {
  if (skipWatch.value) {
    return;
  }

  clearTimeout(timer.value);
  timer.value = setTimeout(onUpdate, 500);
});

const onUpdate = (value) => {
  if (!isLogged.value) {
    loginRequires.value.open();

    skipWatch.value = true;
    selected.value = null;
    nextTick(() => (skipWatch.value = false));

    return;
  }

  loading.value = true;
  bookmarkStore.$toggle(props.title.id, selected.value, {
    success: () => {
      $toast.success('Сохранено успешно');
    },
    error: (error) => {
      $toast.error(handleResponseError(error));
    },
    finish: () => {
      loading.value = false;
    },
  });
};
</script>

<template>
  <div class="relative">
    <div v-if="loading" class="absolute -right-2 -top-2">
      <v-progress-circular color="primary" indeterminate :size="20" :width="2" />
    </div>

    <div class="text-caption mb-2">Добавить в закладки</div>

    <v-item-group
      v-model="selected"
      selected-class="bg-primary"
      class="flex flex-wrap gap-2"
    >
      <v-item
        v-for="(item, index) in getConfig.bookmarks"
        :key="index"
        :value="item.id"
        v-slot="{ selectedClass, toggle }"
      >
        <v-chip
          :class="selectedClass"
          density="compact"
          label
          @click="toggle"
          :prepend-icon="getBookmarkIcon(item.name)"
        >
          {{ item.name }}
        </v-chip>
      </v-item>
    </v-item-group>

    <DialogLoginRequires ref="loginRequires" />
  </div>
</template>
