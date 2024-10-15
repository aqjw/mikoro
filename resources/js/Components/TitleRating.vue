<script setup>
import { toRefs } from 'vue';
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: Object,
    required: true,
  },
  type: {
    type: String,
    default: null,
  },
});

const { title, type } = toRefs(props);

const rating = computed(() => {
  if (type.value === 'shikimori') {
    return title.value.shikimori_rating;
  }

  return title.value.rating ? title.value.rating : title.value.shikimori_rating;
});

const color = computed(() => {
  if (rating.value >= 7) {
    return 'green-darken-3';
  } else if (rating.value >= 5) {
    return 'yellow-darken-3';
  }
  return 'red-darken-3';
});
</script>

<template>
  <v-chip v-if="rating > 0" density="compact" variant="flat" :color="color" class="!p-1">
    <span class="text-xs font-semibold">
      {{ rating.toFixed(1) }}
    </span>
  </v-chip>
</template>
