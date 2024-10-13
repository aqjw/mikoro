<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  label: String,
  loadItems: Function,
  actionItem: Function,
});

const items = ref([]);
const loading = ref(false);
const isMenuOpen = ref(false);

const load = () => {
  if (!items.value.length) {
    loading.value = true;
    props.loadItems((data) => {
      items.value = data;
      loading.value = false;
    });
  }
};

watch(isMenuOpen, (newValue) => {
  if (newValue) {
    load();
  }
});
</script>

<template>
  <v-menu v-model="isMenuOpen" rounded location="end" :close-on-content-click="false">
    <template v-slot:activator="{ props }">
      <v-list-item :title="label" v-bind="props" />
    </template>

    <v-card min-width="200px">
      <v-card-text>
        <div v-if="loading" class="flex justify-center">
          <v-progress-circular indeterminate :size="30" :width="2" color="primary" />
        </div>
        <div v-else class="grid grid-cols-4 gap-2">
          <a
            v-for="(item, index) in items"
            :key="index"
            href="#"
            @click.prevent="actionItem(item)"
          >
            {{ item.name }}
          </a>
        </div>
      </v-card-text>
    </v-card>
  </v-menu>
</template>
