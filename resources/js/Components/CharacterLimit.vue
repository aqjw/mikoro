<script setup>
import { toRefs } from 'vue';

const props = defineProps({
  value: {
    type: Number,
    required: true,
  },
  min: {
    type: Number,
    required: true,
  },
  max: {
    type: Number,
    required: true,
  },
});

const { value, min, max } = toRefs(props);
</script>

<template>
  <div v-if="value > 0">
    <span v-if="value < min" class="text-red-500">
      {{ min - value }}
    </span>
    <span
      v-else
      :class="{
        'text-gray-400': value < max,
        'text-red-500': value > max,
      }"
    >
      {{ value }} / {{ max }}
    </span>
  </div>
</template>
