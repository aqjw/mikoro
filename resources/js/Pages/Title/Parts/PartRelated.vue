<script setup>
import DateManager from '@/Plugins/DateManager';
import TitleRating from '@/Components/TitleRating.vue';
import { onMounted } from 'vue';

const props = defineProps({
  title: {
    type: Object,
    required: true,
  },
});

onMounted(() => {
  if (props.title.related.length > 5) {
    scrollRelated();
  }
});

const scrollRelated = () => {
  const item = document.querySelector('#related-list .v-list-item--active');
  if (item) {
    const list = document.getElementById('related-list');
    list.scrollTop = item.offsetTop-45;
  }
};
</script>

<template>
  <v-list class="spacerless" max-height="250px" id="related-list">
    <v-list-item
      v-for="(item, index) in title.related"
      :key="index"
      :title="item.title"
      :active="item.id == title.id"
      color="primary"
      @click="$inertia.visit(route('title', item.slug))"
    >
      <template #subtitle>
        <span v-if="item.released_at" class="capitalize">
          {{ DateManager.format(item.released_at, 'LLLL y') }}
        </span>
        <span v-else>{{ item.year }}</span>
      </template>
      <template #prepend="{ isActive }">
        <v-badge
          color="primary"
          :content="Math.abs(index - title.related.length)"
          inline
          class="scale-110 relative"
        >
          <span
            v-if="isActive"
            class="absolute animate-ping inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"
          />
        </v-badge>
      </template>
      <template #append>
        <TitleRating :title="item" type="shikimori" />
      </template>
    </v-list-item>
  </v-list>
</template>
