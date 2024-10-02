<script setup>
import { computed } from 'vue';

const props = defineProps({
  value: {
    type: Number,
  },
});

const color = computed(() => {
  if (props.value >= 8) {
    return 'green';
  } else if (props.value >= 6) {
    return 'yellow';
  }
  return 'red';
});
</script>

<template>
  <v-chip v-if="value > 0" density="compact" variant="flat" :color="color">
    <span class="text-xs font-semibold">
      {{ value.toFixed(1) }}
    </span>
  </v-chip>
</template>
