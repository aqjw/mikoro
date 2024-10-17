<script setup>
import { toRefs } from 'vue';
import { computed } from 'vue';

const props = defineProps({
  value: {
    type: Number,
    required: true,
  },
});

const { value } = toRefs(props);

const color = computed(() => {
  if (value.value >= 7) {
    return 'green-darken-3';
  } else if (value.value >= 5) {
    return 'yellow-darken-3';
  }
  return 'red-darken-3';
});
</script>

<template>
  <v-chip v-if="value > 0" density="compact" variant="flat" :color="color" class="!p-1">
    <span class="text-xs font-semibold">
      {{ value.toFixed(1) }}
    </span>
  </v-chip>
</template>
