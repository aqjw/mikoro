<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { useCatalogStore } from '@/Stores/CatalogStore';
import { computed, ref } from 'vue';
import MenuList from '../Menu/MenuList.vue';

const catalogStore = useCatalogStore();

const onAction = (e, item) => {
  e.preventDefault();
  router.get(item.href);
};

// TODO: iaponiia | kitai

const items = ref([
  {
    label: 'Новинки',
    href: route('home'),
    isActive: () => route().current('home'),
    action: (e, item) => {
      catalogStore.$resetAll();
      catalogStore.$resetItems();
      onAction(e, item);
    },
  },
  {
    label: 'Япония',
    href: route('catalog.country', 'iaponiia'),
    isActive: () => route().current('catalog.country', 'iaponiia'),
    action: onAction,
  },
  {
    label: 'Китай',
    href: route('catalog.country', 'kitai'),
    isActive: () => route().current('catalog.country', 'kitai'),
    action: onAction,
  },
  {
    label: 'Жанры',
    list: true,
    loadItems: (finish) => {
      axios.get(route('upi.title.genres')).then(({ data }) => {
        finish(data.genres);
      });
    },
    actionItem: (item) => {
      router.get(route('catalog.genre', item.slug));
    },
  },
  //   'Пересказы',
  //   'Подборки',
  //   'Топ 100',
]);
</script>

<template>
  <v-list rounded="lg">
    <template v-for="(item, index) in items" :key="index">
      <MenuList
        v-if="item.list"
        :label="item.label"
        :load-items="item.loadItems"
        :action-item="item.actionItem"
      />
      <v-list-item
        v-else
        :title="item.label"
        :href="item.href"
        :active="item.isActive()"
        color="primary"
        @click="(e) => item.action(e, item)"
      />
    </template>
  </v-list>
</template>
