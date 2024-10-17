<script setup>
import TitleRating from '@/Components/TitleRating.vue';
import { Link } from '@inertiajs/vue3';

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
          <span v-if="item.single_episode">Фильм</span>
          <span v-else>{{ item.episodes }} серия</span>
        </v-chip>
      </div>

      <div class="d-flex gap-1 flex-wrap mt-1">
        <Link
          v-for="genre in item.genres"
          :key="genre.slug"
          :href="route('catalog.genre', genre.slug)"
        >
          <v-chip
            size="small"
            variant="tonal"
            color="primary"
            density="compact"
            @click="() => {}"
          >
            {{ genre.name }}
          </v-chip>
        </Link>
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
      <TitleRating :value="item.shikimori_rating" />
    </template>
  </v-list-item>
</template>
