<script setup>
defineProps({
  item: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <v-list-item :title="item.name" density="compact" @click="() => {}">
    <template #append> </template>
  </v-list-item>
</template>
