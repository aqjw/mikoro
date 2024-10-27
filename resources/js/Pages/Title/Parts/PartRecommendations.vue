<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from 'vue-toast-notification';
import CardTitle from '@/Components/Card/CardTitle.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  titleId: {
    type: Number,
    required: true,
  },
});

const $toast = useToast();

const items = ref([]);
const loading = ref(true);

onMounted(() => {
  loadItems();
});

const loadItems = () => {
  loading.value = true;
  axios
    .get(route('upi.title.recommendations', props.titleId))
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
  <div class="relative">
    <div v-if="loading">
      <v-progress-circular color="primary" indeterminate :size="20" :width="2" />
    </div>

    <div v-else class="grid grid-cols-7 gap-4 mb-4">
      <div
        v-for="(item, index) in items"
        :key="index"
        class="hover:scale-105 duration-200"
      >
        <Link :href="route('title', item.slug)">
          <CardTitle :item="item" small />
        </Link>
      </div>
    </div>
  </div>
</template>
