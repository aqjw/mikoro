<script setup>
const props = defineProps({
  user: Object,
});

const { user } = props;
const initials = user.name
  .match(/(\b\S)?/g)
  .join('')
  .match(/(^\S|\S$)?/g)
  .join('');
</script>

<template>
  <v-menu rounded location="bottom end" origin="auto" :close-on-content-click="false">
    <template v-slot:activator="{ props }">
      <v-btn icon v-bind="props">
        <v-avatar color="brown" size="small">
          <span class="text-xs">{{ initials }}</span>
        </v-avatar>
      </v-btn>
    </template>

    <v-card min-width="200px">
      <v-card-text>
        <div class="mx-auto text-center">
          <v-avatar color="brown">
            <span class="text-base">{{ initials }}</span>
          </v-avatar>
          <h3>{{ user.name }}</h3>
          <p class="text-caption mt-1">
            {{ user.email }}
          </p>

          <v-divider class="my-3 -mx-4"></v-divider>
          <v-btn variant="text" rounded> Edit Account </v-btn>

          <v-divider class="my-3 -mx-4"></v-divider>
          <v-btn
            :to="{
                href: route('logout'),
              method: 'post',
            }"
            variant="tonal"
            color="red-lighten-1"
            class="text-none"
          >
            Log out
          </v-btn>
        </div>
      </v-card-text>
    </v-card>
  </v-menu>
</template>
