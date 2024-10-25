<script setup>
import { computed, ref } from 'vue';
import { CalendarHeatmap } from 'vue3-calendar-heatmap';
import { useTheme } from 'vuetify';
import '@/../css/heatmap.css';

defineProps({
  values: {
    type: Array,
    required: true,
  },
});

const theme = useTheme();

const isDark = computed(() => theme.global.name.value === 'dark');

const endDate = new Date();

const locale = {
  months: [
    'Янв',
    'Фев',
    'Мар',
    'Апр',
    'Май',
    'Июн',
    'Июл',
    'Авг',
    'Сен',
    'Окт',
    'Ноя',
    'Дек',
  ],
  days: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
  on: 'в',
  less: 'Меньше',
  more: 'Больше',
};

const rangeColor = computed(() => {
  return isDark.value
    ? ['#252529', '#1e334a', '#1d466c', '#1d5689', '#1d69ac', '#1B95D1']
    : ['#e1e4e8', '#dae2ef', '#c0ddf9', '#73b3f3', '#3886e1', '#17459e'];
});
</script>

<template>
  <CalendarHeatmap
    :values="values"
    :end-date="endDate"
    :dark-mode="isDark"
    :round="7"
    no-data-text="Активности не было"
    tooltip-unit="активности"
    :locale="locale"
    :range-color="rangeColor"
  />
</template>
