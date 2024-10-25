<script setup>
import { nextTick, computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import CardComment from '@/Components/Comments/CardComment.vue';
import CardCommentEdit from '@/Components/Comments/CardCommentEdit.vue';
import TextEditor from '@/Components/TextEditor.vue';
import InfiniteScroll from '@/Components/InfiniteScroll.vue';
import DialogLoginRequires from '@/Components/Dialogs/DialogLoginRequires.vue';
import CharacterLimit from '@/Components/CharacterLimit.vue';
import CardTitle from '@/Components/Card/CardTitle.vue';
import { storeToRefs, useAppStore, useCommentStore, useUserStore } from '@/Stores';
import { handleResponseError } from '@/Utils';
import { useToast } from 'vue-toast-notification';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  userId: {
    type: Number,
    required: true,
  },
  bookmark: {
    type: String,
    required: true,
  },
});

const $toast = useToast();

const infiniteScroll = ref(null);
const loading = ref(false);
const sorting = ref('latest');
const total = ref(0);
const page = ref(1);
const items = ref([]);
const hasMore = ref(true);

const isEmpty = computed(() => {
  return !loading.value && total.value == 0;
});

watch(sorting, () => {
  setTimeout(() => {
    page.value = 1;
    infiniteScroll.value?.reload();
  }, 200);
});

onMounted(() => {
  nextTick(() => {
    infiniteScroll.value?.load();
  });
});

const onLoad = (finish) => {
  loading.value = true;

  const endpoint = route('upi.profile.bookmarks', {
    user: props.userId,
    bookmark: props.bookmark,
  });

  axios
    .get(endpoint, {
      params: {
        page: page.value,
        sorting: sorting.value,
      },
    })
    .then(({ data }) => {
      if (page.value === 1) {
        items.value = [];
      }
      items.value.push(...data.items);
      total.value = data.total;
      hasMore.value = data.has_more;
      page.value += 1;
    })
    .catch(({ response }) => {
      $toast.error(handleResponseError(response));
    })
    .finally(() => {
      loading.value = false;
      finish();
    });
};
</script>

<template>
  <div>
    <div v-if="!isEmpty" class="flex justify-between items-center mt-2">
      <div>
        <span class="mr-2">Всего тайтлов в списке:</span>
        <span class="font-semibold">{{ total }}</span>
      </div>

      <div>
        <v-select
          :items="[
            { value: 'latest', title: 'Сначала последние' },
            { value: 'oldest', title: 'Сначала старые' },
          ]"
          v-model="sorting"
          variant="solo"
          color="primary"
          density="compact"
          rounded="lg"
          hide-details
        >
          <template #prepend>
            <v-progress-circular
              v-if="loading"
              color="primary"
              indeterminate
              :size="20"
              :width="2"
            />
            <v-icon v-else icon="mdi-sort" />
          </template>
        </v-select>
      </div>
    </div>

    <InfiniteScroll
      ref="infiniteScroll"
      :items="items"
      :has-more="hasMore"
      :on-load="onLoad"
      class="mt-4"
    >
      <div class="grid grid-cols-6 gap-4 mb-4">
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
    </InfiniteScroll>
  </div>
</template>
