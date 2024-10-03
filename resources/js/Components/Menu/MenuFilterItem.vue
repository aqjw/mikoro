<script setup>
const { labelKey, options, items } = defineProps({
  title: String,
  labelKey: {
    type: String,
    default: 'name',
  },
  options: Array,
  items: Object,
});

const toggleOption = (value) => {
  if (items.incl.includes(value)) {
    items.incl = items.incl.filter((item) => item !== value);
    items.excl.push(value);
  } else if (items.excl.includes(value)) {
    items.excl = items.excl.filter((item) => item !== value);
  } else {
    items.incl.push(value);
  }
};

const getOptionColor = (value) => {
  if (items.incl.includes(value)) {
    return 'green';
  } else if (items.excl.includes(value)) {
    return 'red';
  }

  return 'default';
};

const getOptionIcon = (value) => {
  if (items.incl.includes(value)) {
    return 'mdi-check-circle-outline';
  } else if (items.excl.includes(value)) {
    return 'mdi-close-circle-outline';
  }

  return 'mdi-circle-outline';
};

const getOptionVariant = (value) => {
  return items.incl.includes(value) || items.excl.includes(value) ? 'tonal' : 'text';
};

const getOptionLabels = (type) => {
  return items[type].map((key) => options.find((option) => option.id === key)[labelKey]);
};
</script>

<template>
  <v-menu
    rounded
    location="bottom end"
    origin="auto"
    offset="5"
    :close-on-content-click="false"
  >
    <template v-slot:activator="{ props }">
      <div class="flex justify-between">
        <div class="flex items-center">
          <span class="font-medium text-lg text-gray-700 dark:text-gray-200 mr-2">
            {{ title }}
          </span>

          <div class=" rounded-md overflow-hidden">
          <v-chip
            v-if="items.incl.length"
            density="compact"
            :rounded="false"
            variant="tonal"
            color="green"
          >
            {{ items.incl.length }}
          </v-chip>
          <v-chip
            v-if="items.excl.length"
            density="compact"
            :rounded="false"
            variant="tonal"
            color="red"
          >
            {{ items.excl.length }}
          </v-chip>
          </div>
        </div>
        <v-btn
          variant="tonal"
          density="compact"
          rounded="sm"
          color="primary"
          icon="mdi-list-status"
          v-bind="props"
        />
      </div>

      <div class="flex flex-col gap-1">
        <div
          v-if="items.incl.length"
          class="flex gap-1 text-gray-600 dark:text-gray-400 font-medium"
        >
          {{ getOptionLabels('incl').join(', ') }}
        </div>

        <div v-if="items.excl.length" class="text-red-600 font-medium">
          <span class="font-semibold">Исключить:</span>
          {{ getOptionLabels('excl').join(', ') }}
        </div>
      </div>
    </template>

    <v-card max-width="250px" max-height="400px">
      <v-card-text class="!p-1">
        <div class="d-flex flex-col gap-1">
          <v-chip
            v-for="option in options"
            :key="option.id"
            :ripple="false"
            :rounded="false"
            :variant="getOptionVariant(option.id)"
            :prepend-icon="getOptionIcon(option.id)"
            :color="getOptionColor(option.id)"
            @click="toggleOption(option.id)"
          >
            {{ option[labelKey] }}
          </v-chip>
        </div>
      </v-card-text>
    </v-card>
  </v-menu>
</template>
