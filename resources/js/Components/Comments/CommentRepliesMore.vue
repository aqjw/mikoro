<script setup>
import { computed, toRefs, ref } from 'vue';
import { storeToRefs, useAppStore, useCommentStore } from '@/Stores';
import { useToast } from 'vue-toast-notification';
import { handleResponseError } from '@/Utils';
const props = defineProps({
  comment: Object,
});

const { comment } = toRefs(props);

const $toast = useToast();
const appStore = useAppStore();
const commentStore = useCommentStore();
const { getConfig } = storeToRefs(appStore);

const repliesLimit = getConfig.value.comments.repliesPerPage;
const loading = ref(false);

const repliesTotal = computed(() => comment.value.replies_total);
const repliesCount = computed(
  () => comment.value.replies.filter((item) => !item.is_new).length
);

const hasMoreReplies = computed(() => {
  return repliesTotal.value > repliesLimit && repliesCount.value < repliesTotal.value;
});

const onLoadMore = () => {
  loading.value = true;
  commentStore.$loadReplies(comment.value.id, {
    success: () => {
      //
    },
    error: (error) => {
      $toast.error(handleResponseError(error));
    },
    finish: () => {
      loading.value = false;
    },
  });
};
</script>

<template>
  <div v-if="hasMoreReplies" class="comment-card is-reply">
    <div>
      <v-btn
        variant="tonal"
        class="text-none"
        color="primary"
        density="comfortable"
        rounded="xl"
        :loading="loading"
        @click="onLoadMore"
      >
        <span class="text-sm mr-2">+{{ repliesTotal - repliesCount }}</span>
        <span>More</span>
      </v-btn>
    </div>
  </div>
</template>
