<script setup lang="ts">
import { ref } from 'vue';
import type { ApexOptions } from 'apexcharts';

const props = defineProps({
  items: {
    type: Object,
    required: true,
  },
  activities: {
    type: Object,
    required: true,
  },
});

const chart = ref<{
  series: { name: string; data: number[] }[];
  options: ApexOptions;
}>({
  series: props.items.business.series ?? [],
  options: {
    chart: {
      height: 350,
      type: 'heatmap',
      zoom: { enabled: false },
    },
    dataLabels: {
      enabled: true,
    },
    title: {
      text: 'Růst byznysu - heatmapa',
    },
    colors: props.items.businessColors ?? ['#008FFB'],
  },
});
</script>

<template>
  <div id="chart">
    <apexchart type="heatmap" height="350" :options="chart.options" :series="chart.series" />
  </div>
</template>
