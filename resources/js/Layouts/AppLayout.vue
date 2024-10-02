<script setup>
import Navbar from '@/Components/Layout/Navbar.vue';
import Sidebar from '@/Components/Layout/Sidebar.vue';

defineProps({
  bgTransparent: {
    type: Boolean,
    default: false,
  },
});
</script>

<template>
  <v-app id="inspire" class="bg-transparent">
    <Navbar />

    <v-main>
      <v-container>
        <v-row>
          <v-col cols="2">
            <div class="sticky top-[64px]">
              <Sidebar />
            </div>
          </v-col>

          <v-col>
            <v-sheet min-height="70vh" rounded="lg" class="bg-transparent">
              <slot />
            </v-sheet>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>
