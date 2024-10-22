<script setup>
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useToast } from 'vue-toast-notification';
import { handleResponseError } from '@/Utils';

const props = defineProps({
  settings: Object,
  translations: Array,
  qualities: Array,
  visibilities: Array,
});

const $toast = useToast();

const tab = ref('notifications');
const saving = ref(false);

const options = ref({
  quality: props.qualities,
  translation: props.translations,
  visibility: props.visibilities,
  // { value: 1, label: 'Все пользователи' },
  // { value: 2, label: 'Только авторизованные пользователи' },
  // { value: 3, label: 'Никто' },
});

const settings = ref({
  notifications: {
    comment_reply: props.settings.notifications.comment_reply,
    site_updates: props.settings.notifications.site_updates,
    news_alerts: props.settings.notifications.news_alerts,
  },
  player: {
    default_quality: props.settings.player.default_quality,
    default_translation: props.settings.player.default_translation,
  },
  privacy: {
    list_visibility: props.settings.privacy.list_visibility,
    history_visibility: props.settings.privacy.history_visibility,
  },
});

const timer = ref(null);

watch(
  settings,
  () => {
    clearTimeout(timer.value);
    timer.value = setTimeout(updateSettings, 1000);
  },
  { deep: true }
);

const updateSettings = () => {
  saving.value = true;
  axios
    .post(route('upi.settings.save'), {
      settings: settings.value,
    })
    .then(({ data }) => {
      $toast.success('Сохранено успешно');
    })
    .catch(({ response }) => {
      $toast.error(handleResponseError(response));
    })
    .finally(() => {
      saving.value = false;
    });
};
</script>

<template>
  <Head title="Настройки" />

  <AppLayout>
    <div class="relative">
      <div class="inline-flex items-center gap-2 mb-4">
        <div class="text-xl font-medium">Настройки</div>
        <v-progress-circular
          v-if="saving"
          color="primary"
          indeterminate
          :size="18"
          :width="2"
        />
      </div>
      <div class="bg-second rounded-lg shadow-lg overflow-hidden">
        <v-card elevation="0">
          <v-tabs v-model="tab" density="compact" slider-color="primary">
            <v-tab
              value="notifications"
              class="text-none"
              prepend-icon="mdi-bell-outline"
            >
              Уведомления
            </v-tab>
            <v-tab
              value="player"
              class="text-none"
              prepend-icon="mdi-play-circle-outline"
            >
              Плеер
            </v-tab>
            <v-tab value="privacy" class="text-none" prepend-icon="mdi-lock-outline">
              Конфиденциальность
            </v-tab>
          </v-tabs>

          <v-card-text class="!p-0">
            <v-tabs-window v-model="tab">
              <!-- Notifications Tab -->
              <v-tabs-window-item value="notifications" class="p-4">
                <v-switch
                  v-model="settings.notifications.comment_reply"
                  color="primary"
                  hide-details
                  density="comfortable"
                  label="Получать уведомления о новых ответах на мои комментарии"
                />
                <v-switch
                  v-model="settings.notifications.site_updates"
                  color="primary"
                  hide-details
                  density="comfortable"
                  label="Получать уведомления об обновлениях сайта"
                />
                <v-switch
                  v-model="settings.notifications.news_alerts"
                  color="primary"
                  hide-details
                  density="comfortable"
                  label="Получать новостные уведомления"
                />
              </v-tabs-window-item>

              <!-- Player Tab -->
              <v-tabs-window-item value="player" class="p-4 inline-flex gap-4">
                <v-select
                  v-model="settings.player.default_translation"
                  label="Озвучка по умолчанию"
                  :items="options.translation"
                  :item-props="
                    (item) => ({
                      title: item.title,
                      subtitle: `Озвучено тайтлов: ${item.titles_count}`,
                    })
                  "
                  item-title="title"
                  item-value="id"
                  variant="filled"
                  density="comfortable"
                  searchable
                  class="w-1/2"
                />

                <v-select
                  v-model="settings.player.default_quality"
                  label="Качество по умолчанию"
                  :items="options.quality"
                  item-title="name"
                  item-value="id"
                  variant="filled"
                  density="comfortable"
                  class="w-1/2"
                />
              </v-tabs-window-item>

              <!-- Privacy Tab -->
              <v-tabs-window-item value="privacy" class="p-4 inline-flex gap-4">
                <v-select
                  v-model="settings.privacy.list_visibility"
                  label="Кто может видеть мои списки"
                  :items="options.visibility"
                  variant="filled"
                  density="comfortable"
                  item-title="name"
                  item-value="id"
                  searchable
                  class="w-1/2"
                />
                <v-select
                  v-model="settings.privacy.history_visibility"
                  label="Кто может видеть мою историю просмотров"
                  :items="options.visibility"
                  variant="filled"
                  density="comfortable"
                  item-title="name"
                  item-value="id"
                  searchable
                  class="w-1/2"
                />
              </v-tabs-window-item>
            </v-tabs-window>
          </v-card-text>
        </v-card>
      </div>
    </div>
  </AppLayout>
</template>
