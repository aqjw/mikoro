<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import FullLayout from '@/Layouts/FullLayout.vue';
import PartBookmarkTabs from './Parts/PartBookmarkTabs.vue';
import PartHistory from './Parts/PartHistory.vue';
import PartHeatmap from './Parts/PartHeatmap.vue';

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
            <div class="h-56 w-56 bg-stone-300 rounded-full"></div>
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
