<script setup lang="ts">
import { ref, computed } from 'vue'; // Přidáme computed pro reaktivitu na props
import type { ApexOptions } from 'apexcharts';

const props = defineProps({
  items: {
    type: Object, // Očekáváme { series: number[], axis: string[] }
    required: true,
  },
});

// Je lepší použít computed, aby se graf překreslil, když se změní props.items
const chartSeries = computed(() => [
  {
    name: 'Počet kontaktů',
    data: props.items.series ?? [],
  },
]);

const chartOptions = computed<ApexOptions>(() => ({
  chart: {
    type: 'bar',
    height: 400,
  },
  colors: ['#1A56DB', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#6366F1', '#374151', '#EC4899'],
  plotOptions: {
    bar: {
      borderRadius: 0,
      horizontal: true,
      barHeight: '85%',
      isFunnel: true,
      distributed: true,
    },
  },
  dataLabels: {
    enabled: true,
    formatter: function (val, opt) {
      // Ochrana před prázdnými labely
      const label = opt.w.globals.labels[opt.dataPointIndex] || '';
      return label + ':  ' + val;
    },
  },
  xaxis: {
    categories: props.items.axis ?? [],
  },
  yaxis: {
    show: true,
  },
}));
</script>

<template>
  <div id="chart">
    <apexchart type="bar" height="400" :options="chartOptions" :series="chartSeries" />
  </div>
</template>
