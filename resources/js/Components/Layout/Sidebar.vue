<script setup>
const links = [
//   'Новинки',
  'Аниме',
  'Дунхуа',
  'Пересказы',
  // 'Подборки',
  'Жанры',
  'Топ 100',
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
