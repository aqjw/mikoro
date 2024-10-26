<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { storeToRefs, useNotificationStore } from '@/Stores';
import { useToast } from 'vue-toast-notification';
import { handleResponseError } from '@/Utils';

const $toast = useToast();
const notificationStore = useNotificationStore();
const { items, unreadCount } = storeToRefs(notificationStore);

const loading = ref(false);
const loadingAll = ref(false);
const isMenuOpen = ref(false);

watch(isMenuOpen, (newValue) => {
  if (newValue) {
    loadItems();
  }
});

const loadItems = () => {
  const len = items.value.length;
  if (len > 0 && len === unreadCount.value) {
    return;
  }

  loading.value = true;
  notificationStore.$loadItems({
    success: () => {},
    error: (error) => {
      $toast.error(handleResponseError(error));
    },
    finish: () => {
      loading.value = false;
    },
  });
};

const markReadAll = () => {
  loadingAll.value = true;
  notificationStore.$markReadAll({
    success: () => {},
    error: (error) => {
      $toast.error(handleResponseError(error));
    },
    finish: () => {
      loadingAll.value = false;
    },
  });
};

const markRead = (item, success = null) => {
  if (loadingAll.value) {
    return;
  }

  item.loading = true;
  notificationStore.$markRead(item.id, {
    success: () => {
      if (success) {
        success();
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

const openNotification = (item) => {
  markRead(item, () => {
    if (item.type == 'new-episode') {
      router.visit(route('title', item.data.title_slug), {
        data: {
          episode_id: item.data.episode_id,
          translation_id: item.data.translation_id,
        },
      });
    }
  });
};
</script>

<template>
  <v-menu
    v-model="isMenuOpen"
    rounded
    location="bottom end"
    origin="auto"
    :close-on-content-click="false"
  >
    <template v-slot:activator="{ props }">
      <v-badge
        v-if="unreadCount > 0"
        color="primary"
        class="noti-badge"
        :content="unreadCount"
      >
        <v-btn
          density="comfortable"
          icon="mdi-bell-outline"
          variant="plain"
          v-bind="props"
        />
      </v-badge>
      <v-btn
        v-else
        density="comfortable"
        icon="mdi-bell-outline"
        variant="plain"
        v-bind="props"
      />
    </template>

    <v-card width="450" rounded="lg">
      <v-toolbar density="compact">
        <v-toolbar-title>Уведомления</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn
          v-if="items.length > 0"
          icon="mdi-delete-empty-outline"
          color="red"
          variant="text"
          :loading="loadingAll"
          @click="markReadAll"
        />
      </v-toolbar>

      <div v-if="loading" class="flex justify-center items-center p-12">
        <v-progress-circular color="primary" indeterminate :size="30" :width="2" />
      </div>

      <v-list v-else lines="two" density="comfortable" max-height="450">
        <v-list-item v-if="items.length == 0" class="text-center">
          Нету уведомлений
        </v-list-item>

        <v-list-item
          v-else
          v-for="(item, index) in items"
          :key="index"
          :title="item.data.title"
          :subtitle="item.data.subtitle"
          :disabled="item.loading"
          @click="openNotification(item)"
        >
          <template #prepend>
            <v-img
              :width="50"
              :height="70"
              rounded
              cover
              :src="$media.image(item.data.image)"
              class="mr-4"
            />
          </template>
          <template #append>
            <v-btn
              density="comfortable"
              size="x-small"
              variant="tonal"
              icon="mdi-close"
              class="ml-4"
              :loading="item.loading"
              @click.stop="markRead(item)"
            />
          </template>
        </v-list-item>
      </v-list>
    </v-card>
  </v-menu>
</template>
