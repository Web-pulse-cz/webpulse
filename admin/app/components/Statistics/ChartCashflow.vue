<script setup lang="ts">
import { ref } from 'vue';
import type { ApexOptions } from 'apexcharts';

const props = defineProps({
  items: {
    type: Object,
    required: true,
  },
});

function getMax() {
  const max = Math.max(...props.items.business.series[0].data);
  return Math.ceil(max / 10) * 10 + 2;
}
const chart = ref<{
  series: { name: string; data: number[] }[];
  options: ApexOptions;
}>({
  series: [
    {
      name: 'Actual',
      data: props.items.cashflow,
    },
  ],
  options: {
    chart: {
      height: 400,
      type: 'bar',
    },
    plotOptions: {
      bar: {
        columnWidth: '60%',
      },
    },
    colors: ['#7dd3fc'],
    dataLabels: {
      enabled: false,
    },
    legend: {
      show: true,
      showForSingleSeries: true,
      customLegendItems: ['Utraceno', 'Budget'],
      markers: {
        fillColors: ['#7dd3fc', '#7f1d1d'],
      },
    },
  },
});
</script>

<template>
  <div id="chart">
    <apexchart type="bar" height="400" :options="chart.options" :series="chart.series" />
  </div>
</template>
