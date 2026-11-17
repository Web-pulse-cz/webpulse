<script setup lang="ts">
import { ref } from 'vue';
import type { ApexOptions } from 'apexcharts';

const props = defineProps({
  items: {
    type: Object,
    required: true,
  },
});

function formatAmount(amount: number) {
  return (amount ?? 0).toLocaleString('cs-CZ');
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
  <div>
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
      <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Celkové příjmy</p>
        <p class="mt-1 text-xl font-bold text-emerald-600">
          {{ formatAmount(items.cashflowSummary?.income) }} Kč
        </p>
      </div>
      <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Celkové výdaje</p>
        <p class="mt-1 text-xl font-bold text-danger">
          {{ formatAmount(items.cashflowSummary?.expense) }} Kč
        </p>
      </div>
      <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Rozdíl</p>
        <p
          class="mt-1 text-xl font-bold"
          :class="(items.cashflowSummary?.difference ?? 0) >= 0 ? 'text-success' : 'text-danger'"
        >
          {{ formatAmount(items.cashflowSummary?.difference) }} Kč
        </p>
      </div>
    </div>

    <div id="chart">
      <apexchart type="bar" height="400" :options="chart.options" :series="chart.series" />
    </div>
  </div>
</template>
