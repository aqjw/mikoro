<script setup>
import CardComment from '@/Components/Card/CardComment.vue';
defineProps({
  hasReplies: Boolean,
  isReply: Boolean,
});
</script>

<template>
  <div class="comment-card" :class="isReply ? 'is-reply' : ''">
    <div v-if="hasReplies" class="has-replies"></div>

    <div>
      <v-avatar color="brown" size="small">
        <span class="text-xs">SA</span>
      </v-avatar>
    </div>

    <div class="mt-1 ml-2">
      <div class="flex gap-2 items-center">
        <div class="font-semibold">Lyle Kauffman</div>
        <div class="text-gray-500 text-sm italic">1 day ago</div>
      </div>

      <div class="relative">
        <div class="mt-2">
          How do we measure the amount of water vapor in the air? Is it something we'll
          cover later?
        </div>

        <div class="comment-actions">
          <div class="comment-action">
            <v-icon icon="mdi-thumb-up" size="small" />
            <span class="text-sm font-semibold">12</span>
          </div>

          <div class="comment-action">
            <v-icon icon="mdi-thumb-down" size="small" />
            <span class="text-sm font-semibold">4</span>
          </div>

          <div class="ml-4 comment-action">
            <v-icon icon="mdi-message-reply-text" size="small" />
            <span class="text-sm font-semibold">Reply</span>
          </div>
        </div>

        <div v-if="hasReplies" class="mt-8">
          <div class="flex flex-col gap-8">
            <CardComment is-reply :has-replies="!isReply" />
            <CardComment is-reply />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
