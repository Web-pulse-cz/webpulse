<script setup lang="ts">
const daysInMonth = (month: number, year: number) => {
  return new Date(year, month, 0).getDate();
};

const props = defineProps({
  categories: {
    type: Array,
    required: false,
    default: [],
  },
  income: {
    type: Array,
    required: false,
    default: [],
  },
  year: {
    type: Number,
    required: false,
    default: new Date().getFullYear(),
  },
  month: {
    type: Number,
    required: false,
    default: new Date().getMonth() + 1,
  },
});

const cashflowActionDialog = ref({
  show: false as boolean,
  dayRecords: [] as [],
  day: 0 as number,
  type: 'expense' as string,
  categoryId: null as number | null,
  category: null as string | null,
});

const cashflowBudgetDialog = ref({
  show: false as boolean,
  categoryId: 0 as number,
  budget: 0 as number,
});

const cashflowTotalBudgetDialog = ref({
  show: false as boolean,
  totalBudget: 0 as number,
  months: 3 as number,
});

const emit = defineEmits([
  'edit-category',
  'reorder-categories',
  'load-items',
  'save-day-records',
  'save-budget',
  'save-total-budget',
]);

const draggedIndex = ref<number | null>(null);
const dragOverIndex = ref<number | null>(null);

function onDragStart(index: number) {
  draggedIndex.value = index;
}

function onDragEnter(index: number) {
  if (draggedIndex.value !== null && index !== draggedIndex.value) {
    dragOverIndex.value = index;
  }
}

function onDrop(index: number) {
  if (draggedIndex.value === null || draggedIndex.value === index) {
    onDragEnd();
    return;
  }

  const ids = props.categories.map((category: any) => category.id);
  const [moved] = ids.splice(draggedIndex.value, 1);
  ids.splice(index, 0, moved);

  emit('reorder-categories', ids);
  onDragEnd();
}

function onDragEnd() {
  draggedIndex.value = null;
  dragOverIndex.value = null;
}

function summaryByDay(categoryId: number, day: number) {
  const category = props.categories.find((category: any) => category.id === categoryId);
  if (category) {
    return category.cashflows.reduce((acc: number, cashflow: any) => {
      if (new Date(cashflow.date).getDate() === day) {
        return acc + cashflow.amount;
      }
      return acc;
    }, 0);
  }
  return 0;
}

function incomeByDay(day: number) {
  return props.income.reduce((acc: number, cashflow: any) => {
    if (new Date(cashflow.date).getDate() === day) {
      return acc + cashflow.amount;
    }
    return acc;
  }, 0);
}

function monthlyCategoryBudget(categoryId: number) {
  const category = props.categories.find((category: any) => category.id === categoryId);
  if (category) {
    return category.budgets[0].amount;
  }
  return 0;
}

function totalSpent(categoryId: number) {
  const category = props.categories.find((category: any) => category.id === categoryId);
  if (category) {
    return category.cashflows.reduce((acc: number, cashflow: any) => {
      return acc + cashflow.amount;
    }, 0);
  }
  return 0;
}

function leftFromBudget(categoryId: number) {
  const category = props.categories.find((category: any) => category.id === categoryId);
  if (category) {
    const totalSpent = category.cashflows.reduce((acc: number, cashflow: any) => {
      return acc + cashflow.amount;
    }, 0);
    return category.budgets[0].amount - totalSpent;
  }
  return 0;
}

function descriptionByDay(day: number) {
  return props.categories.reduce((acc: string, category: any) => {
    return category.cashflows.reduce((acc: string, cashflow: any) => {
      if (new Date(cashflow.date).getDate() === day) {
        return acc ? `${acc}, ${cashflow.description}` : cashflow.description;
      }
      return acc;
    }, acc);
  }, '');
}

function descriptionByDayIncome(day: number) {
  return props.income.reduce((acc: string, cashflow: any) => {
    if (new Date(cashflow.date).getDate() === day) {
      return acc ? `${acc}, ${cashflow.description}` : cashflow.description + '';
    }
    return acc + '';
  }, '');
}

