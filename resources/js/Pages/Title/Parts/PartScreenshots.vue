<script setup>
import DialogGallery from '@/Components/Dialogs/DialogGallery.vue';
import { ref } from 'vue';

const dialogGallery = ref(null);

defineProps({
  title: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <div>
    <div class="mb-2">
      <span class="text-xl font-medium mr-1">Скриншоты</span>
      <span>({{ screenshots.length }})</span>
    </div>

    <carousel :itemsToShow="screenshots.length > 3 ? 3.25 : 3">
      <slide
        v-for="(screenshot, index) in screenshots"
        :key="index"
        class="px-1 cursor-pointer"
        v-ripple
        @click="() => dialogGallery.open(index)"
      >
        <v-img
          :src="$media.original(screenshot)"
          :lazy-src="$media.placeholder(screenshot)"
          cover
          class="rounded-md"
        />
      </slide>
      <slide
        v-if="screenshots.length < 3"
        v-for="stub in 3 - screenshots.length"
        :key="stub"
      />

      <template #addons>
        <navigation v-if="screenshots.length > 3" />
      </template>
    </carousel>

    <DialogGallery ref="dialogGallery" :images="screenshots" />
  </div>
</template>
