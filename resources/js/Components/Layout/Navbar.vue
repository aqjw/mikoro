<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useTheme } from 'vuetify';
import { storeToRefs, useUserStore } from '@/Stores';
import MenuProfile from '@/Components/Menu/MenuProfile.vue';
import MenuNotification from '@/Components/Menu/MenuNotification.vue';
import useSession from '@/Composables/useSession';
import DialogSearch from '../Dialogs/DialogSearch.vue';

const theme = useTheme();
const themeMode = ref(useSession.get('theme', 'light'));
const isDark = computed(() => themeMode.value === 'dark');

// class="backdrop-blur-md" :color="`rgba(${bgColor}, 0.8)`"
// const bgColor = computed(() => {
//   return isDark.value ? '33,33,33' : '255,255,255';
// });

const userStore = useUserStore();
const { isLogged, user } = storeToRefs(userStore);

function toggleTheme() {
  themeMode.value = isDark.value ? 'light' : 'dark';
  theme.global.name.value = themeMode.value;
  useSession.set('theme', themeMode.value);

  if (isDark.value) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
}

// TODO:
console.clear()
</script>

<template>
  <v-app-bar flat density="comfortable" class="!shadow-">
    <v-container>
      <v-row>
        <v-col cols="2" class="d-flex align-center">
          <Link
            :href="route('home')"
            class="text-2xl font-semibold mr-10 text-zinc-800 dark:text-zinc-200 no-underline"
          >
            mikoro.tv
          </Link>
        </v-col>

        <v-col class="mx-auto d-flex align-center justify-center">
          <DialogSearch />

          <v-spacer></v-spacer>

          <div class="d-flex align-center gap-3">
            <v-btn
              density="comfortable"
              :icon="isDark ? 'mdi-weather-night' : 'mdi-white-balance-sunny'"
              variant="plain"
              @click="toggleTheme"
            />

            <template v-if="isLogged">
              <v-btn
                density="comfortable"
                icon="mdi-bookmark-outline"
                variant="plain"
                :active="route().current('bookmarks')"
                active-color="primary"
                :to="route('bookmarks')"
              />

              <MenuNotification />

              <MenuProfile :user="user" />
            </template>

            <v-btn
              v-else
              :to="route('login')"
              variant="tonal"
              class="text-none"
              color="primary"
              density="comfortable"
            >
              Login
            </v-btn>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </v-app-bar>
</template>
