<script setup>
import { initials } from '@/Utils';

defineProps({
  user: Object,
});
</script>

<template>
  <v-menu rounded location="bottom end" origin="auto" :close-on-content-click="false">
    <template v-slot:activator="{ props }">
      <v-btn icon v-bind="props">
        <v-avatar color="brown" size="small">
          <span class="text-xs">{{ initials(user.name) }}</span>
        </v-avatar>
      </v-btn>
    </template>

    <v-card width="200px">
      <v-card-text class="!p-0">
        <div class="mx-auto text-center p-4">
          <v-avatar color="brown" size="large">
            <span class="text-base">{{ initials(user.name) }}</span>
          </v-avatar>

          <div class="text-lg mt-2">{{ user.name }}</div>
        </div>

        <v-list density="compact" class="!pt-0">
          <v-list-item @click="() => {}">
            <div class="flex items-center gap-4 pr-2">
              <v-icon size="small" icon="mdi-account-outline" variant="tonal" />
              <span class="text-sm">{{ 'Profile' }}</span>
            </div>
          </v-list-item>

          <v-list-item :to="route('settings')">
            <div class="flex items-center gap-4 pr-2">
              <v-icon size="small" icon="mdi-cog-outline" variant="tonal" />
              <span class="text-sm">{{ 'Settings' }}</span>
            </div>
          </v-list-item>

          <v-list-item
            base-color="red-lighten-1"
            :to="{
              href: route('logout'),
              method: 'post',
            }"
          >
            <div class="flex items-center gap-4 pr-2">
              <v-icon size="small" icon="mdi-logout" variant="tonal" />
              <span class="text-sm">{{ 'Log out' }}</span>
            </div>
          </v-list-item>
        </v-list>
      </v-card-text>
    </v-card>
  </v-menu>
</template>
