<script setup lang="ts">
import { ref, computed, inject, onMounted, watch } from 'vue';
import { CalculatorIcon, TrashIcon, StarIcon, FireIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const route = useRoute();
const router = useRouter();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const user = useSanctumUser();

interface Macros {
  calories: number | null;
  proteins: number | null;
  carbohydrates: number | null;
  fats: number | null;
  fiber: number | null;
}

interface Row {
  key: string;
  foodstuff_id: number | null;
  name: string;
  amount: number | null;
  macros: Macros;
  missing: boolean;
}

const isNew = computed(() => route.params.id === 'pridat');

const loading = ref(false);
const saving = ref(false);
const name = ref('');
const portions = ref(1);
const rows = ref<Row[]>([]);

const pageTitle = ref(isNew.value ? 'Nová kalkulačka' : 'Kalorická kalkulačka');
const breadcrumbs = ref([
  { name: 'Kalorická kalkulačka', link: '/restaurace/kalkulacka', current: false },
  { name: pageTitle.value, link: '/restaurace/kalkulacka/' + route.params.id, current: true },
]);

let keySeq = 0;
function nextKey() {
  keySeq += 1;
  return `row-${keySeq}`;
}

function emptyMacros(): Macros {
  return { calories: null, proteins: null, carbohydrates: null, fats: null, fiber: null };
}

// --- Calculation -----------------------------------------------------------

function macroValue(row: Row, key: keyof Macros): number {
  const per100 = Number(row.macros?.[key] ?? 0);
  const amount = Number(row.amount ?? 0);
  return (per100 * amount) / 100;
}

const totals = computed(() => {
  const acc = { calories: 0, proteins: 0, carbohydrates: 0, fats: 0, fiber: 0 };
  rows.value.forEach((row) => {
    acc.calories += macroValue(row, 'calories');
    acc.proteins += macroValue(row, 'proteins');
    acc.carbohydrates += macroValue(row, 'carbohydrates');
    acc.fats += macroValue(row, 'fats');
    acc.fiber += macroValue(row, 'fiber');
  });
  return acc;
});

// Portions "multiply" the per-portion values entered in the table.
const grandTotals = computed(() => {
  const p = Math.max(1, Number(portions.value) || 1);
  return {
    calories: totals.value.calories * p,
    proteins: totals.value.proteins * p,
    carbohydrates: totals.value.carbohydrates * p,
    fats: totals.value.fats * p,
    fiber: totals.value.fiber * p,
  };
});

function fmt(value: number, decimals = 1): string {
  if (!isFinite(value)) return '0';
  return value.toFixed(decimals).replace(/\.0$/, '');
}

// --- Row management --------------------------------------------------------

function addFoodstuff(food: any) {
  rows.value.push({
    key: nextKey(),
    foodstuff_id: food.id,
    name: food.name,
    amount: 100,
    macros: {
      calories: food.macronutrients?.calories ?? null,
      proteins: food.macronutrients?.proteins ?? null,
      carbohydrates: food.macronutrients?.carbohydrates ?? null,
      fats: food.macronutrients?.fats ?? null,
      fiber: food.macronutrients?.fiber ?? null,
    },
    missing: false,
  });
}

function removeRow(key: string) {
  rows.value = rows.value.filter((r) => r.key !== key);
}

// --- Load saved measurement ------------------------------------------------

async function loadMeasurement() {
  if (isNew.value) return;
  loading.value = true;
  const client = useSanctumClient();

  try {
    const measurement: any = await client(
      '/api/admin/food/calorie-measurement/' + route.params.id,
      {
        method: 'GET',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-Site-Hash': selectedSiteHash.value,
        },
      },
    );

    // Fetch current foodstuffs to recompute macros live.
    const foods: any = await client('/api/admin/food/foodstuff', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Site-Hash': selectedSiteHash.value,
      },
    });
    const foodList: any[] = Array.isArray(foods) ? foods : (foods?.data ?? []);
    const byId = new Map<number, any>(foodList.map((f) => [f.id, f]));

    name.value = measurement.name ?? '';
    portions.value = measurement.portions ?? 1;
    rows.value = (measurement.items ?? []).map((item: any) => {
      const food = item.foodstuff_id ? byId.get(item.foodstuff_id) : null;
      return {
        key: nextKey(),
        foodstuff_id: item.foodstuff_id ?? null,
        name: food?.name ?? item.name,
        amount: item.amount ?? null,
        macros: food ? { ...emptyMacros(), ...(food.macronutrients ?? {}) } : emptyMacros(),
        missing: !!item.foodstuff_id && !food,
      };
    });

    pageTitle.value = name.value || 'Kalorická kalkulačka';
    breadcrumbs.value[1].name = pageTitle.value;
  } catch (_) {
    $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst měření.', severity: 'error' });
    router.push('/restaurace/kalkulacka');
  } finally {
    loading.value = false;
  }
}

