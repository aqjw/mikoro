<script setup>
import { computed } from 'vue';
import TitleRating from '@/Components/TitleRating.vue';

defineProps({
  item: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <v-list-item :title="item.title" @click="() => {}">
    <template #subtitle>
      <div class="d-flex gap-1 flex-wrap mt-1">
        <v-chip size="small" variant="tonal" rounded="sm" density="compact">
          {{ item.year }}
        </v-chip>

        <v-chip size="small" variant="tonal" rounded="sm" density="compact">
          эпизод {{ item.episodes }}
        </v-chip>
      </div>

      <div class="d-flex gap-1 flex-wrap mt-1">
        <v-chip
          v-for="genre in item.genres"
          :key="genre.slug"
          size="small"
          variant="tonal"
          color="primary"
          density="compact"
          @click="() => {}"
        >
          {{ genre.name }}
        </v-chip>
      </div>
    </template>
    <template #prepend>
      <v-img
        :width="65"
        :height="80"
        rounded
        aspect-ratio="1/1"
        cover
        :lazy-src="$media.placeholder(item.poster)"
        :src="$media.original(item.poster)"
        class="mr-4"
      />
    </template>
    <template #append>
      <TitleRating :value="item.shikimori_rating" class="!p-1" />
    </template>
  </v-list-item>
</template>
