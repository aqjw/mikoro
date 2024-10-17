<script setup>
import TitleRating from '@/Components/TitleRating.vue';

defineProps({
  item: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <v-card
    class="bg-zinc-200 rounded-md h-80"
    rounded="lg"
    elevation="6"
    @click="() => {}"
  >
    <v-img
      height="90%"
      :lazy-src="$media.placeholder(item.poster)"
      :src="$media.original(item.poster)"
      cover
    >
      <v-chip
        label
        density="compact"
        variant="flat"
        color="primary"
        class="!rounded-none !rounded-br-lg !absolute top-0 left-0"
      >
        <span v-if="item.single_episode">Фильм</span>
        <span v-else>{{ item.last_episode }} серия</span>
      </v-chip>

      <TitleRating :value="item.shikimori_rating" class="!absolute bottom-2 right-2" />
    </v-img>

    <div class="truncate px-2 pt-1">
      <span class="font-semibold">{{ item.title }}</span>
      <v-tooltip
        v-if="item.title.length > 22"
        activator="parent"
        location="top"
        :open-delay="350"
      >
        {{ item.title }}
      </v-tooltip>
    </div>
  </v-card>
</template>