// --- Save ------------------------------------------------------------------

function currentSiteId(): number | null {
  const sites = (user.value as any)?.sites ?? [];
  const site = sites.find((s: any) => s.hash === selectedSiteHash.value);
  return site?.id ?? null;
}

async function save() {
  if (!name.value.trim()) {
    $toast.show({
      summary: 'Chybí název',
      detail: 'Zadejte název měření pro uložení do oblíbených.',
      severity: 'warn',
    });
    return;
  }
  if (!rows.value.length) {
    $toast.show({
      summary: 'Prázdné měření',
      detail: 'Přidejte alespoň jednu potravinu.',
      severity: 'warn',
    });
    return;
  }

  saving.value = true;
  const client = useSanctumClient();
  const siteId = currentSiteId();

  const payload = {
    name: name.value.trim(),
    portions: Math.max(1, Number(portions.value) || 1),
    items: rows.value.map((r) => ({
      foodstuff_id: r.foodstuff_id,
      name: r.name,
      amount: Number(r.amount ?? 0),
    })),
    sites: siteId ? [siteId] : [],
  };

  try {
    const response: any = await client(
      isNew.value
        ? '/api/admin/food/calorie-measurement'
        : '/api/admin/food/calorie-measurement/' + route.params.id,
      {
        method: 'POST',
        body: JSON.stringify(payload),
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-Site-Hash': selectedSiteHash.value,
        },
      },
    );

    $toast.show({
      summary: 'Uloženo',
      detail: 'Měření bylo uloženo do oblíbených.',
      severity: 'success',
    });

    if (isNew.value && response?.id) {
      router.replace('/restaurace/kalkulacka/' + response.id);
    }
  } catch (_) {
    $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit měření.', severity: 'error' });
  } finally {
    saving.value = false;
  }
}

watch(selectedSiteHash, () => {
  if (!isNew.value) loadMeasurement();
});

