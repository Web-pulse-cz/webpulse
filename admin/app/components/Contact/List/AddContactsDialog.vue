<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { UserPlusIcon } from '@heroicons/vue/24/outline';
import _ from 'lodash';

type TriState = boolean | null;
type LastContacted = '1m' | '3m' | '6m' | '1y' | null;

interface LookupItem {
  id: number;
  name: string;
  color?: string;
}

const show = defineModel('show', {
  type: Boolean,
  default: false,
});

const props = defineProps({
  listId: {
    type: [Number, String],
    required: true,
  },
});

const emit = defineEmits(['added']);

const { $toast } = useNuxtApp();

const defaultFilters = () => ({
  phone: null as TriState,
  email: null as TriState,
  phases: [] as number[],
  sources: [] as number[],
  goal: null as TriState,
  occupation: null as TriState,
  last_contacted: null as LastContacted,
});

const filters = ref(defaultFilters());
const phases = ref<LookupItem[]>([]);
const sources = ref<LookupItem[]>([]);
const matchCount = ref<number | null>(null);
const countLoading = ref(false);
const saving = ref(false);

const triStateFields: Array<{ key: 'phone' | 'email' | 'goal' | 'occupation'; label: string }> = [
  { key: 'phone', label: 'Telefon' },
  { key: 'email', label: 'E-mail' },
  { key: 'goal', label: 'Sen nebo životní cíl' },
  { key: 'occupation', label: 'Práce' },
];

const triStateOptions: Array<{ value: TriState; label: string }> = [
  { value: null, label: 'Nezáleží' },
  { value: true, label: 'Ano' },
  { value: false, label: 'Ne' },
];

const lastContactedOptions: Array<{ value: LastContacted; label: string }> = [
  { value: null, label: 'Nezáleží' },
  { value: '1m', label: 'Měsíc' },
  { value: '3m', label: '3 měsíce' },
  { value: '6m', label: '6 měsíců' },
  { value: '1y', label: 'Rok a více' },
];

function toggleId(list: number[], id: number) {
  const index = list.indexOf(id);
  if (index === -1) list.push(id);
  else list.splice(index, 1);
}

async function loadLookups() {
  const client = useSanctumClient();
  const headers = { Accept: 'application/json', 'Content-Type': 'application/json' };

  const [phaseResult, sourceResult] = await Promise.allSettled([
    client<LookupItem[]>('/api/admin/contact/phase', { method: 'GET', headers }),
    client<LookupItem[]>('/api/admin/contact/source', { method: 'GET', headers }),
  ]);

  if (phaseResult.status === 'fulfilled') phases.value = phaseResult.value;
  if (sourceResult.status === 'fulfilled') sources.value = sourceResult.value;
  if (phaseResult.status === 'rejected' || sourceResult.status === 'rejected') {
    $toast.show({
      summary: 'Chyba',
      detail: 'Nepodařilo se načíst fáze nebo zdroje. Zkuste to prosím později.',
      severity: 'error',
    });
  }
}

async function request(dryRun: boolean) {
  const client = useSanctumClient();
  return client<{ count: number }>('/api/admin/contact/list/' + props.listId + '/add-contacts', {
    method: 'POST',
    body: JSON.stringify({ ...filters.value, dry_run: dryRun }),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  });
}

async function loadCount() {
  countLoading.value = true;
  try {
    const response = await request(true);
    matchCount.value = response.count;
  } catch {
    matchCount.value = null;
  } finally {
    countLoading.value = false;
  }
}

const debouncedLoadCount = _.debounce(loadCount, 300);

watch(filters, () => debouncedLoadCount(), { deep: true });

watch(show, (value) => {
  if (!value) return;
  filters.value = defaultFilters();
  matchCount.value = null;
  if (!phases.value.length || !sources.value.length) loadLookups();
  loadCount();
});

async function save() {
  saving.value = true;
  try {
    const response = await request(false);
    $toast.show({
      summary: 'Hotovo',
      detail: response.count
        ? `Do seznamu bylo přidáno kontaktů: ${response.count}.`
        : 'Žádný nový kontakt neodpovídá filtrům.',
      severity: response.count ? 'success' : 'info',
    });
    show.value = false;
    emit('added', response.count);
  } catch {
    $toast.show({
      summary: 'Chyba',
      detail: 'Nepodařilo se přidat kontakty do seznamu. Zkuste to prosím znovu.',
      severity: 'error',
    });
  } finally {
    saving.value = false;
  }
}

const segmentClass = (active: boolean) =>
  active
    ? 'bg-white text-slate-900 shadow-sm ring-1 ring-slate-200'
    : 'text-slate-500 hover:text-slate-800';
</script>

