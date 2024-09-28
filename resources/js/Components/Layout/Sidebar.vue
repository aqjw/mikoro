<script setup>
const links = [
  'Новинки',
  'Китайские',
  'Фильмы',
  'Подборки',
  'Топ 100 аниме',
  'Расписание',
  'Жанры',
];
</script>

<template>
  <v-list rounded="lg">
    <v-list-item
      v-for="(link, index) in links"
      :key="index"
      :title="link"
      link
    ></v-list-item>
  </v-list>
</template>
