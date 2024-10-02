<script setup>
import { onMounted, ref, watch, computed } from 'vue';
import ItemTitle from '@/Components/Item/ItemTitle.vue';
import ItemStudio from '@/Components/Item/ItemStudio.vue';
import { useDisplay } from 'vuetify';

const { mobile, platform } = useDisplay();

const QUERY_MIN_LENGTH = 3;
const TYPING_DELAY_MS = 500;

const dialog = ref(false);
const searchKeyHint = ref(null);

const type = ref('title');
const types = ref([
  { value: 'title', label: 'Тайтл' },
  { value: 'studio', label: 'Студия' },
  // TODO:
  //   { value: 'director', label: 'Режиссёр' },
  //   { value: 'voice_actor', label: 'Актёр озвучки' },
  //   { value: 'character', label: 'Персонаж' },
]);

const loading = ref(false);
const query = ref({
  raw: '',
  exact: '',
});
const timer = ref(null);

const items = ref({
  title: [],
  studio: [],
  director: [],
  voice_actor: [],
  character: [],
});

const randomItems = ref({
  title: [],
  studio: [],
  director: [],
  voice_actor: [],
  character: [],
});

const hasQuery = computed(() => {
  return query.value.exact.length >= QUERY_MIN_LENGTH;
});

const itemsList = computed(() => {
  if (hasQuery.value) {
    return items.value[type.value];
  }

  return randomItems.value[type.value];
});

const listItemComponent = computed(() => {
  return {
    title: ItemTitle,
    studio: ItemStudio,
    director: ItemTitle,
    voice_actor: ItemTitle,
    character: ItemTitle,
  }[type.value];
});

const listLines = computed(() => {
  if (type.value === 'studio') {
    return 'one';
  }
  return 'two';
});

watch(dialog, (newVal) => {
  if (newVal) {
    loadRandomItems();
  }
});

watch(type, () => {
  if (hasQuery.value && !items.value[type.value].length) {
    loadItems();
  }
});

watch(
  () => query.value.raw,
  (newVal) => {
    if (newVal === null) {
      query.value.raw = '';
      return;
    }

    const len = newVal.length;
    clearTimeout(timer.value);

    timer.value = setTimeout(() => {
      if (len >= QUERY_MIN_LENGTH) {
        query.value.exact = newVal;
      } else if (hasQuery.value && len === 0) {
        query.value.exact = '';
      }
    }, TYPING_DELAY_MS);
  }
);

watch(
  () => query.value.exact,
  (newVal) => {
    resetOtherTabs();

    if (hasQuery.value) {
      loadItems();
    } else {
      items.value[type.value] = [];
    }
  }
);

const resetOtherTabs = () => {
  for (const itemsType of Object.keys(items.value)) {
    if (itemsType !== type.value) {
      items.value[itemsType] = [];
    }
  }
};

const loadItems = async () => {
  loading.value = true;

  axios
    .get(
      route('upi.search.query', {
        type: type.value,
        query: query.value.exact,
      })
    )
    .then(({ data }) => {
      items.value[type.value] = data;
    })
    .finally(() => {
      loading.value = false;
    });
};

const loadRandomItems = () => {
  loading.value = true;

  axios
    .get(route('upi.search.random'))
    .then(({ data }) => {
      randomItems.value = data;
    })
    .finally(() => {
      loading.value = false;
    });
};

onMounted(() => {
  if (platform.value.mac || platform.value.win || platform.value.linux) {
    searchKeyHint.value = platform.value.mac ? 'cmd+k' : 'ctrl+k';

    window.addEventListener('keydown', (event) => {
      if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
        event.preventDefault();
        dialog.value = true;
      }
    });
  }
});
</script>

<template>
  <v-dialog v-model="dialog" max-width="600" :opacity="0.6">
    <template v-slot:activator="{ props: activatorProps }">
      <v-btn
        variant="tonal"
        color="grey"
        class="text-none !justify-between"
        width="200"
        rounded="lg"
        v-bind="activatorProps"
      >
        <template #prepend>
          <v-icon icon="mdi-magnify" />
          <span class="ml-1">Search</span>
        </template>

        <template #append v-if="searchKeyHint">
          <v-chip size="small" variant="tonal" rounded="sm" density="comfortable">
            {{ searchKeyHint }}
          </v-chip>
        </template>
      </v-btn>
    </template>

    <template v-slot:default="{ isActive }">
      <v-card rounded="lg">
        <v-card-text class="!p-4 !pb-0">
          <v-text-field
            v-model="query.raw"
            density="compact"
            placeholder="What are you looking for?"
            rounded="lg"
            variant="solo"
            autofocus
            hide-details
            single-line
            clearable
            persistent-clear
          />

          <v-tabs v-model="type" density="compact" class="mt-4 w-fit rounded-lg">
            <v-tab
              v-for="({ value, label }, index) in types"
              :value="value"
              class="text-none"
              :ripple="false"
            >
              {{ label }}
            </v-tab>
          </v-tabs>

          <div v-if="loading" class="p-12 d-flex justify-center items-center">
            <v-progress-circular indeterminate :size="50" :width="2" color="primary" />
          </div>
          <div
            v-else-if="itemsList.length === 0"
            class="p-12 d-flex justify-center items-center"
          >
            {{ type }} not found 🤷‍♂️
          </div>
          <div v-else>
            <v-list :lines="listLines" density="comfortable" max-height="500">
              <template v-for="(item, index) in itemsList" :key="index">
                <component :is="listItemComponent" :item="item" />
              </template>
            </v-list>
          </div>
        </v-card-text>
      </v-card>
    </template>
  </v-dialog>
</template>
