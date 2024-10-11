<script setup>
import { onBeforeUnmount, onBeforeMount, ref, computed, watch } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import Placeholder from '@tiptap/extension-placeholder';
// import StarterKit from '@tiptap/starter-kit';

import Document from '@tiptap/extension-document';
import Paragraph from '@tiptap/extension-paragraph';
import Text from '@tiptap/extension-text';

import Bold from '@tiptap/extension-bold';
import Italic from '@tiptap/extension-italic';
import Underline from '@tiptap/extension-underline';
import Strike from '@tiptap/extension-strike';
import Spoiler from '@/Plugins/tiptap/extension-spoiler';
import { formatHtmlToBbcode } from '@/Utils';

import { useCommentStore } from '@/Stores/CommentStore';
import { storeToRefs } from 'pinia';

defineProps({
  //
});

const commentStore = useCommentStore();
const { replyTo } = storeToRefs(commentStore);

const emit = defineEmits(['on-submit']);

const loading = ref(false);
const content = ref(null);

const editor = useEditor({
  content: '',
  extensions: [
    Document,
    Paragraph,
    Text,
    //
    Bold,
    Italic,
    Underline,
    Strike,
    Spoiler,
    Placeholder.configure({
      placeholder: 'Add comment …',
    }),
  ],
  editorProps: {
    rows: 10,
    attributes: {
      class: 'prose prose-sm sm:prose-base lg:prose-lg xl:prose-2xl focus:outline-none',
    },
  },
});

const editorButtons = [
  {
    name: 'bold',
    icon: 'mdi-format-bold',
    action: () => editor.value.chain().focus().toggleBold().run(),
  },
  {
    name: 'italic',
    icon: 'mdi-format-italic',
    action: () => editor.value.chain().focus().toggleItalic().run(),
  },
  {
    name: 'underline',
    icon: 'mdi-format-underline',
    action: () => editor.value.chain().focus().toggleUnderline().run(),
  },
  {
    name: 'strike',
    icon: 'mdi-format-strikethrough-variant',
    action: () => editor.value.chain().focus().toggleStrike().run(),
  },
  {
    name: 'spoiler',
    icon: 'mdi-creation-outline',
    action: () => editor.value.chain().focus().toggleSpoiler().run(),
  },
];

watch(replyTo, (newVal) => {
  if (newVal) {
    editor.value.chain().focus();
  }
});

const contentLength = computed(() => {
  return editor.value?.getText()?.length;
});

onBeforeUnmount(() => {
  editor.value?.destroy();
});

const onReplyToCancel = () => {
  commentStore.$setReplyTo(null);
};

const onSubmit = () => {
  loading.value = true;
  editor.value.setEditable(false);
  emit('on-submit', {
    body: formatHtmlToBbcode(editor.value?.getHTML()),
    success() {
      editor.value.commands.clearContent();
    },
    error() {},
    finish() {
      loading.value = false;
      editor.value.setEditable(true);
    },
  });
};
</script>

<template>
  <div
    :class="{ 'textarea-editor': true, 'is-focused': editor?.isFocused }"
    @click="() => editor.chain().focus()"
  >
    <editor-content :editor="editor" v-model="content" />

    <div v-if="editor" class="actions-section">
      <div class="flex items-center">
        <div class="flex gap-3">
          <v-btn
            v-for="button in editorButtons"
            :key="button.name"
            density="comfortable"
            rounded="sm"
            :variant="editor.isActive(button.name) ? 'tonal' : 'text'"
            color="grey-darken-1"
            :icon="button.icon"
            @click="button.action"
          />
        </div>
        <div
          v-if="replyTo"
          class="pl-6 ml-6 border-l border-black/50 flex items-center gap-2"
        >
          <span>Reply to "{{ replyTo.author.name }}"</span>
          <v-btn
            size="small"
            variant="tonal"
            density="compact"
            icon="mdi-close"
            @click="onReplyToCancel"
          />
        </div>
      </div>

      <div>
        <v-btn
          :disabled="contentLength < 10"
          :loading="loading"
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
  </div>
</template>
