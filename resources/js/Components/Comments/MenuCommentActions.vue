<script setup>
import { ref } from 'vue';
import DialogConfirm from '@/Components/Dialogs/DialogConfirm.vue';
import { useUserStore } from '@/Stores/UserStore';
import { storeToRefs } from 'pinia';

const userStore = useUserStore();
const { isLogged, user } = storeToRefs(userStore);

const emit = defineEmits(['reply', 'report', 'edit', 'delete']);

const props = defineProps({
  comment: Object,
});

const deleteConfirm = ref(null);
const items = ref([
  {
    label: 'Reply',
    icon: 'mdi-message-reply-text',
    color: '',
    can: () => true,
    action: () => emit('reply'),
  },
  {
    label: 'Report',
    icon: 'mdi-shield-alert-outline',
    color: '',
    subitems: window.config.comments.report_reasons,
    can: () => true,
    action: (reasonId) => emit('report', reasonId),
  },
  {
    label: 'Edit',
    icon: 'mdi-pencil-outline',
    color: '',
    can: () => props.comment.author.id == user.value.id,
    action: () => emit('edit'),
  },
  {
    label: 'Delete',
    icon: 'mdi-delete-forever-outline',
    color: 'text-red-600 dark:text-red-400',
    can: () => props.comment.author.id == user.value.id,
    action: () => {
      deleteConfirm.value.open(getDeleteConfirmOptions(), (confirm) => {
        if (confirm) {
          emit('delete');
        }
      });
    },
  },
]);

const getDeleteConfirmOptions = () => ({
  title: 'Delete comment',
  text: 'Your comment will be deleted permanently. Are you sure?',
  icon: 'mdi-delete-forever-outline',
  buttons: {
    confirm: {
      color: 'red',
      variant: 'tonal',
    },
  },
});
</script>

<template>
  <div>
    <v-menu rounded>
      <template v-slot:activator="{ props }">
        <v-btn
          icon="mdi-dots-vertical"
          size="small"
          variant="text"
          density="comfortable"
          v-bind="props"
        />
      </template>

      <v-list density="compact" color="primary">
        <template v-for="(item, index) in items" :key="index">
          <v-list-item
            v-if="item.can() && !item.subitems"
            color="primary"
            @click="item.action"
          >
            <div :class="['flex items-center gap-4 pr-2', item.color]">
              <v-icon size="small" :icon="item.icon" variant="tonal" />
              <span class="text-sm">{{ item.label }}</span>
            </div>
          </v-list-item>

          <v-list-group v-else-if="item.can() && item.subitems">
            <template v-slot:activator="{ props }">
              <v-list-item v-bind="props" color="primary">
                <div :class="['flex items-center gap-4 pr-2', item.color]">
                  <v-icon size="small" :icon="item.icon" variant="tonal" />
                  <span class="text-sm">{{ item.label }}</span>
                </div>
              </v-list-item>
            </template>

            <v-list-item
              v-for="(subitem, _index) in item.subitems"
              :key="_index"
              color="primary"
              @click="item.action(subitem.id)"
            >
              <div class="flex items-center gap-4 pr-2 text-gray-500 dark:text-gray-400">
                <v-icon size="small" :icon="subitem.icon" variant="tonal" />
                <span class="text-sm">{{ subitem.name }}</span>
              </div>
            </v-list-item>
          </v-list-group>
        </template>
      </v-list>
    </v-menu>

    <DialogConfirm ref="deleteConfirm" />
  </div>
</template>