useHead({ title: pageTitle.value });
onMounted(() => loadMeasurement());
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-24">
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="calorie_measurements" />

    <LayoutContainer>
      <!-- Search -->
      <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-5">
        <div
          class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
        >
          <CalculatorIcon class="size-5" />
        </div>
        <div>
          <LayoutTitle class="!mb-0">Kalorická kalkulačka</LayoutTitle>
          <p class="text-xs text-slate-500">Vyhledejte potravinu a přidejte ji do tabulky.</p>
        </div>
      </div>

      <RestaurantFoodstuffAutocomplete class="mb-6" @select="addFoodstuff" />

      <!-- Table -->
      <div class="overflow-x-auto rounded-2xl ring-1 ring-slate-200">
        <table class="w-full min-w-[720px] text-left text-sm">
          <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
            <tr>
              <th class="px-4 py-3">Název</th>
              <th class="px-4 py-3 text-center">Množství (g/ml)</th>
              <th class="px-4 py-3 text-center">Kalorie</th>
              <th class="px-4 py-3 text-center">Sacharidy</th>
              <th class="px-4 py-3 text-center">Bílkoviny</th>
              <th class="px-4 py-3 text-center">Tuky</th>
              <th class="px-4 py-3 text-center">Akce</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="row in rows" :key="row.key" class="hover:bg-slate-50/60">
              <td class="px-4 py-3 font-medium text-slate-800">
                {{ row.name }}
                <span
                  v-if="row.missing"
                  class="ml-1 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-700"
                  title="Potravina už neexistuje – hodnoty nelze přepočítat"
                >
                  smazáno
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <input
                  v-model.number="row.amount"
                  type="number"
                  min="0"
                  inputmode="numeric"
                  class="w-24 rounded-xl border-0 px-3 py-2 text-center text-sm font-semibold tabular-nums text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500"
                />
              </td>
              <td class="px-4 py-3 text-center font-semibold tabular-nums text-slate-900">
                {{ fmt(macroValue(row, 'calories'), 0) }}
              </td>
              <td class="px-4 py-3 text-center tabular-nums text-slate-600">
                {{ fmt(macroValue(row, 'carbohydrates')) }} g
              </td>
              <td class="px-4 py-3 text-center tabular-nums text-slate-600">
                {{ fmt(macroValue(row, 'proteins')) }} g
              </td>
              <td class="px-4 py-3 text-center tabular-nums text-slate-600">
                {{ fmt(macroValue(row, 'fats')) }} g
              </td>
              <td class="px-4 py-3 text-center">
                <button
                  type="button"
                  class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                  title="Odebrat"
                  @click="removeRow(row.key)"
                >
                  <TrashIcon class="size-4" />
                </button>
              </td>
            </tr>

            <tr v-if="!rows.length">
              <td colspan="7" class="px-4 py-10 text-center text-sm italic text-slate-400">
                Zatím žádné potraviny. Přidejte je vyhledáním výše.
              </td>
            </tr>
          </tbody>

          <!-- Footer: totals -->
          <tfoot v-if="rows.length" class="border-t-2 border-slate-200 bg-slate-50">
            <tr class="font-bold text-slate-900">
              <td class="px-4 py-3">
                <span class="text-[11px] uppercase tracking-wide text-slate-500"
                  >Celkem (1 porce)</span
                >
              </td>
              <td class="px-4 py-3"></td>
              <td class="px-4 py-3 text-center tabular-nums">{{ fmt(totals.calories, 0) }}</td>
              <td class="px-4 py-3 text-center tabular-nums">{{ fmt(totals.carbohydrates) }} g</td>
              <td class="px-4 py-3 text-center tabular-nums">{{ fmt(totals.proteins) }} g</td>
              <td class="px-4 py-3 text-center tabular-nums">{{ fmt(totals.fats) }} g</td>
              <td class="px-4 py-3"></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Portions + grand total -->
      <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700">Počet porcí</label>
          <input
            v-model.number="portions"
            type="number"
            min="1"
            inputmode="numeric"
            class="w-28 rounded-xl border-0 px-3 py-2.5 text-center text-sm font-semibold tabular-nums text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500"
          />
          <p class="mt-1 text-xs text-slate-400">Vynásobí celkovou hodnotu tabulky.</p>
        </div>

        <div class="rounded-2xl bg-indigo-50 px-5 py-4 ring-1 ring-inset ring-indigo-100">
          <div class="mb-2 flex items-center gap-2 text-indigo-700">
            <FireIcon class="size-4" />
            <span class="text-[11px] font-bold uppercase tracking-widest">
              Celkem × {{ Math.max(1, Number(portions) || 1) }} porcí
            </span>
          </div>
          <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm">
            <div>
              <span class="font-extrabold tabular-nums text-slate-900">{{
                fmt(grandTotals.calories, 0)
              }}</span>
              <span class="text-xs text-slate-500"> kcal</span>
            </div>
            <div>
              <span class="font-bold tabular-nums text-slate-800"
                >{{ fmt(grandTotals.carbohydrates) }} g</span
              >
              <span class="text-xs text-slate-500"> sach.</span>
            </div>
            <div>
              <span class="font-bold tabular-nums text-slate-800"
                >{{ fmt(grandTotals.proteins) }} g</span
              >
              <span class="text-xs text-slate-500"> bílk.</span>
            </div>
            <div>
              <span class="font-bold tabular-nums text-slate-800"
                >{{ fmt(grandTotals.fats) }} g</span
              >
              <span class="text-xs text-slate-500"> tuky</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Save to favorites -->
      <div class="mt-8 flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-end">
        <div class="flex-1">
          <label class="mb-1.5 block text-sm font-medium text-slate-700">Název měření</label>
          <input
            v-model="name"
            type="text"
            placeholder="např. Snídaně – kaiserka s tvarohem"
            class="w-full rounded-xl border-0 px-4 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500"
          />
        </div>
        <button
          type="button"
          :disabled="saving"
          class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-indigo-700 disabled:opacity-50"
          @click="save"
        >
          <StarIcon class="size-4" />
          {{ isNew ? 'Uložit do oblíbených' : 'Uložit změny' }}
        </button>
      </div>
    </LayoutContainer>
  </div>
</template>
