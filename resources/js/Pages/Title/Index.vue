<script setup>
import SectionComments from '@/Components/Comments/SectionComments.vue';
import Player from '@/Components/Player.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, toRefs } from 'vue';
import PartBookmark from './Parts/PartBookmark.vue';
import PartDetails from './Parts/PartDetails.vue';
import PartEpisodeNotify from './Parts/PartEpisodeNotify.vue';
import PartRating from './Parts/PartRating.vue';
import PartRecommendations from './Parts/PartRecommendations.vue';
import PartRelated from './Parts/PartRelated.vue';
import PartScreenshots from './Parts/PartScreenshots.vue';

const props = defineProps({
  title: {
    type: Object,
  },
});

const {
  poster,
  status,
  screenshots,
  last_episode,
  episodes_count,
  studios,
  countries,
  genres,
  translations,
  related,
} = toRefs(props.title);

const isReleased = computed(() => status.value === 'released');
</script>

<template>
  <Head :title="title.title" />

  <AppLayout>
    <div class="space-y-4">
      <div class="bg-second p-4 rounded-lg shadow-lg">
        <div>
          <div
            v-if="poster"
            class="rounded-br-3xl float-left w-96 -ml-8 -mt-8 mb-4 p-4 mr-4 bg-main"
          >
            <div class="rounded-lg overflow-hidden shadow-lg shadow-black/40">
              <v-img :src="$media.image(poster)" cover />
            </div>
          </div>

          <div>
            <div class="mb-2">
              <div class="flex justify-between items-start">
                <div class="text-lg font-semibold truncate pr-4">{{ title.title }}</div>
                <div v-if="last_episode">
                  <v-chip label density="comfortable" color="primary">
                    <div class="font-medium">
                      <span v-if="isReleased">
                        Серия {{ last_episode + ' из ' + last_episode }}
                      </span>
                      <span v-else-if="episodes_count > last_episode">
                        Серия {{ last_episode + ' из ' + episodes_count }}
                      </span>
                      <span v-else-if="last_episode >= episodes_count">
                        Серия {{ last_episode }}
                      </span>
                    </div>
                  </v-chip>
                </div>
              </div>

              <div class="text-sm opacity-80 mt-1">{{ title.other_title }}</div>
            </div>

            <PartDetails :title="title" />
          </div>
        </div>

        <div class="clear-left"></div>

        <div v-if="title.description">
          <div class="text-xl font-medium mb-2">Описание:</div>
          <div class="opacity-80">{{ title.description }}</div>
        </div>
      </div>

      <PartScreenshots v-if="screenshots.length > 1" :title="title" />

      <div>
        <div class="flex gap-4 mb-2">
          <div class="w-8/12">
            <span class="text-xl font-medium mr-1">Хронология</span>
            <span>({{ related.length }})</span>
          </div>

          <div class="w-4/12">
            <span class="text-xl font-medium">Рейтинг</span>
          </div>
        </div>

        <div class="flex gap-4 items-stretch">
          <div class="w-8/12 bg-second rounded-lg shadow-lg overflow-hidden">
            <PartRelated :title="title" />
          </div>

          <div
            :class="[
              'w-4/12 overflow-hidden relative',
              'bg-second rounded-lg shadow-lg',
              'flex items-center justify-center',
            ]"
          >
            <PartRating :title="title" />
          </div>
        </div>
      </div>

      <div class="bg-second rounded-lg shadow-lg p-4 flex relative">
        <PartBookmark :title="title" class="w-5/12" />
        <div class="divider-vertical mx-4"></div>
        <PartEpisodeNotify :title="title" class="w-7/12" />
      </div>

      <div
        class="bg-second h-[30rem] rounded-lg shadow-lg overflow-hidden duration-200 hover:scale-[1.025]"
      >
        <Player
          :poster="$media.original(screenshots[0])"
          :title-id="title.id"
          :is-single-episode="title.single_episode"
        />
      </div>

      <div>
        <div class="text-xl font-medium mb-2">Рекомендации</div>
        <PartRecommendations :title-id="title.id" />
      </div>

      <div class="mb-4 mt-6">
        <div class="bg-second rounded-lg shadow-lg">
          <SectionComments :title="title" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
