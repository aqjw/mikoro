<script setup>
import { computed, ref, toRefs, watch, onMounted } from 'vue';
import CardTitle from '@/Components/Card/CardTitle.vue';
import TitleRating from '@/Components/TitleRating.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import 'vue3-carousel/dist/carousel.css';
import { Carousel, Slide, Navigation } from 'vue3-carousel';
import Player from '@/Components/Player.vue';
import CardComment from '@/Components/Card/CardComment.vue';
import SectionComments from '@/Components/Sections/SectionComments.vue';

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
  genres,
  translations,
} = toRefs(props.title);

const isReleased = computed(() => status.value === 'released');
</script>

<template>
  <Head :title="title.title" />

  <AppLayout>
    <div>
      <div class="bg-second p-4 rounded-lg shadow-lg">
        <div>
          <div
            v-if="poster"
            class="rounded-br-3xl float-left w-96 -ml-8 -mt-8 mb-4 p-4 mr-4 bg-main"
          >
            <div class="rounded-lg overflow-hidden shadow-lg shadow-black/40">
              <v-img
                :lazy-src="$media.placeholder(poster)"
                :src="$media.original(poster)"
                cover
              />
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
                        Эпизод {{ last_episode + ' из ' + last_episode }}
                      </span>
                      <span v-else-if="episodes_count > last_episode">
                        Эпизод {{ last_episode + ' из ' + episodes_count }}
                      </span>
                      <span v-else-if="last_episode >= episodes_count">
                        Эпизод {{ last_episode }}
                      </span>
                    </div>
                  </v-chip>
                </div>
              </div>

              <div class="text-sm opacity-80 mt-1">{{ title.other_title }}</div>
            </div>

            <v-table density="compact">
              <tbody>
                <tr>
                  <td class="align-top py-2">
                    <span class="font-semibold">Год:</span>
                  </td>
                  <td class="align-top py-2">
                    <span>{{ title.year }}</span>
                  </td>
                </tr>

                <tr>
                  <td class="align-top py-2">
                    <span class="font-semibold">Статус:</span>
                  </td>
                  <td class="align-top py-2">
                    <Link :href="route('catalog.status', title.status)">
                      <v-chip
                        size="small"
                        label
                        variant="tonal"
                        color="primary"
                        density="compact"
                        @click="() => {}"
                      >
                        {{ title.status }}
                      </v-chip>
                    </Link>
                  </td>
                </tr>

                <tr>
                  <td class="align-top py-2">
                    <span class="font-semibold">Длительность:</span>
                  </td>
                  <td class="align-top py-2">
                    <span>{{ title.duration }} минут</span>
                  </td>
                </tr>

                <tr>
                  <td class="align-top py-2">
                    <span class="font-semibold">Ограничения:</span>
                  </td>
                  <td class="align-top py-2">
                    <span>+{{ title.minimal_age }}</span>
                  </td>
                </tr>

                <tr>
                  <td class="align-top py-2">
                    <span class="font-semibold">Студии:</span>
                  </td>
                  <td class="align-top py-2">
                    <div class="d-flex gap-1 flex-wrap">
                      <Link
                        v-for="studio in studios"
                        :key="studio.slug"
                        :href="route('catalog.studio', studio.slug)"
                      >
                        <v-chip
                          size="small"
                          label
                          variant="tonal"
                          color="primary"
                          density="compact"
                          @click="() => {}"
                        >
                          {{ studio.name }}
                        </v-chip>
                      </Link>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-top py-2">
                    <span class="font-semibold">Жанры:</span>
                  </td>
                  <td class="align-top py-2">
                    <div class="d-flex gap-1 flex-wrap">
                      <Link
                        v-for="genre in genres"
                        :key="genre.slug"
                        :href="route('catalog.genre', genre.slug)"
                      >
                        <v-chip
                          size="small"
                          label
                          variant="tonal"
                          color="primary"
                          density="compact"
                          @click="() => {}"
                        >
                          {{ genre.name }}
                        </v-chip>
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </v-table>

            <!-- <div class="pt-2">
              <span class="font-semibold mr-1">Описание:</span>

            </div> -->
          </div>
        </div>

        <div class="mt-8 w-full clear-left">
          <div>
            <div class="text-xl font-medium mb-2">Описание:</div>
            <div class="opacity-80">{{ title.description }}</div>
          </div>
        </div>

        <div v-if="screenshots.length" class="mt-8 w-full">
          <div>
            <div class="text-xl font-medium mb-4">Скриншоты из первого эпизода:</div>
            <carousel :itemsToShow="screenshots.length > 3 ? 3.25 : 3">
              <slide v-for="(screenshot, index) in screenshots" :key="index" class="px-1">
                <v-img
                  :src="$media.original(screenshot)"
                  :lazy-src="$media.placeholder(screenshot)"
                  cover
                />
              </slide>
              <slide
                v-if="screenshots.length < 3"
                v-for="stub in 3 - screenshots.length"
                :key="stub"
              />

              <template #addons>
                <navigation v-if="screenshots.length > 3" />
              </template>
            </carousel>
          </div>
        </div>
      </div>

      <div class="bg-gray-300 h-[30rem] my-4 rounded-lg shadow-lg overflow-hidden">
        <!-- <div class="bg-gray-300 h-[20rem] my-4 rounded-lg shadow-lg overflow-hidden"> -->
        <Player :poster="$media.original(screenshots[0])" :title-id="title.id" />
      </div>

      <div class="bg-second rounded-lg shadow-lg my-4">
        <SectionComments :title="title" />
      </div>
    </div>
  </AppLayout>
</template>
