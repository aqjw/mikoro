<script setup>
import { ref } from 'vue';
import TitleRating from '@/Components/TitleRating.vue';

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  small: Boolean,
});

const tooltipMinLength = ref(props.small ? 13 : 22);
</script>

<template>
  <v-card
    :class="['bg-zinc-200 rounded-md', small ? 'h-52' : 'h-80']"
    rounded="lg"
    :elevation="small ? 4 : 6"
    @click="() => {}"
  >
    <v-img
      :height="small ? '85%' : '90%'"
      :src="$media.image(item.poster)"
      class="bg-gray-200 dark:bg-gray-700"
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
      <span :class="['font-semibold', small ? 'text-sm' : '']">{{ item.title }}</span>
      <v-tooltip
        v-if="item.title.length > tooltipMinLength"
        activator="parent"
        location="top"
        :open-delay="350"
      >
        {{ item.title }}
      </v-tooltip>
    </div>
  </v-card>
</template>
