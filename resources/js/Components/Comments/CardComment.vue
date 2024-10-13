<script setup>
import { computed, nextTick, ref } from 'vue';
import CardComment from '@/Components/Comments/CardComment.vue';
import DateManager from '@/Plugins/DateManager';
import { useCommentStore } from '@/Stores/CommentStore';
import { useUserStore } from '@/Stores/UserStore';
import { initials, formatBbcodeToHtml, handleResponseError } from '@/Utils';
import MenuCommentActions from '@/Components/Comments/MenuCommentActions.vue';
import { storeToRefs } from 'pinia';
import CardCommentEdit from '@/Components/Comments/CardCommentEdit.vue';
import CardCommentReply from '@/Components/Comments/CardCommentReply.vue';
import CommentRepliesMore from '@/Components/Comments/CommentRepliesMore.vue';
import { useToast } from 'vue-toast-notification';
import DialogLoginRequires from '@/Components/Dialogs/DialogLoginRequires.vue';

const props = defineProps({
  comment: Object,
});

const commentStore = useCommentStore();
const userStore = useUserStore();
const { replyTo, edit: editComment } = storeToRefs(commentStore);
const { isLogged } = storeToRefs(userStore);
const $toast = useToast();

const loginRequires = ref(null);
const loadingReaction = ref(
  window.config.comments.reactions.reduce((obj, { name }) => {
    obj[name] = false;
    return obj;
  }, {})
);

const isReply = computed(() => props.comment.parent_id != null);
const isReplyTo = computed(() => replyTo.value?.real_id == props.comment.id);
const hasReplies = computed(() => props.comment.replies.length > 0);
//
const replies = computed(() => props.comment.replies.filter((item) => !item.is_new));
const newReplies = computed(() => props.comment.replies.filter((item) => item.is_new));

const toggleReaction = (reactionName) => {
  if (!isLogged.value) {
    loginRequires.value.open();
    return;
  }

  const reaction = window.config.comments.reactions.find(
    (reaction) => reaction.name === reactionName
  );
  let loadingTimeout;

  loadingTimeout = setTimeout(() => {
    loadingReaction.value[reactionName] = true;
  }, 200);

  commentStore.$toggleReaction(props.comment.id, reaction.id, {
    success: () => {
      console.log('success');
    },
    error: (error) => {
      $toast.error(handleResponseError(error));
    },
    finish: () => {
      console.log('finish');
      clearTimeout(loadingTimeout);
      loadingReaction.value[reactionName] = false;
    },
  });
};

const isReactionActive = (reactionName) => {
  const reaction = window.config.comments.reactions.find(
    (reaction) => reaction.name === reactionName
  );
  return props.comment.userReactions.includes(reaction.id);
};

const onReply = () => {
  commentStore.$setReplyTo(props.comment);
};

const onReport = (reasonId) => {
  commentStore.$report(props.comment.id, reasonId, {
    success: () => {
      $toast.success('Reported successfully.');
    },
    error: (error) => {
      $toast.error(handleResponseError(error));
    },
    finish: () => {
      console.log('finish');
    },
  });
};

const onEdit = () => {
  commentStore.$setEdit(props.comment);
};

const onDelete = () => {
  commentStore.$deleteComment(props.comment.id, {
    success: () => {
      $toast.success('Deleted successfully.');
    },
    error: (error) => {
      $toast.error(handleResponseError(error));
    },
    finish: () => {
      console.log('finish');
    },
  });
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
    }"
  >
    <div v-if="hasReplies || isReplyTo" class="has-replies"></div>

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
          <span v-if="comment.created_at != comment.updated_at" class="text-sm">
            (edited)
          </span>
        </div>
      </div>

      <div class="relative">
        <div class="mt-2" @click="onSpoilerClick">
          <p class="comment-body" v-html="formatBbcodeToHtml(comment.body)"></p>
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

          <MenuCommentActions
            v-if="isLogged"
            :comment="comment"
            @reply="onReply"
            @report="onReport"
            @edit="onEdit"
            @delete="onDelete"
          />
        </div>

        <div v-if="hasReplies || isReplyTo" class="mt-8">
          <div class="space-y-8">
            <template v-for="(comment, index) in replies" :key="index">
              <CardCommentEdit
                v-if="editComment && comment.id == editComment.id"
                :comment="comment"
              />
              <CardComment v-else :comment="comment" />
            </template>

            <CommentRepliesMore :comment="comment" />

            <template v-for="(comment, index) in newReplies" :key="index">
              <CardCommentEdit
                v-if="editComment && comment.id == editComment.id"
                :comment="comment"
              />
              <CardComment v-else :comment="comment" />
            </template>

            <CardCommentReply v-if="isReplyTo" />
          </div>
        </div>
      </div>
    </div>

    <DialogLoginRequires ref="loginRequires" />
  </div>
</template>
