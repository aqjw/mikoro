<script setup>
import FullLayout from '@/Layouts/FullLayout.vue';
import { initials } from '@/Utils';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import PartBookmarkTabs from './Parts/Profile/PartBookmarkTabs.vue';
import PartHeatmap from './Parts/Profile/PartHeatmap.vue';
import PartHistory from './Parts/Profile/PartHistory.vue';

const props = defineProps({
  user: Object,
});

const tab = ref('bookmarks');
</script>

<template>
  <Head title="Profile" />

  <FullLayout>
    <div class="pt-4">
      <div class="flex gap-6 items-start">
        <div class="flex-shrink-0 flex justify-center flex-col text-center w-[16rem]">
          <div class="bg-second shadow-lg p-4 rounded-full overflow-hidden">
            <v-img
              class="h-56 w-56 bg-zinc-400 dark:bg-zinc-500 rounded-full"
              :src="$media.image(user.avatar)"
            >
              <span
                v-if="!user.avatar"
                class="text-4xl font-semibold text-white absolute-center"
              >
                {{ initials(user.name) }}
              </span>
            </v-img>
          </div>

          <div class="text-xl font-bold mt-4">
            {{ user.name }}
          </div>

          <div class="text-md font-italic">
            {{ user.slug }}
          </div>
          <v-btn density="comfortable" class="text-none mt-2">Подписаться</v-btn>

          <PartHistory :user-id="user.id" class="mt-6" />
        </div>

        <div class="w-full">
          <div class="text-lg mb-2 font-semibold">График активности</div>
          <PartHeatmap :user-id="user.id" class="mb-4" />

          <div class="text-lg mb-2 font-semibold">Закладки</div>
          <PartBookmarkTabs :user-id="user.id" />
        </div>
      </div>
    </div>
  </FullLayout>
</template>
