<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';
import CardComment from '@/Components/Card/CardComment.vue';
import DateManager from '@/Plugins/DateManager';
import { useCommentStore } from '@/Stores/CommentStore';
import { initials, formatBbcodeToHtml } from '@/Utils';
import MenuCommentActions from '../Menu/MenuCommentActions.vue';
import { storeToRefs } from 'pinia';
import TextEditor from '@/Components/TextEditor.vue';
import CardCommentReply from './CardCommentReply.vue';

const commentStore = useCommentStore();
const { items, params, replyTo, draft, draftTextLength } = storeToRefs(commentStore);

const props = defineProps({
  //
});

// for new comment
const textEditor = ref(false);
const submitting = ref(false);

onMounted(() => {
  nextTick(() => {
    textEditor.value?.focus();
  });
});

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

const onReplyToCancel = () => {
  commentStore.$setReplyTo(null);
};
</script>

<template>
  <div class="comment-card is-reply">
    <div>
      <v-avatar color="brown" size="small">
        <span class="text-xs">{{ initials('comment.author.name') }}</span>
      </v-avatar>
    </div>

    <div class="mt-1 ml-2 flex-grow">
      <div class="flex gap-2 items-center">
        <div class="font-semibold">{{ 'comment.author.name' }}</div>
        <div class="text-gray-500 text-sm italic">now</div>
      </div>

      <div class="relative">
        <div class="mt-2 text-editor-container !p-0">
          <TextEditor ref="textEditor">
            <template #actions="">
              <div class="flex gap-2">
                <v-btn
                  density="comfortable"
                  variant="tonal"
                  rounded="xl"
                  class="text-none"
                  @click="onReplyToCancel"
                >
                  Cancel
                </v-btn>
                <v-btn
                  :disabled="editTextLength < 10"
                  :loading="saving"
                  density="comfortable"
                  color="primary"
                  variant="tonal"
                  rounded="xl"
                  class="text-none"
                  @click="onSubmit"
                >
                  Submit
                </v-btn>
              </div>
            </template>
          </TextEditor>
        </div>
      </div>
    </div>
  </div>
</template>