<template>
  <TransitionRoot as="template" :show="show">
    <Dialog class="relative z-50" @close="show = false">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div
          class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
        >
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel
              class="relative w-full transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl shadow-slate-200/50 transition-all sm:my-8 sm:max-w-2xl sm:p-8"
            >
              <form @submit.prevent="save">
                <div class="mb-6 flex items-center gap-3">
                  <div
                    class="flex size-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
                  >
                    <UserPlusIcon class="size-6" />
                  </div>
                  <div>
                    <DialogTitle as="h3" class="text-xl font-bold tracking-tight text-slate-900">
                      Přidat kontakty
                    </DialogTitle>
                    <p class="text-sm text-slate-500">
                      Přidají se všechny vaše kontakty odpovídající filtrům, které v seznamu ještě
                      nejsou.
                    </p>
                  </div>
                </div>

                <div class="space-y-6">
                  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div v-for="field in triStateFields" :key="field.key">
                      <span class="mb-1.5 block text-sm font-medium text-slate-700">
                        {{ field.label }}
                      </span>
                      <div class="inline-flex w-full rounded-xl bg-slate-100 p-1" role="radiogroup">
                        <button
                          v-for="option in triStateOptions"
                          :key="String(option.value)"
                          type="button"
                          role="radio"
                          :aria-checked="filters[field.key] === option.value"
                          class="flex-1 rounded-lg px-3 py-1.5 text-sm font-medium transition-all"
                          :class="segmentClass(filters[field.key] === option.value)"
                          @click="filters[field.key] = option.value"
                        >
                          {{ option.label }}
                        </button>
                      </div>
                    </div>
                  </div>

                  <div>
                    <span class="mb-1.5 block text-sm font-medium text-slate-700">Fáze</span>
                    <div class="flex flex-wrap gap-2">
                      <button
                        v-for="phase in phases"
                        :key="phase.id"
                        type="button"
                        :aria-pressed="filters.phases.includes(phase.id)"
                        class="rounded-full transition-opacity"
                        :class="
                          filters.phases.includes(phase.id)
                            ? 'opacity-100'
                            : 'opacity-40 hover:opacity-70'
                        "
                        @click="toggleId(filters.phases, phase.id)"
                      >
                        <PropsBadge :color="phase.color">{{ phase.name }}</PropsBadge>
                      </button>
                      <span v-if="!phases.length" class="text-sm text-slate-400">
                        Nemáte vytvořené žádné fáze.
                      </span>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Bez výběru = všechny fáze.</p>
                  </div>

                  <div>
                    <span class="mb-1.5 block text-sm font-medium text-slate-700">Zdroj</span>
                    <div class="flex flex-wrap gap-2">
                      <button
                        v-for="source in sources"
                        :key="source.id"
                        type="button"
                        :aria-pressed="filters.sources.includes(source.id)"
                        class="rounded-full transition-opacity"
                        :class="
                          filters.sources.includes(source.id)
                            ? 'opacity-100'
                            : 'opacity-40 hover:opacity-70'
                        "
                        @click="toggleId(filters.sources, source.id)"
                      >
                        <PropsBadge :color="source.color">{{ source.name }}</PropsBadge>
                      </button>
                      <span v-if="!sources.length" class="text-sm text-slate-400">
                        Nemáte vytvořené žádné zdroje.
                      </span>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Bez výběru = všechny zdroje.</p>
                  </div>

                  <div>
                    <span class="mb-1.5 block text-sm font-medium text-slate-700">
                      Naposledy kontaktováno
                    </span>
                    <div
                      class="flex w-full flex-wrap gap-1 rounded-xl bg-slate-100 p-1 sm:flex-nowrap"
                      role="radiogroup"
                    >
                      <button
                        v-for="option in lastContactedOptions"
                        :key="String(option.value)"
                        type="button"
                        role="radio"
                        :aria-checked="filters.last_contacted === option.value"
                        class="flex-1 whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-medium transition-all"
                        :class="segmentClass(filters.last_contacted === option.value)"
                        @click="filters.last_contacted = option.value"
                      >
                        {{ option.label }}
                      </button>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">
                      Nekontaktováno déle než zvolené období (včetně kontaktů bez data).
                    </p>
                  </div>
                </div>

                <div
                  class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between"
                >
                  <p class="text-sm text-slate-500">
                    Odpovídá:
                    <span v-if="countLoading" class="text-slate-400">počítám…</span>
                    <span v-else class="font-bold text-indigo-600">{{ matchCount ?? '–' }}</span>
                    nových kontaktů
                  </p>
                  <div class="flex gap-3">
                    <BaseButton variant="secondary" type="button" @click="show = false">
                      Zrušit
                    </BaseButton>
                    <BaseButton
                      variant="primary"
                      type="submit"
                      :loading="saving"
                      :disabled="matchCount === 0"
                    >
                      Uložit
                    </BaseButton>
                  </div>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
