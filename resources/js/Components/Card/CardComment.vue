<script setup>
import CardComment from '@/Components/Card/CardComment.vue';
defineProps({
  hasReplies: Boolean,
  isReply: Boolean,
});
</script>

<template>
  <div>
    <div class="flex gap-2 items-center relative">
    <div v-if="isReply" class="absolute -left-8 w-8 h-[1px] bg-black"></div>

      <v-avatar color="brown" size="small">
        <span class="text-xs">SA</span>
      </v-avatar>

      <div class="font-semibold ml-2">Lyle Kauffman</div>
      <div class="text-gray-500">1 day ago</div>
    </div>

    <div class="pl-12 relative">
      <div
        v-if="hasReplies"
        :class="['absolute left-4 -top-2 bottom-0 ', 'bg-black w-[1px]']"
      ></div>

      <div class="mt-2">
        How do we measure the amount of water vapor in the air? Is it something we'll
        cover later?
      </div>

      <div class="flex gap-4 mt-3">
        <div
          class="flex gap-1 items-center text-slate-500 hover:text-slate-700 cursor-pointer"
        >
          <v-icon icon="mdi-thumb-up" size="small" />
          <span class="text-sm font-semibold">12</span>
        </div>

        <div
          class="flex gap-1 items-center text-slate-500 hover:text-slate-700 cursor-pointer"
        >
          <v-icon icon="mdi-thumb-down" size="small" />
          <span class="text-sm font-semibold">4</span>
        </div>

        <div
          :class="[
            'ml-4 flex gap-1 items-center',
            'text-slate-500 hover:text-slate-700 cursor-pointer',
          ]"
        >
          <v-icon icon="mdi-message-reply-text" size="small" />
          <span class="text-sm font-semibold">Reply</span>
        </div>
      </div>

      <div v-if="hasReplies" class="mt-8">
        <div class="flex flex-col gap-8">
          <CardComment is-reply />
          <CardComment is-reply />
          <CardComment is-reply />
        </div>
      </div>
    </div>
  </div>
</template>
