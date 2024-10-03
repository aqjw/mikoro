<script setup>
import CardTitle from '@/Components/Card/CardTitle.vue';
import FilterSort from '@/Components/Layout/FilterSort.vue';
import TitleRating from '@/Components/TitleRating.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, watch } from 'vue';

const props = defineProps({
  title: {
    type: Object,
  },
});
</script>

<template>
  <Head :title="title.title" />

  <AppLayout bg-transparent>
    <div>
      {{ title }}
    </div>
  </AppLayout>
</template>
