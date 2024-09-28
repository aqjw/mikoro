<script setup>
import Navbar from '@/Components/Layout/Navbar.vue';
</script>

<template>
  <v-app id="inspire">
    <Navbar />

    <v-main class="bg-zinc-200 dark:bg-zinc-900">
      <v-container>
        <v-sheet rounded="lg" class=" overflow-hidden">
          <slot />
        </v-sheet>
      </v-container>
    </v-main>
  </v-app>
</template>
