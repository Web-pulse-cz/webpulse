<script setup lang="ts">
import { ref, watch, inject, type Ref } from 'vue';
import {
  Combobox,
  ComboboxInput,
  ComboboxOptions,
  ComboboxOption,
  TransitionRoot,
} from '@headlessui/vue';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import { debounce } from 'lodash';

interface Foodstuff {
  id: number;
  name: string;
  macronutrients: {
    calories: number | null;
    proteins: number | null;
    carbohydrates: number | null;
    fats: number | null;
    fiber: number | null;
  } | null;
}

const emit = defineEmits<{ (e: 'select', foodstuff: Foodstuff): void }>();

const selectedSiteHash = inject<Ref<string>>('selectedSiteHash', ref(''));

const query = ref('');
const results = ref<Foodstuff[]>([]);
const loading = ref(false);
const selected = ref<Foodstuff | null>(null);

async function loadItems() {
  const term = query.value.trim();
  if (term.length < 2) {
    results.value = [];
    return;
  }

  loading.value = true;
  const client = useSanctumClient();

  try {
    const response: any = await client('/api/admin/food/foodstuff', {
      method: 'GET',
      query: { search: term },
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Site-Hash': selectedSiteHash.value,
      },
    });
    results.value = Array.isArray(response) ? response : (response?.data ?? []);
  } catch (_) {
    results.value = [];
  } finally {
    loading.value = false;
  }
}

const debouncedLoad = debounce(loadItems, 350);
watch(query, debouncedLoad);

function onSelect(foodstuff: Foodstuff | null) {
  if (!foodstuff) return;
  emit('select', foodstuff);
  // Reset so the field is ready for the next food.
  selected.value = null;
  query.value = '';
  results.value = [];
}
</script>

<template>
  <Combobox :model-value="selected" @update:model-value="onSelect">
    <div class="relative">
      <div class="relative w-full">
        <MagnifyingGlassIcon
          class="pointer-events-none absolute inset-y-0 left-4 my-auto size-5 text-slate-400"
        />
        <ComboboxInput
          class="block w-full rounded-xl border-0 bg-white py-3 pl-12 pr-4 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 transition-all placeholder:text-slate-400 hover:ring-slate-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
          placeholder="Vyhledat potravinu (např. Kaiserka cereální)…"
          :display-value="() => query"
          @change="query = $event.target.value"
        />
      </div>

      <TransitionRoot
        :show="query.trim().length >= 2"
        leave="transition ease-in duration-100"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <ComboboxOptions
          static
          class="absolute z-50 mt-2 max-h-72 w-full overflow-auto rounded-2xl bg-white p-1.5 text-sm shadow-xl shadow-slate-200/50 ring-1 ring-slate-200 focus:outline-none"
        >
          <div v-if="loading" class="px-4 py-3 text-center text-sm font-medium text-slate-400">
            Vyhledávám…
          </div>
          <div
            v-else-if="results.length === 0"
            class="px-4 py-3 text-center text-sm font-medium text-slate-500"
          >
            Žádná potravina nenalezena.
          </div>

          <ComboboxOption
            v-for="food in results"
            :key="food.id"
            v-slot="{ active }"
            as="template"
            :value="food"
          >
            <li
              :class="[
                active ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700',
                'relative flex cursor-pointer select-none items-center justify-between gap-3 rounded-xl px-4 py-2.5 transition-colors',
              ]"
            >
              <span class="block truncate font-medium">{{ food.name }}</span>
              <span class="shrink-0 text-xs tabular-nums text-slate-400">
                {{ food.macronutrients?.calories ?? '?' }} kcal / 100 g
              </span>
            </li>
          </ComboboxOption>
        </ComboboxOptions>
      </TransitionRoot>
    </div>
  </Combobox>
</template>
