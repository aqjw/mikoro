<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import CardComment from '@/Components/Card/CardComment.vue';
import CardCommentEdit from '@/Components/Card/CardCommentEdit.vue';
import TextEditor from '@/Components/TextEditor.vue';
import { useCommentStore } from '@/Stores/CommentStore';
import { storeToRefs } from 'pinia';

const props = defineProps({
  title: {
    type: Object,
    required: true,
  },
});

const commentStore = useCommentStore();
const { items, params, replyTo, edit: editComment, draft, draftTextLength } = storeToRefs(
  commentStore
);

commentStore.$setTitleId(props.title.id);

// for list
const infiniteScroll = ref(null);
const doneCallback = ref(null);

// for new comment
const textEditor = ref(false);
const submitting = ref(false);

const onLoadItems = ({ done }) => {
  doneCallback.value = done;
  commentStore.$loadComments((hasMore) => {
    done(hasMore ? 'ok' : 'empty');
  });
};

const onSubmit = () => {
  submitting.value = true;
  textEditor.value.setEditable(false);
  commentStore.$storeComment({
    success: () => {
      //
    },
    error: (error) => {
      console.error(error);
    },
    finish: () => {
      submitting.value = false;
      textEditor.value.setEditable(true);
    },
  });
};

onBeforeUnmount(() => {
  commentStore.$reset();
});
</script>

<template>
  <div class="comments-section">
    <div class="text-editor-container">
      <TextEditor ref="textEditor" v-model="draft">
        <template #actions="">
          <v-btn
            :disabled="draftTextLength < 10"
            :loading="submitting"
            density="comfortable"
            color="primary"
            variant="tonal"
            rounded="xl"
            class="text-none"
            @click="onSubmit"
          >
            Submit
          </v-btn>
        </template>
      </TextEditor>
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
          <div class="space-y-8">
            <template v-for="(comment, index) in items" :key="index">
              <CardCommentEdit
                v-if="editComment && comment.id == editComment.id"
                :comment="comment"
              />
              <CardComment v-else :comment="comment" />
            </template>
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
