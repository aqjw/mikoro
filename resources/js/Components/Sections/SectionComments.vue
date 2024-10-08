<script setup>
import CardComment from '@/Components/Card/CardComment.vue';
defineProps({
  title: Object,
});
</script>

<template>
  <div>
    <div>
      <textarea
        placeholder="Add comment.."
        variant="text"
        class="bg-gray-100 rounded-lg resize-none w-full max-h-96 min-h-24"
        hide-details
        v-autogrow
      />
    </div>

    <div class="flex justify-between items-center mb-8">
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

    <div class="flex flex-col gap-8">
      <!-- https://www.vuescript.com/best-wysiwyg-rich-text-editor/ -->
      <CardComment />
      <CardComment has-replies />
      <CardComment />
      <CardComment />
      <CardComment has-replies />
    </div>
  </div>
</template>
