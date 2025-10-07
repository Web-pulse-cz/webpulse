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

const emit = defineEmits(['create-category', 'load-items', 'save-day-records', 'save-budget']);

function handleMouseOver(event: MouseEvent) {
  const target = event.currentTarget as HTMLElement;
  const verticalLine = target.querySelector('.vertical-line::after');
  if (verticalLine) {
    verticalLine.style.display = 'flex';
  }
}
function handleClick() {
  emit('create-category');
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
      }));

    cashflowActionDialog.value.dayRecords = dayRecords;
  } else {
    cashflowActionDialog.value.dayRecords = [];
  }

  // Přidání prázdného záznamu mimo reaktivní změny
  cashflowActionDialog.value.dayRecords.push({
    id: null,
    description: '',
    amount: 0,
  });
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
    }));

  cashflowActionDialog.value.dayRecords = dayRecords;

  // Přidání prázdného záznamu mimo reaktivní změny
  cashflowActionDialog.value.dayRecords.push({
    id: null,
    description: '',
    amount: 0,
  });
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
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
          <table class="min-w-full divide-y divide-gray-300">
            <thead>
              <tr class="divide-x divide-gray-300">
                <th
                  scope="col"
                  class="w-[80px] bg-primaryLight px-2 py-2 text-center text-xxs font-semibold text-gray-300 lg:px-4 lg:py-3.5 lg:text-xs"
                >
                  Datum
                </th>
                <th
                  scope="col"
                  class="w-auto bg-primaryLight py-2 pl-2 pr-2 text-left text-xs font-semibold text-gray-300 sm:pr-0 lg:py-3.5 lg:pl-4 lg:pr-4 lg:text-xs"
                >
                  Příjem
                </th>
                <th
                  v-for="(category, index) in categories"
                  :key="index"
                  scope="col"
                  class="w-auto min-w-[130px] bg-primaryLight px-2 py-2 text-left text-xs font-semibold text-gray-300 lg:px-4 lg:py-3.5 lg:text-xs"
                  :class="{ 'vertical-line': index === categories.length - 1 }"
                  @mouseover="handleMouseOver"
                  @click="handleClick"
                >
                  {{ category.name }}
                </th>
                <th
                  scope="col"
                  class="bg-primaryLight py-2 pl-2 pr-2 text-left text-xs font-semibold text-gray-300 sm:pr-0 lg:py-3.5 lg:pl-4 lg:pr-4 lg:text-xs"
                >
                  Poznámky
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr
                v-for="(day, index) in daysInMonth(month, year)"
                :key="index"
                class="divide-x divide-gray-200"
              >
                <td
                  class="whitespace-nowrap p-1 text-center text-sm font-semibold text-gray-500 lg:p-2 lg:text-xs"
                >
                  {{ day }}
                </td>
                <td
                  class="cursor-pointer whitespace-nowrap p-1 text-end text-xs text-gray-500 hover:bg-gray-100 lg:p-2"
                  @click="updateCashflowIncome(day)"
                >
                  <span v-if="incomeByDay(day) > 0">{{ formatAmount(incomeByDay(day)) }} Kč</span>
                </td>
                <td
                  v-for="(category, index) in categories"
                  :key="index"
                  class="cursor-pointer whitespace-nowrap p-1 text-end text-xs text-gray-500 hover:bg-gray-100 lg:p-2"
                  @click="updateCashflow('expense', category.id, day)"
                  @mouseover="markRowColumn"
                >
                  <span v-if="summaryByDay(category.id, day) > 0"
                    >{{ formatAmount(summaryByDay(category.id, day)) }} Kč</span
                  >
                </td>
                <td class="whitespace-nowrap p-1 text-xs text-gray-500 lg:p-2">
                  <span v-if="descriptionByDayIncome(day) !== ''" class="text-success">{{ descriptionByDayIncome(day)}} </span>
                  <span v-if="descriptionByDayIncome(day) !== ''">, </span>
                  <span v-if="descriptionByDay(day) !== ''" class="text-danger">{{ descriptionByDay(day)}}</span>
                </td>
              </tr>
              <tr class="divide-x divide-gray-200">
                <td
                  class="whitespace-nowrap p-1 text-center text-xs font-semibold text-gray-500 lg:p-2 lg:text-xs"
                  colspan="2"
                >
                  Celkem utraceno
                </td>
                <td
                  v-for="(category, index) in categories"
                  :key="index"
                  class="whitespace-nowrap p-1 text-end text-xs font-semibold text-gray-500 lg:p-2 lg:text-xs"
                >
                  {{ formatAmount(totalSpent(category.id)) + ' Kč' }}
                </td>
                <td
                  class="whitespace-nowrap p-1 text-center text-xs font-semibold text-sky-500 lg:p-2 lg:text-xs"
                >
                  {{ formatAmount(summaryMonthlySpent()) }} Kč
                </td>
              </tr>
              <tr class="divide-x divide-gray-200">
                <td
                  class="whitespace-nowrap p-1 text-center text-xs font-semibold text-gray-500 lg:p-2 lg:text-xs"
                  colspan="2"
                >
                  Měsíční budget
                </td>
                <td
                  v-for="(category, index) in categories"
                  :key="index"
                  class="cursor-pointer whitespace-nowrap p-1 text-end text-xs font-semibold text-gray-500 hover:bg-gray-100 lg:p-2 lg:text-xs"
                  @click="updateBudget(category.id, monthlyCategoryBudget(category.id))"
                >
                  {{ formatAmount(monthlyCategoryBudget(category.id)) + ' Kč' }}
                </td>
                <td
                  class="whitespace-nowrap p-1 text-center text-xs font-semibold text-sky-500 lg:p-2 lg:text-xs"
                >
                  {{ formatAmount(summaryMonthlyBudget()) }} Kč
                </td>
              </tr>
              <tr class="divide-x divide-gray-200">
                <td
                  class="whitespace-nowrap p-1 text-center text-xs font-semibold text-gray-500 lg:p-2 lg:text-xs"
                  colspan="2"
                >
                  Zůstává z budgetu
                </td>
                <td
                  v-for="(category, index) in categories"
                  :key="index"
                  :class="
                    leftFromBudgetTextClass(category.id) +
                    ' whitespace-nowrap p-1 text-end text-xs font-semibold lg:p-2 lg:text-xs'
                  "
                >
                  {{ formatAmount(leftFromBudget(category.id)) + ' Kč' }}
                </td>
                <td
                  class="whitespace-nowrap p-1 text-center text-xs font-semibold text-sky-500 lg:p-2 lg:text-xs"
                >
                  {{ formatAmount(summaryMonthlyLeft()) }} Kč
                </td>
              </tr>
            </tbody>
          </table>
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
      @save-budget="
        emit('save-budget', cashflowBudgetDialog.categoryId, cashflowBudgetDialog.budget)
      "
    />
  </div>
</template>

<style scoped>
.vertical-line {
  position: relative;
}

.vertical-line::after {
  content: '+';
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  width: 32px;
  background-color: blue;
  display: none;
  cursor: pointer;
  align-items: center;
  justify-content: center;
  color: #fff;
}

.vertical-line:hover::after {
  display: flex;
}

.highlight {
  background-color: #f9fafb;
}
</style>
