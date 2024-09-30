<script setup>
import Navbar from '@/Components/Layout/Navbar.vue';
import Sidebar from '@/Components/Layout/Sidebar.vue';
</script>

<template>
  <v-app id="inspire">
    <Navbar />

    <v-main class="bg-zinc-200 dark:bg-zinc-900 mt-2">
      <v-container>
        <v-row>
          <v-col cols="2">
            <div class="sticky top-[88px]">
              <Sidebar />
            </div>
          </v-col>

          <v-col>
            <v-sheet min-height="70vh" rounded="lg">
              <slot />
            </v-sheet>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>
