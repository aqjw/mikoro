<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import CardComment from '@/Components/Card/CardComment.vue';
import TextareaEditor from '@/Components/TextareaEditor.vue';
import { useCommentStore } from '@/Stores/CommentStore';
import { storeToRefs } from 'pinia';

const props = defineProps({
  title: {
    type: Object,
    required: true,
  },
});

const commentStore = useCommentStore();
const { items, params} = storeToRefs(commentStore);

commentStore.$setTitleId(props.title.id);

const infiniteScroll = ref(null);
const timer = ref(null);
const doneCallback = ref(null);

const onLoadItems = ({ done }) => {
  doneCallback.value = done;
  commentStore.$loadComments((hasMore) => {
    done(hasMore ? 'ok' : 'empty');
  });
};

const onSubmit = ({ body, success, error, finish }) => {
  commentStore.$storeComment(body, { success, error, finish });
};

onBeforeUnmount(() => {
  commentStore.$reset();
});
</script>

<template>
  <div class="comments-section">
    <div class="textarea-editor-container">
      <TextareaEditor @on-submit="onSubmit" />
    </div>

    <div class="p-6">
      <div class="flex justify-between items-center mb-4">
        <div class="text-xl font-medium mb-2">Комментарии:</div>
        <div class="w-64">
          <v-select
            :items="[1, 2, 3, 4]"
            variant="solo"
            :elevation="0"
            color="primary"
            density="compact"
            rounded="lg"
            hide-details
          />
        </div>
      </div>

      <div>
        <v-infinite-scroll
          ref="infiniteScroll"
          :items="items"
          :onLoad="onLoadItems"
          :mode="items.length > 0 ? 'manual' : 'intersect'"
        >
          <div class="flex flex-col gap-8">
            <CardComment
              v-for="(item, index) in items"
              :key="index"
              :comment="item"
            />
          </div>

          <template v-slot:load-more="{ props }">
            <v-btn
              v-bind="props"
              variant="flat"
              class="text-none"
              color="primary"
              density="comfortable"
            >
              Load More
            </v-btn>
          </template>

          <template v-slot:loading>
            <v-progress-circular indeterminate :size="50" :width="2" color="primary" />
          </template>

          <template v-slot:empty>
            <div v-if="items.length === 0">Ничего не найдено</div>
          </template>
        </v-infinite-scroll>

        <!-- <CardComment has-replies />-->
      </div>
    </div>
  </div>
</template>
