<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout>
    <div class="p-4">
      <div class="grid grid-cols-4 gap-4">
        <div class="bg-zinc-200 dark:bg-zinc-900 rounded-md h-80" v-for="i in 50">{{ i }}</div>
      </div>
    </div>
  </AppLayout>
</template>