function updateCashflow(type: string, categoryId: number | null, day: number) {
  cashflowActionDialog.value.day = day;
  cashflowActionDialog.value.categoryId = categoryId;

  const category = props.categories.find((category: any) => category.id === categoryId);
  if (category) {
    const dayRecords = category.cashflows
      .filter((cashflow: any) => new Date(cashflow.date).getDate() === day)
      .map((cashflow: any) => ({
        id: cashflow.id,
        description: cashflow.description,
        amount: cashflow.amount,
        is_repeated: cashflow.is_repeated,
      }));

    cashflowActionDialog.value.dayRecords = dayRecords;
  } else {
    cashflowActionDialog.value.dayRecords = [];
  }

  cashflowActionDialog.value.type = type;
  cashflowActionDialog.value.category = category ? category.name : 'Příjem';
  cashflowActionDialog.value.show = true;
}

function updateCashflowIncome(day: number, categoryId: number | null = null) {
  cashflowActionDialog.value.day = day;
  cashflowActionDialog.value.categoryId = null;

  const dayRecords = props.income
    .filter((cashflow: any) => new Date(cashflow.date).getDate() === day)
    .map((cashflow: any) => ({
      id: cashflow.id,
      description: cashflow.description,
      amount: cashflow.amount,
      is_repeated: cashflow.is_repeated,
    }));

  cashflowActionDialog.value.dayRecords = dayRecords;
  cashflowActionDialog.value.type = 'income';
  cashflowActionDialog.value.show = true;
}

function updateBudget(categoryId: number, budget: number) {
  cashflowBudgetDialog.value.categoryId = categoryId;
  cashflowBudgetDialog.value.budget = budget;
  cashflowBudgetDialog.value.show = true;
}

function leftFromBudgetTextClass(categoryId: number) {
  const leftFromBudgetValue = leftFromBudget(categoryId);
  const totalBudget = monthlyCategoryBudget(categoryId);
  const percentageLeft = (leftFromBudgetValue / totalBudget) * 100;

  if (percentageLeft >= 50) {
    return 'bg-green-200 text-success';
  } else if (percentageLeft >= 25) {
    return 'bg-yellow-200 text-warning';
  } else {
    return 'bg-red-200 text-danger';
  }
}

function summaryMonthlySpent() {
  return props.categories.reduce((acc: number, category: any) => {
    return (
      acc +
      category.cashflows.reduce((acc: number, cashflow: any) => {
        return acc + cashflow.amount;
      }, 0)
    );
  }, 0);
}

function summaryMonthlyBudget() {
  return props.categories.reduce((acc: number, category: any) => {
    return acc + category.budgets[0].amount;
  }, 0);
}

function summaryMonthlyLeft() {
  return summaryMonthlyBudget() - summaryMonthlySpent();
}

function summaryMonthlyIncome() {
  return props.income.reduce((acc: number, cashflow: any) => acc + cashflow.amount, 0);
}

function monthlyBalance() {
  return summaryMonthlyIncome() - summaryMonthlySpent();
}

function openTotalBudgetDialog() {
  cashflowTotalBudgetDialog.value.totalBudget = summaryMonthlyBudget();
  cashflowTotalBudgetDialog.value.months = 3;
  cashflowTotalBudgetDialog.value.show = true;
}

function markRowColumn(event: MouseEvent) {
  const target = event.currentTarget as HTMLElement;
  const row = target.parentElement as HTMLElement;
  const table = row.parentElement?.parentElement as HTMLElement;
  const columnIndex = Array.from(row.children).indexOf(target);

  // Reset all cells
  table.querySelectorAll('td, th').forEach((cell) => {
    cell.classList.remove('highlight');
  });

  // Highlight the row
  row.querySelectorAll('td').forEach((cell) => {
    cell.classList.add('highlight');
  });

  // Highlight the column
  table.querySelectorAll(`tr`).forEach((row) => {
    const cell = row.children[columnIndex];
    if (cell) {
      cell.classList.add('highlight');
    }
  });
}

function formatAmount(amount: number) {
  return amount.toLocaleString('cs-CZ');
}
</script>

