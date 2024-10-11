<script setup>
import { computed, ref } from 'vue';
import CardComment from '@/Components/Card/CardComment.vue';
import DateManager from '@/Plugins/DateManager';
import { useCommentStore } from '@/Stores/CommentStore';
import { initials, formatBbcodeToHtml } from '@/Utils';
import MenuCommentActions from '../Menu/MenuCommentActions.vue';
import { storeToRefs } from 'pinia';

const commentStore = useCommentStore();
const { replyTo } = storeToRefs(commentStore);

const props = defineProps({
  comment: Object,
});

const loadingReaction = ref(
  window.reactions.reduce((obj, { name }) => {
    obj[name] = false;
    return obj;
  }, {})
);

const isReply = computed(() => props.comment.parent_id != null);
const isReplyTo = computed(() => replyTo.value?.id == props.comment.id);
const hasReplies = computed(() => props.comment.replies.length > 0);

const toggleReaction = (reactionName) => {
  const reaction = window.reactions.find((reaction) => reaction.name === reactionName);
  let loadingTimeout;

  loadingTimeout = setTimeout(() => {
    loadingReaction.value[reactionName] = true;
  }, 200);

  commentStore.$toggleReaction(props.comment.id, reaction.id, {
    success() {
      console.log('success');
    },
    error() {
      console.log('error');
    },
    finish() {
      console.log('finish');
      clearTimeout(loadingTimeout);
      loadingReaction.value[reactionName] = false;
    },
  });
};

const isReactionActive = (reactionName) => {
  const reaction = window.reactions.find((reaction) => reaction.name === reactionName);
  return props.comment.userReactions.includes(reaction.id);
};

const onReply = () => {
  commentStore.$setReplyTo(props.comment);
};

const onDelete = () => {
  commentStore.$deleteComment(props.comment.id);
};

const onSpoilerClick = (event) => {
  const spoilerElement = event.target.closest('spoiler');
  if (spoilerElement) {
    spoilerElement.classList.toggle('un-spoiler');
  }
};
</script>

<template>
  <div
    :class="{
      'comment-card': true,
      'is-reply': isReply,
      'reply-to': isReplyTo,
    }"
  >
    <div v-if="hasReplies" class="has-replies"></div>

    <div>
      <v-avatar color="brown" size="small">
        <span class="text-xs">{{ initials(comment.author.name) }}</span>
      </v-avatar>
    </div>

    <div class="mt-1 ml-2">
      <div class="flex gap-2 items-center">
        <div class="font-semibold">{{ comment.author.name }}</div>
        <div class="text-gray-500 text-sm italic">
          {{ DateManager.toHuman(comment.created_at, { parts: 1 }) }} ago
        </div>
      </div>

      <div class="relative">
        <div class="mt-2 comment-body" @click="onSpoilerClick">
          <p v-html="formatBbcodeToHtml(comment.body)"></p>
        </div>

        <div class="flex items-center gap-2 mt-3">
          <div class="comment-reactions">
            <v-btn
              :rounded="false"
              size="small"
              prepend-icon="mdi-thumb-up"
              variant="tonal"
              :loading="loadingReaction.like"
              :color="isReactionActive('like') ? 'green' : 'grey'"
              :class="!comment.reactions.like ? 'no-reactions' : ''"
              @click="() => toggleReaction('like')"
            >
              {{ comment.reactions.like }}
            </v-btn>

            <v-btn
              :rounded="false"
              size="small"
              prepend-icon="mdi-thumb-down"
              variant="tonal"
              :loading="loadingReaction.dislike"
              :color="isReactionActive('dislike') ? 'red' : 'grey'"
              :class="!comment.reactions.dislike ? 'no-reactions' : ''"
              @click="() => toggleReaction('dislike')"
            >
              {{ comment.reactions.dislike }}
            </v-btn>
          </div>

          <MenuCommentActions @reply="onReply" @delete="onDelete" />
        </div>

        <div v-if="hasReplies" class="mt-8">
          <div class="flex flex-col gap-8">
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
