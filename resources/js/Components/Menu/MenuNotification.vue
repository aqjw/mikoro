<script setup>
import { storeToRefs } from 'pinia';
import { useNotificationStore } from '@/Stores/NotificationStore';

const notificationStore = useNotificationStore();
const { lastItems, unreadCount } = storeToRefs(notificationStore);

const items = [
  {
    prependAvatar: 'https://animestars.org/uploads/posts/2024-04/97d5fb3943_1.webp',
    title: 'Операция: Семейка Ёдзакура',
    subtitle: `Вышла 3 серия в озвучке AnimeVost`,
  },
  {
    prependAvatar: 'https://animestars.org/uploads/posts/2024-08/e17f4fa7e2_11.webp',
    title: 'Палач богов: Между смертным и божественным царством',
    subtitle: `Вышла 7 серия в озвучке AniMaunt`,
  },
  {
    prependAvatar: 'https://animestars.org/uploads/posts/2024-07/3b6a62433f_1.webp',
    title: 'Расколотая битвой синева небес 5 сезон',
    subtitle: `Вышла 114 серия в озвучке АниСтар Многоголосый`,
  },
];
</script>

<template>
  <v-menu rounded location="bottom end" origin="auto" :close-on-content-click="false">
    <template v-slot:activator="{ props }">
      <v-badge v-if="unreadCount > 0" color="primary" :content="unreadCount">
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

    <v-card min-width="450" max-width="450">
      <!-- <v-card class="mx-auto" max-width="450"> -->
      <v-toolbar color="primary" density="comfortable">
        <!-- <v-btn icon="mdi-bell-outline" variant="text"></v-btn> -->
        <v-toolbar-title>Notifications</v-toolbar-title>

        <!-- <v-spacer></v-spacer> -->

        <!-- <v-btn icon="mdi-magnify" variant="text"></v-btn> -->
      </v-toolbar>

      <v-list lines="two" density="comfortable">
        <v-list-item
          v-for="(item, index) in items"
          :key="index"
          :title="item.title"
          :subtitle="item.subtitle"
          @click="() => {}"
        >
          <template #prepend>
            <v-img
              :width="50"
              :height="50"
              rounded
              aspect-ratio="1/1"
              cover
              :src="item.prependAvatar"
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
            />
          </template>
        </v-list-item>
      </v-list>
    </v-card>
  </v-menu>
</template>
