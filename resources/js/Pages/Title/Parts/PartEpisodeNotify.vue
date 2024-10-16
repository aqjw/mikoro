<script setup>
import { nextTick, ref, watch } from 'vue';
import { formatCompactNumber, handleResponseError } from '@/Utils';
import DialogLoginRequires from '@/Components/Dialogs/DialogLoginRequires.vue';
import { useToast } from 'vue-toast-notification';
import { useUserStore } from '@/Stores/UserStore';
import { storeToRefs } from 'pinia';
import { toRefs } from 'vue';

const props = defineProps({
  title: {
    type: Object,
    required: true,
  },
});

const $toast = useToast();
const userStore = useUserStore();
const { isLogged } = storeToRefs(userStore);

const loginRequires = ref(null);
const loading = ref(false);
const selected = ref(props.title.episode_release_notifications);
const timer = ref(null);
const skipWatch = ref(false);

watch(selected, () => {
  if (skipWatch.value) {
    return;
  }

  clearTimeout(timer.value);
  timer.value = setTimeout(onUpdate, 1000);
});

const toggleAll = () => {
  if (selected.value.length === props.title.translations.length) {
    selected.value = [];
  } else {
    selected.value = props.title.translations.map((translation) => translation.id);
  }
};

const onUpdate = (value) => {
  if (!isLogged.value) {
    loginRequires.value.open();

    skipWatch.value = true;
    selected.value = [];
    nextTick(() => (skipWatch.value = false));

    return;
  }

  loading.value = true;
  axios
    .post(route('upi.title.episode_release_notifications', props.title.id), {
      translation_ids: selected.value,
    })
    .then(() => {
      $toast.success('Сохранено успешно');
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
  <div>
    <div v-if="loading" class="absolute right-2 top-2">
      <v-progress-circular color="primary" indeterminate :size="20" :width="2" />
    </div>

    <div class="flex gap-2 text-center mb-2">
      <div class="text-caption">Уведомить о выходе новой серии</div>
      <v-btn
        variant="outlined"
        color="primary"
        density="compact"
        class="text-none"
        @click="toggleAll"
      >
        Все
      </v-btn>
    </div>

    <v-item-group
      v-model="selected"
      selected-class="bg-primary"
      class="flex flex-wrap gap-2"
      multiple
    >
      <v-item
        v-for="(item, index) in title.translations"
        :key="index"
        :value="item.id"
        v-slot="{ selectedClass, toggle }"
      >
        <v-chip :class="selectedClass" density="compact" label @click="toggle">
          {{ item.title }}
        </v-chip>
      </v-item>
    </v-item-group>

    <DialogLoginRequires ref="loginRequires" />
  </div>
</template>