<template>
  <div class="px-4 sm:px-0">
    <div class="mt-8 flow-root">
      <div class="-mx-4 -my-2 overflow-x-auto pb-4 sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
          <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <table class="min-w-full border-collapse divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr class="divide-x divide-slate-200">
                  <th
                    scope="col"
                    class="w-[60px] px-3 py-3.5 text-center text-[10px] font-bold uppercase tracking-wider text-slate-500 lg:px-4 lg:text-[11px]"
                  >
                    Den
                  </th>
                  <th
                    scope="col"
                    class="w-auto px-3 py-3.5 text-right text-[10px] font-bold uppercase tracking-wider text-emerald-600 lg:px-4 lg:text-[11px]"
                  >
                    Příjem
                  </th>
                  <th
                    v-for="(category, index) in categories"
                    :key="index"
                    scope="col"
                    class="w-auto min-w-[120px] cursor-pointer px-3 py-3.5 text-right text-[10px] font-bold uppercase tracking-wider text-slate-500 transition-colors hover:bg-slate-100 lg:px-4 lg:text-[11px]"
                    :class="{
                      'opacity-40': draggedIndex === index,
                      'bg-indigo-100': dragOverIndex === index,
                    }"
                    draggable="true"
                    @click="emit('edit-category', category)"
                    @dragstart="onDragStart(index)"
                    @dragenter.prevent="onDragEnter(index)"
                    @dragover.prevent
                    @drop.prevent="onDrop(index)"
                    @dragend="onDragEnd"
                  >
                    {{ category.name }}
                  </th>
                  <th
                    scope="col"
                    class="px-3 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 lg:px-4 lg:text-[11px]"
                  >
                    Poznámky
                  </th>
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-100 bg-white">
                <tr
                  v-for="(day, index) in daysInMonth(month, year)"
                  :key="index"
                  class="divide-x divide-slate-100 transition-colors"
                >
                  <td
                    class="whitespace-nowrap bg-slate-50/30 px-3 py-2.5 text-center text-xs font-semibold text-slate-400"
                  >
                    {{ day }}.
                  </td>

                  <td
                    class="cursor-pointer whitespace-nowrap px-3 py-2.5 text-right text-xs font-medium text-emerald-600 transition-colors hover:bg-emerald-50"
                    @click="updateCashflowIncome(day)"
                  >
                    <span v-if="incomeByDay(day) > 0">{{ formatAmount(incomeByDay(day)) }} Kč</span>
                    <span v-else class="text-slate-200">-</span>
                  </td>

                  <td
                    v-for="(category, index) in categories"
                    :key="index"
                    class="cursor-pointer whitespace-nowrap px-3 py-2.5 text-right text-xs font-medium text-slate-700 transition-colors hover:bg-indigo-50"
                    @click="updateCashflow('expense', category.id, day)"
                    @mouseover="markRowColumn"
                  >
                    <span v-if="summaryByDay(category.id, day) > 0">
                      {{ formatAmount(summaryByDay(category.id, day)) }} Kč
                    </span>
                    <span v-else class="text-slate-200">-</span>
                  </td>

                  <td class="whitespace-nowrap px-3 py-2.5 text-xs text-slate-500">
                    <span
                      v-if="descriptionByDayIncome(day) !== ''"
                      class="font-medium text-emerald-600"
                    >
                      + {{ descriptionByDayIncome(day) }}
                    </span>
                    <span
                      v-if="descriptionByDayIncome(day) !== '' && descriptionByDay(day) !== ''"
                      class="mx-1 text-slate-300"
                      >|</span
                    >
                    <span v-if="descriptionByDay(day) !== ''" class="text-slate-600">
                      {{ descriptionByDay(day) }}
                    </span>
                  </td>
                </tr>

                <tr class="divide-x divide-slate-200 border-t-2 border-slate-200 bg-slate-50/50">
                  <td
                    class="whitespace-nowrap px-3 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-600"
                  >
                    Celkem utraceno
                  </td>
                  <td
                    class="whitespace-nowrap px-3 py-3 text-right text-xs font-bold text-emerald-600"
                  >
                    {{ formatAmount(summaryMonthlyIncome()) }} Kč
                  </td>
                  <td
                    v-for="(category, index) in categories"
                    :key="index"
                    class="whitespace-nowrap px-3 py-3 text-right text-xs font-bold text-slate-800"
                  >
                    {{ formatAmount(totalSpent(category.id)) }} Kč
                  </td>
                  <td
                    class="whitespace-nowrap bg-indigo-50/50 px-3 py-3 text-center text-xs font-bold text-indigo-600"
                  >
                    {{ formatAmount(summaryMonthlySpent()) }} Kč
                  </td>
                </tr>

                <tr class="divide-x divide-slate-200 bg-slate-50/50">
                  <td
                    class="whitespace-nowrap px-3 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-600"
                    colspan="2"
                  >
                    Měsíční budget
                  </td>
                  <td
                    v-for="(category, index) in categories"
                    :key="index"
                    class="cursor-pointer whitespace-nowrap px-3 py-3 text-right text-xs font-bold text-slate-500 transition-colors hover:bg-slate-200"
                    @click="updateBudget(category.id, monthlyCategoryBudget(category.id))"
                  >
                    {{ formatAmount(monthlyCategoryBudget(category.id)) }} Kč
                  </td>
                  <td
                    class="cursor-pointer whitespace-nowrap px-3 py-3 text-center text-xs font-bold text-slate-500 transition-colors hover:bg-slate-200"
                    title="Nastavit celkový budget"
                    @click="openTotalBudgetDialog"
                  >
                    {{ formatAmount(summaryMonthlyBudget()) }} Kč
                  </td>
                </tr>

                <tr class="divide-x divide-slate-200 bg-slate-50/50">
                  <td
                    class="whitespace-nowrap px-3 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-600"
                    colspan="2"
                  >
                    Zůstává z budgetu
                  </td>
                  <td
                    v-for="(category, index) in categories"
                    :key="index"
                    :class="[
                      leftFromBudgetTextClass(category.id),
                      'whitespace-nowrap px-3 py-3 text-right text-xs font-bold transition-colors',
                    ]"
                  >
                    {{ formatAmount(leftFromBudget(category.id)) }} Kč
                  </td>
                  <td
                    class="whitespace-nowrap bg-indigo-50/50 px-3 py-3 text-center text-xs font-bold text-indigo-600"
                  >
                    {{ formatAmount(summaryMonthlyLeft()) }} Kč
                  </td>
                </tr>

                <tr class="divide-x divide-slate-200 border-t-2 border-slate-300 bg-slate-100/60">
                  <td
                    class="whitespace-nowrap px-3 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-600"
                    :colspan="2 + categories.length"
                  >
                    Bilance (příjmy − výdaje)
                  </td>
                  <td
                    class="whitespace-nowrap px-3 py-3 text-center text-xs font-bold"
                    :class="
                      monthlyBalance() >= 0 ? 'bg-green-100 text-success' : 'bg-red-100 text-danger'
                    "
                  >
                    {{ formatAmount(monthlyBalance()) }} Kč
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <CashflowDialogAction
      v-model:show="cashflowActionDialog.show"
      :day="cashflowActionDialog.day"
      :day-records="cashflowActionDialog.dayRecords"
      :type="cashflowActionDialog.type"
      :category="cashflowActionDialog.category ? cashflowActionDialog.category : 'Příjem'"
      @save-day-records="
        emit(
          'save-day-records',
          cashflowActionDialog.categoryId,
          cashflowActionDialog.day,
          cashflowActionDialog.type,
          cashflowActionDialog.dayRecords,
        )
      "
    />
    <CashflowDialogBudget
      :id="cashflowBudgetDialog.categoryId"
      v-model:show="cashflowBudgetDialog.show"
      v-model:budget="cashflowBudgetDialog.budget"
      :month="month"
      :year="year"
      @save-budget="
        emit('save-budget', cashflowBudgetDialog.categoryId, cashflowBudgetDialog.budget)
      "
    />
    <CashflowDialogTotalBudget
      v-model:show="cashflowTotalBudgetDialog.show"
      v-model:total-budget="cashflowTotalBudgetDialog.totalBudget"
      v-model:months="cashflowTotalBudgetDialog.months"
      :month="month"
      :year="year"
      @save-total-budget="
        emit(
          'save-total-budget',
          cashflowTotalBudgetDialog.totalBudget,
          cashflowTotalBudgetDialog.months,
        )
      "
    />
  </div>
</template>

<style scoped>
/* Vylepšený highlight kříže (sloupec + řádek) */
.highlight {
  background-color: #f8fafc !important; /* slate-50 */
}
</style>
