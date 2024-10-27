<script setup>
import useMedia from '@/Plugins/useMedia';
import { ref } from 'vue';
import { useToast } from 'vue-toast-notification';
import { initials } from '@/Utils';
import { useUserStore } from '@/Stores';

const props = defineProps({
  user: Object,
});

const userStore = useUserStore();
const $toast = useToast();

const avatar = ref(props.user.avatar);
const fileInput = ref(null);
const bounceOnce = ref(false);
const processing = ref(false);

const compressImageToBase64 = (file, maxWidth = 230, maxHeight = 230) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = (event) => {
      const img = new Image();
      img.onload = () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        // Calculate cropping dimensions for a centered 1:1 aspect ratio
        let cropSize;
        let offsetX = 0;
        let offsetY = 0;

        if (img.width > img.height) {
          cropSize = img.height;
          offsetX = (img.width - img.height) / 2;
        } else {
          cropSize = img.width;
          offsetY = (img.height - img.width) / 2;
        }

        // Set canvas to the max dimensions for compression
        canvas.width = maxWidth;
        canvas.height = maxHeight;

        // Draw the cropped image to fit within the 1:1 canvas
        ctx.drawImage(
          img,
          offsetX,
          offsetY,
          cropSize,
          cropSize, // Crop source image
          0,
          0,
          maxWidth,
          maxHeight // Draw to canvas with max dimensions
        );

        // Adjust quality as needed
        const base64String = canvas.toDataURL('image/jpeg', 0.7);
        resolve(base64String);
      };
      img.onerror = reject;
      img.src = event.target.result;
    };
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
};

const uploadAvatar = async (e) => {
  const file = e.target.files[0] ?? null;
  fileInput.value.value = null;
  if (!file) return;

  // Ensure the file is an image
  if (!file.type.startsWith('image/')) {
    $toast.error('Please select a valid image file');
    return;
  }

  const base64Avatar = await compressImageToBase64(file);

  bounceOnceAndStopProcessing(() => {
    avatar.value = base64Avatar;
  });

  processing.value = true;
  axios
    .post(route('upi.profile.update_avatar'), {
      base64: base64Avatar,
    })
    .then(({ data }) => {
      userStore.setUser(data.user);
      $toast.success('Avatar uploaded successfully');
    })
    .catch(({ response }) => {
      $toast.error('Failed to upload avatar');
    })
    .finally(() => {
      processing.value = false;
    });
};

const deleteAvatar = async () => {
  processing.value = true;

  axios
    .delete(route('upi.profile.delete_avatar'))
    .then(({ data }) => {
      $toast.success('Avatar deleted successfully');

      bounceOnceAndStopProcessing(() => {
        userStore.setUser(data.user);
        avatar.value = null;
      });
    })
    .catch(({ response }) => {
      $toast.error('Failed to delete avatar');
    })

    .finally(() => {
      processing.value = false;
    });
};

const bounceOnceAndStopProcessing = (halfCallback) => {
  bounceOnce.value = true;
  setTimeout(halfCallback, 250);
  setTimeout(() => (bounceOnce.value = false), 500);
};
</script>

<template>
  <div
    class="bg-second shadow-lg p-4 rounded-full overflow-hidden relative"
    :class="{ 'bounce-once': bounceOnce }"
  >
    <div
      v-if="processing"
      class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2"
    >
      <v-progress-circular
        indeterminate
        color="primary"
        class="!h-[15rem] !w-[15rem]"
        :width="2"
      />
    </div>

    <v-img
      class="h-56 w-56 bg-zinc-400 dark:bg-zinc-500 rounded-full overflow-hidden relative"
      :src="$media.image(avatar)"
    >
      <span v-if="!avatar" class="text-4xl font-semibold text-white absolute-center">
        {{ initials(user.name) }}
      </span>

      <div
        v-if="!processing"
        :class="[
          'absolute left-1/2 bottom-4 -translate-x-1/2',
          'flex rounded-[1rem] overflow-hidden bg-white',
        ]"
      >
        <v-btn
          density="comfortable"
          color="primary"
          variant="plain"
          icon="mdi-upload"
          :rounded="false"
          @click="fileInput.click()"
        />

        <input
          type="file"
          ref="fileInput"
          class="hidden"
          accept="image/*"
          @change="uploadAvatar"
        />

        <v-btn
          v-if="avatar"
          density="comfortable"
          color="red"
          variant="plain"
          icon="mdi-delete-outline"
          :rounded="false"
          @click="deleteAvatar"
        />
      </div>
    </v-img>
  </div>
</template>
