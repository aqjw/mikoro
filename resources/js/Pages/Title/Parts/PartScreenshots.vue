<script setup>
import { ref } from 'vue';
import DialogGallery from '@/Components/Dialogs/DialogGallery.vue';
import 'vue3-carousel/dist/carousel.css';
import { Carousel, Slide, Navigation } from 'vue3-carousel';

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
      <span>({{ title.screenshots.length }})</span>
    </div>

    <carousel :itemsToShow="title.screenshots.length > 3 ? 3.25 : 3">
      <slide
        v-for="(screenshot, index) in title.screenshots"
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
        v-if="title.screenshots.length < 3"
        v-for="stub in 3 - title.screenshots.length"
        :key="stub"
      />

      <template #addons>
        <navigation v-if="title.screenshots.length > 3" />
      </template>
    </carousel>

    <DialogGallery ref="dialogGallery" :images="title.screenshots" />
  </div>
</template>
