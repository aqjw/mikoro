<script setup>
import { nextTick, computed, onMounted, ref } from 'vue';
import { useTheme } from 'vuetify';
import { useToast } from 'vue-toast-notification';
import { getBookmarkIcon, getBookmarkColor, handleResponseError } from '@/Utils';
import DateManager from '@/Plugins/DateManager';
import Heatmap from '@/Components/Heatmap.vue';

const props = defineProps({
  userId: {
    type: Number,
    required: true,
  },
});

const $toast = useToast();

const loading = ref(true);
const items = ref([]);

onMounted(() => {
  loadItems();
});

const loadItems = () => {
  loading.value = true;

  axios
    .get(route('upi.profile.heatmap', { user: props.userId }))
    .then(({ data }) => {
      items.value = data.items;
    })
    .catch(({ response }) => {
      $toast.error(handleResponseError(response));
    })
    .finally(() => {
      loading.value = false;
    });
};
</script>

<template>
  <Heatmap :values="items" />
</template>
