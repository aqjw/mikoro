<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useTheme } from 'vuetify';
import { useUserStore } from '@/Stores/UserStore';
import { storeToRefs } from 'pinia';
import MenuProfile from '@/Components/Menu/MenuProfile.vue';
import MenuNotification from '@/Components/Menu/MenuNotification.vue';
import useSession from '@/Composables/useSession';
import { compile } from 'vue';

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
</script>

<template>
  <v-app-bar flat>
    <v-container>
      <v-row>
        <v-col cols="2" class="d-flex align-center">
          <Link
            :href="route('home')"
            class="text-2xl font-semibold mr-10 text-zinc-800 dark:text-zinc-200 no-underline"
          >
            Mikoro.tv
          </Link>
        </v-col>

        <v-col class="mx-auto d-flex align-center justify-center">
          <v-responsive max-width="200">
            <v-text-field
              density="compact"
              label="Search"
              rounded="lg"
              variant="solo-filled"
              flat
              hide-details
              single-line
            ></v-text-field>
          </v-responsive>

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
                @click="toggleTheme"
              />

              <MenuNotification />

              <MenuProfile :user="user" />
            </template>

            <v-btn
              v-else
              :to="route('login')"
              variant="flat"
              class="text-none"
              color="primary"
            >
              Login
            </v-btn>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </v-app-bar>
</template>
