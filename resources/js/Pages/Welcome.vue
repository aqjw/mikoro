<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout>
    <div class="p-4">
      <div class="grid grid-cols-4 gap-4">
        <div class="bg-zinc-600 rounded-md h-80 flex justify-center items-center text-4xl font-bold text-gray-100" v-for="i in 50">{{ i }}</div>
      </div>
    </div>
  </AppLayout>
</template>
