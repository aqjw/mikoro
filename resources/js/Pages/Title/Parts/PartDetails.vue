<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  title: {
    type: Object,
    required: true,
  },
});
</script>

<template>
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
          {{ title.status }}
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
              v-for="studio in title.studios"
              :key="studio.slug"
              :href="route('catalog.studio', studio.slug)"
            >
              <v-chip
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
          <span class="font-semibold">Страны:</span>
        </td>
        <td class="align-top py-2">
          <div class="d-flex gap-1 flex-wrap">
            <Link
              v-for="country in title.countries"
              :key="country.slug"
              :href="route('catalog.country', country.slug)"
            >
              <v-chip
                label
                variant="tonal"
                color="primary"
                density="compact"
                @click="() => {}"
              >
                {{ country.name }}
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
              v-for="genre in title.genres"
              :key="genre.slug"
              :href="route('catalog.genre', genre.slug)"
            >
              <v-chip
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
</template>
