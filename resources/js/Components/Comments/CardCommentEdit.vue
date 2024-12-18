<script setup>
import { computed, nextTick, ref, onMounted } from 'vue';
import CardComment from '@/Components/Comments/CardComment.vue';
import DateManager from '@/Plugins/DateManager';
import { storeToRefs, useAppStore, useCommentStore } from '@/Stores';
import { initials, formatBbcodeToHtml, handleResponseError } from '@/Utils';
import TextEditor from '@/Components/TextEditor.vue';
import CharacterLimit from '@/Components/CharacterLimit.vue';
import { useToast } from 'vue-toast-notification';

const props = defineProps({
  comment: Object,
});

const $toast = useToast();

const appStore = useAppStore();
const commentStore = useCommentStore();
const { getConfig } = storeToRefs(appStore);
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
      <v-img
        class="h-8 w-8 bg-zinc-400 dark:bg-zinc-500 rounded-full mx-auto"
        :src="$media.image(comment.author.avatar)"
      >
        <span
          v-if="!comment.author.avatar"
          class="text-lg font-semibold text-white absolute-center"
        >
          {{ initials(comment.author.name) }}
        </span>
      </v-img>
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
                :min="getConfig.comments.minCharacters"
                :max="getConfig.comments.maxCharacters"
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
                    editTextLength < getConfig.comments.minCharacters ||
                    editTextLength > getConfig.comments.maxCharacters
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
