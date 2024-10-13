<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';
import CardComment from '@/Components/Comments/CardComment.vue';
import DateManager from '@/Plugins/DateManager';
import { useCommentStore } from '@/Stores/CommentStore';
import { useUserStore } from '@/Stores/UserStore';
import { storeToRefs } from 'pinia';
import {
  initials,
  scrollToElement,
  formatHtmlToBbcode,
  handleResponseError,
} from '@/Utils';
import MenuCommentActions from '@/Components/Comments/MenuCommentActions.vue';
import TextEditor from '@/Components/TextEditor.vue';
import CardCommentReply from '@/Components/Comments/CardCommentReply.vue';
import { useToast } from 'vue-toast-notification';
import CharacterLimit from '@/Components/CharacterLimit.vue';

const $toast = useToast();

const commentStore = useCommentStore();
const userStore = useUserStore();
const { items, params, replyTo, draft, replyToTextLength } = storeToRefs(commentStore);
const { user } = storeToRefs(userStore);

// for new comment
const textEditor = ref(false);
const submitting = ref(false);

onMounted(() => {
  nextTick(() => {
    const element = document.getElementById(`reply-text-editor-${replyTo.value.id}`);
    scrollToElement(element, 200);

    setTimeout(() => textEditor.value?.focus(), 200);
  });
});

const onSubmit = () => {
  submitting.value = true;
  textEditor.value.setEditable(false);

  const body = formatHtmlToBbcode(replyTo.value.draft.html);
  const parent_id = replyTo.value.real_id;
  commentStore.$storeComment(
    { body, parent_id },
    {
      success: () => {
        onReplyToCancel();
      },
      error: (error) => {
        textEditor.value.setEditable(true);
        $toast.error(handleResponseError(error));
      },
      finish: () => {
        submitting.value = false;
      },
    }
  );
};

const onReplyToCancel = () => {
  commentStore.$setReplyTo(null);
};
</script>

<template>
  <div class="comment-card is-reply">
    <div>
      <v-avatar color="brown" size="small">
        <span class="text-xs">{{ initials(user.name) }}</span>
      </v-avatar>
    </div>

    <div class="mt-1 ml-2 flex-grow">
      <div class="flex gap-2 items-center">
        <div class="font-semibold">{{ user.name }}</div>
        <div class="text-gray-500 text-sm italic">now</div>
      </div>

      <div class="mt-2">
        <TextEditor
          :id="`reply-text-editor-${replyTo.id}`"
          ref="textEditor"
          v-model="replyTo.draft"
        >
          <template #actions>
            <div class="flex gap-4">
              <CharacterLimit
                :value="replyToTextLength"
                :min="$config.comments.min_characters"
                :max="$config.comments.max_characters"
              />

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
                  :disabled="
                    replyToTextLength < $config.comments.min_characters ||
                    replyToTextLength > $config.comments.max_characters
                  "
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
              </div>
            </div>
          </template>
        </TextEditor>
      </div>
    </div>
  </div>
</template>
