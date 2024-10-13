<script setup>
import { onBeforeUnmount, watch } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import Placeholder from '@tiptap/extension-placeholder';
import Document from '@tiptap/extension-document';
import Paragraph from '@tiptap/extension-paragraph';
import Text from '@tiptap/extension-text';
import Bold from '@tiptap/extension-bold';
import Italic from '@tiptap/extension-italic';
import Underline from '@tiptap/extension-underline';
import Strike from '@tiptap/extension-strike';
import Spoiler from '@/Plugins/tiptap/extension-spoiler';
import Blockquote from '@tiptap/extension-blockquote';

const props = defineProps({
  modelValue: {
    type: Object,
    default: {
      text: '',
      html: '',
    },
  },
});

const emit = defineEmits(['update:modelValue']);

const editor = useEditor({
  content: props.modelValue.html,
  extensions: [
    Document,
    Paragraph,
    Text,
    Bold,
    Italic,
    Underline,
    Strike,
    Spoiler,
    Blockquote,
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
  onUpdate({ editor }) {
    emit('update:modelValue', {
      text: editor.getText(),
      html: editor.getHTML(),
    });
  },
});

watch(
  () => props.modelValue.html,
  (newValue) => {
    if (editor.value && newValue !== editor.value.getHTML()) {
      editor.value.commands.setContent(newValue);
    }
  }
);

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
  'divide',
  {
    name: 'blockquote',
    icon: 'mdi-format-quote-close-outline',
    action: () => editor.value.chain().focus().toggleBlockquote().run(),
  },
  {
    name: 'spoiler',
    icon: 'mdi-creation-outline',
    action: () => editor.value.chain().focus().toggleSpoiler().run(),
  },
];

const setEditable = (state) => {
  editor.value.setEditable(state);
};

const focus = () => {
  editor.value.chain().focus();
};

defineExpose({
  setEditable,
  focus,
});

onBeforeUnmount(() => {
  editor.value?.destroy();
});
</script>

<template>
  <div
    :class="{ 'text-editor': true, 'is-focused': editor?.isFocused }"
    @click="() => editor.chain().focus()"
  >
    <editor-content :editor="editor" />

    <div v-if="editor" class="actions-section">
      <div class="flex items-center">
        <div class="flex gap-3">
          <template v-for="button in editorButtons" :key="button.name">
            <div v-if="button === 'divide'" class="divider-vertical"></div>
            <v-btn
              v-else
              density="comfortable"
              rounded="sm"
              :variant="editor.isActive(button.name) ? 'tonal' : 'text'"
              color="grey-darken-1"
              :icon="button.icon"
              @click="button.action"
            >
              <v-icon :icon="button.icon" />
              <v-tooltip activator="parent" location="bottom" :open-delay="300">
                <span class="text-sm">{{ button.name }}</span>
              </v-tooltip>
            </v-btn>
          </template>
        </div>
      </div>

      <div>
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>
