<script setup>
import CardTitle from '@/Components/Card/CardTitle.vue';
import TitleRating from '@/Components/TitleRating.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  titles: {
    type: Array,
  },
});
</script>

<template>
  <Head title="Anime" />

  <AppLayout bg-transparent>
    <div>
      <div class="grid grid-cols-4 gap-4">
        <div
          v-for="(item, index) in titles"
          :key="index"
          class="hover:scale-105 duration-200"
        >
          <CardTitle :item="item" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
