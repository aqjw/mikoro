<script setup>
import { ref } from 'vue';
import DialogConfirm from '../Dialogs/DialogConfirm.vue';

const emit = defineEmits(['reply', 'report', 'edit', 'delete']);

const deleteConfirm = ref(null);
const items = [
  {
    label: 'Reply',
    icon: 'mdi-message-reply-text',
    color: '',
    action: () => emit('reply'),
  },
  {
    label: 'Report',
    icon: 'mdi-shield-alert-outline',
    color: '',
    action: () => emit('report'),
  },
  {
    label: 'Edit',
    icon: 'mdi-pencil-outline',
    color: '',
    action: () => emit('edit'),
  },
  {
    label: 'Delete',
    icon: 'mdi-delete-forever-outline',
    color: 'text-red-600',
    action: () => {
      deleteConfirm.value.open(getDeleteConfirmOptions(), (confirm) => {
        if (confirm) {
          emit('delete');
        }
      });
    },
  },
];

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
        <v-list-item
          v-for="(item, index) in items"
          :key="index"
          color="primary"
          @click="item.action"
        >
          <div :class="['flex items-center gap-4 pr-2', item.color]">
            <v-icon size="small" :icon="item.icon" variant="tonal" />
            <span class="text-sm">{{ item.label }}</span>
          </div>
        </v-list-item>
      </v-list>
    </v-menu>

    <DialogConfirm ref="deleteConfirm" />
  </div>
</template>
