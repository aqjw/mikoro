<script setup>
import { computed, nextTick, ref, onMounted } from 'vue';
import CardComment from '@/Components/Comments/CardComment.vue';
import DateManager from '@/Plugins/DateManager';
import { useCommentStore } from '@/Stores/CommentStore';
import { initials, formatBbcodeToHtml, handleResponseError } from '@/Utils';
import MenuCommentActions from '@/Components/Comments/MenuCommentActions.vue';
import { storeToRefs } from 'pinia';
import TextEditor from '@/Components/TextEditor.vue';
import CharacterLimit from '@/Components/CharacterLimit.vue';
import { useToast } from 'vue-toast-notification';

const props = defineProps({
  comment: Object,
});

const $toast = useToast();

const commentStore = useCommentStore();
const { edit: editComment, editTextLength } = storeToRefs(commentStore);

const isReply = computed(() => props.comment.parent_id != null);
const hasReplies = computed(() => props.comment.replies.length > 0);

// for new comment
const textEditor = ref(false);
const saving = ref(false);

onMounted(() => {
  nextTick(() => {
    textEditor.value?.focus();
  });
});

const onSave = () => {
  saving.value = true;
  textEditor.value.setEditable(false);
  commentStore.$updateComment({
    success: () => {
      //
    },
    error: (error) => {
      textEditor.value.setEditable(true);
      $toast.error(handleResponseError(error));
    },
    finish: () => {
      saving.value = false;
    },
  });
};

const onEditCancel = () => {
  commentStore.$setEdit(null);
};
</script>

<template>
  <div
    :class="{
      'comment-card': true,
      'is-reply': isReply,
    }"
  >
    <div v-if="hasReplies" class="has-replies"></div>

    <div>
      <v-avatar color="brown" size="small">
        <span class="text-xs">{{ initials(comment.author.name) }}</span>
      </v-avatar>
    </div>

    <div class="mt-1 ml-2 flex-grow">
      <div class="flex gap-2 items-center">
        <div class="font-semibold">{{ comment.author.name }}</div>
        <div class="text-gray-500 text-sm italic">
          <span>{{ DateManager.toHuman(comment.created_at, { parts: 1 }) }} ago</span>
          <span class="text-sm"> (edited)</span>
        </div>
      </div>

      <div class="mt-2">
        <TextEditor ref="textEditor" v-model="editComment.draft">
          <template #actions>
            <div class="flex gap-4">
            <CharacterLimit
              :value="editTextLength"
              :min="$config.comments.min_characters"
              :max="$config.comments.max_characters"
            />

            <div class="flex gap-2">
              <v-btn
                density="comfortable"
                variant="tonal"
                rounded="xl"
                class="text-none"
                @click="onEditCancel"
              >
                Cancel
              </v-btn>

              <v-btn
              :disabled="
                editTextLength < $config.comments.min_characters ||
                editTextLength > $config.comments.max_characters
              "
                :loading="saving"
                density="comfortable"
                color="primary"
                variant="tonal"
                rounded="xl"
                class="text-none"
                @click="onSave"
              >
                Save
              </v-btn>
            </div>
            </div>
          </template>
        </TextEditor>

        <div v-if="hasReplies" class="mt-8">
          <div class="space-y-8">
            <CardComment
              v-for="(item, index) in comment.replies"
              :key="index"
              :comment="item"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
