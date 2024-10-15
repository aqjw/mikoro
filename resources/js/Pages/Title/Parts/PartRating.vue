<script setup>
import { ref } from 'vue';
import { formatCompactNumber } from '@/Utils';
import DialogLoginRequires from '@/Components/Dialogs/DialogLoginRequires.vue';
import { useToast } from 'vue-toast-notification';
import { useUserStore } from '@/Stores/UserStore';
import { storeToRefs } from 'pinia';

const props = defineProps({
  title: Object,
  required: true,
});

const $toast = useToast();
const userStore = useUserStore();
const { isLogged } = storeToRefs(userStore);

const loading = ref(false);
const loginRequires = ref(null);

const onUpdate = (value) => {
  if (!isLogged.value) {
    loginRequires.value.open();
    return;
  }

  loading.value = true;
  axios
    .post(route('upi.title.rating', props.title.id), { value })
    .then(({ data }) => {
      props.title.rating = data.rating;
      props.title.rating_votes = data.rating_votes;
      props.title.user_voted = true;
    })
    .catch(() => {
      //
    })
    .finally(() => {
      loading.value = false;
    });
};

const getColor = (value) => {
  if (!value) {
    return 'primary';
  }
  if (value >= 7) {
    return 'green-darken-3';
  } else if (value >= 5) {
    return 'yellow-darken-3';
  }
  return 'red-darken-3';
};
</script>

<template>
  <div class="py-4 relative">
    <div v-if="loading" class="absolute right-2 top-2">
      <v-progress-circular color="primary" indeterminate :size="20" :width="2" />
    </div>

    <div class="flex align-center flex-col">
      <div class="text-h3">
        <span class="text-sm">Наш</span>
        <span class="mx-1">{{ title.rating.toFixed(1) }}</span>
        <span class="text-sm">({{ formatCompactNumber(title.rating_votes) }})</span>
      </div>

      <v-rating
        :model-value="title.rating"
        :active-color="getColor(title.rating)"
        :color="getColor(title.rating)"
        length="10"
        half-increments
        density="compact"
        hover
        @update:model-value="onUpdate"
      />

      <div v-if="title.user_voted" class="mt-1 text-xs opacity-50">Ваш голос учтен</div>
    </div>

    <v-divider class="my-4" />

    <div class="flex align-center flex-col">
      <div class="text-h3">
        <span class="text-sm">Shikimori</span>
        <span class="mx-1">{{ title.shikimori_rating.toFixed(1) }}</span>
        <span class="text-sm">({{ formatCompactNumber(title.shikimori_votes) }})</span>
      </div>

      <v-rating
        :model-value="title.shikimori_rating"
        :active-color="getColor(title.shikimori_rating)"
        :color="getColor(title.shikimori_rating)"
        length="10"
        half-increments
        density="compact"
        readonly
      />
    </div>

    <DialogLoginRequires ref="loginRequires" />
  </div>
</template>
