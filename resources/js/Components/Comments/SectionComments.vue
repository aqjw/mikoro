<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import CardComment from '@/Components/Comments/CardComment.vue';
import CardCommentEdit from '@/Components/Comments/CardCommentEdit.vue';
import TextEditor from '@/Components/TextEditor.vue';
import InfiniteScroll from '@/Components/InfiniteScroll.vue';
import DialogLoginRequires from '@/Components/Dialogs/DialogLoginRequires.vue';
import CharacterLimit from '@/Components/CharacterLimit.vue';
import { storeToRefs, useAppStore, useCommentStore, useUserStore } from '@/Stores';
import { formatHtmlToBbcode, handleResponseError } from '@/Utils';
import { useToast } from 'vue-toast-notification';

const props = defineProps({
  title: {
    type: Object,
    required: true,
  },
});

const $toast = useToast();

const appStore = useAppStore();
const userStore = useUserStore();
const commentStore = useCommentStore();
const { getConfig } = storeToRefs(appStore);
const { isLogged } = storeToRefs(userStore);
const {
  items,
  params,
  has_more,
  total,
  edit: editComment,
  draft,
  draftTextLength,
  sorting_options,
  sorting,
} = storeToRefs(commentStore);

commentStore.$setTitleId(props.title.id);

const loginRequires = ref(null);
//
const infiniteScroll = ref(null);
const loading = ref(false);
const textEditor = ref(false);
const submitting = ref(false);

watch(sorting, () => {
  setTimeout(() => {
    commentStore.$resetPage();
    infiniteScroll.value?.reload();
  }, 200);
});

onMounted(() => {
  nextTick(() => {
    infiniteScroll.value?.load();
  });
});

const onLoad = (finish) => {
  loading.value = true;
  commentStore.$loadComments({
    success: () => {},
    error: () => {},
    finish: () => {
      loading.value = false;
      finish();
    },
  });
};

const onSubmit = () => {
  if (!isLogged.value) {
    loginRequires.value.open();
    return;
  }

  submitting.value = true;
  textEditor.value.setEditable(false);
  commentStore.$storeComment(
    {
      body: formatHtmlToBbcode(draft.value.html),
      parent_id: null,
    },
    {
      success: () => {
        commentStore.$resetDraft();
      },
      error: (error) => {
        $toast.error(handleResponseError(error));
      },
      finish: () => {
        submitting.value = false;
        textEditor.value.setEditable(true);
      },
    }
  );
};

onBeforeUnmount(() => {
  commentStore.$resetAll();
});
</script>

<template>
  <div class="comments-section">
    <div class="p-4">
      <TextEditor ref="textEditor" v-model="draft">
        <template #actions>
          <div class="flex gap-4">
            <CharacterLimit
              :value="draftTextLength"
              :min="getConfig.comments.minCharacters"
              :max="getConfig.comments.maxCharacters"
            />

            <v-btn
              :disabled="
                draftTextLength < getConfig.comments.minCharacters ||
                draftTextLength > getConfig.comments.maxCharacters
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
        </template>
      </TextEditor>
    </div>

    <div class="p-6">
      <div class="flex justify-between items-center mb-4">
        <div class="text-xl font-medium">Комментарии:</div>
        <div>
          <v-select
            :items="sorting_options"
            v-model="sorting"
            variant="underlined"
            :elevation="0"
            color="primary"
            density="compact"
            rounded="lg"
            hide-details
          >
            <template #prepend>
              <v-progress-circular
                v-if="loading"
                color="primary"
                indeterminate
                :size="20"
                :width="2"
              />
              <v-icon v-else icon="mdi-sort" />
            </template>
          </v-select>
        </div>
      </div>

      <div>
        <InfiniteScroll
          ref="infiniteScroll"
          :items="items"
          :has-more="has_more"
          :on-load="onLoad"
        >
          <div class="space-y-8">
            <template v-for="(comment, index) in items" :key="index">
              <CardCommentEdit
                v-if="editComment && comment.id == editComment.id"
                :comment="comment"
              />
              <CardComment v-else :comment="comment" />
            </template>
          </div>
        </InfiniteScroll>
      </div>
    </div>

    <DialogLoginRequires ref="loginRequires" />
  </div>
</template>
