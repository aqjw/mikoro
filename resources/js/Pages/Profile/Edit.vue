<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import FullLayout from '@/Layouts/FullLayout.vue';
import useFormError from '@/Composables/useFormError.js';
import PartUpdatePassword from './Parts/Edit/PartUpdatePassword.vue';
import PartUpdateInformation from './Parts/Edit/PartUpdateInformation.vue';
import PartDestroyAccount from './Parts/Edit/PartDestroyAccount.vue';
import PartUpdateAvatar from './Parts/Edit/PartUpdateAvatar.vue';

const props = defineProps({
  user: Object,
});

const tab = ref('information');
</script>

<template>
  <Head title="Profile Edit" />

  <FullLayout>
    <div class="pt-4">
      <div class="flex gap-6 items-start">
        <div class="flex-shrink-0 flex justify-center flex-col text-center w-[16rem]">
          <PartUpdateAvatar :user="user" />
        </div>

        <div class="w-full bg-second rounded-lg shadow-lg overflow-hidden">
          <v-tabs v-model="tab" density="compact" slider-color="primary">
            <v-tab
              value="information"
              prepend-icon="mdi-information-box-outline"
              base-color="default"
              class="text-none"
            >
              User Information
            </v-tab>
            <v-tab
              value="password"
              prepend-icon="mdi-lock-outline"
              base-color="default"
              class="text-none"
            >
              Change Password
            </v-tab>
            <v-tab
              value="delete"
              prepend-icon="mdi-close-octagon-outline"
              base-color="red"
              slider-color="red"
              class="text-none"
            >
              Destroy Account
            </v-tab>
          </v-tabs>

          <v-card-text class="!p-0 mt-2">
            <v-tabs-window v-model="tab">
              <v-tabs-window-item value="information" class="p-4">
                <PartUpdateInformation :user="user" />
              </v-tabs-window-item>

              <v-tabs-window-item value="password" class="p-4">
                <PartUpdatePassword />
              </v-tabs-window-item>

              <v-tabs-window-item value="delete" class="p-4">
                <PartDestroyAccount />
              </v-tabs-window-item>
            </v-tabs-window>
          </v-card-text>
        </div>
      </div>
    </div>
  </FullLayout>
</template>
