<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import {
  Combobox,
  ComboboxInput,
  ComboboxButton,
  ComboboxOptions,
  ComboboxOption,
  TransitionRoot,
} from '@headlessui/vue';
import { ChevronUpDownIcon } from '@heroicons/vue/20/solid';
import { debounce } from 'lodash';

defineProps({
  label: {
    type: Number,
    required: true,
    default: '' as string | null,
  },
  name: {
    type: String,
    required: true,
    default: '' as string | null,
  },
});
const { $toast } = useNuxtApp();

const model = ref(null);

const error = ref(false);
const loading = ref(false);

const users = ref([{ id: null, firstname: '-', lastname: '-' }]);

const query = ref('');

const emit = defineEmits(['update:modelValue']);

async function loadItems() {
  if (query.value === '' || query.value.length < 3) {
    return;
  }

  loading.value = true;
  const client = useSanctumClient();

  await client<{ id: number; firstname: string; lastname: string }>('/api/admin/user', {
    method: 'GET',
    query: {
      search: query.value,
    },
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      users.value = response;
      users.value.unshift({ id: null, firstname: '-', lastname: '-' });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst uživatele. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}
const debouncedLoadItems = debounce(loadItems, 400);

watch(query, debouncedLoadItems);

const selectedUser = computed(() => {
  return users.value.find((user) => user.id === model.value) || { firstname: '', lastname: '' };
});

watch(model, (newValue) => {
  emit('update:modelValue', newValue);
});
</script>

<template>
  <div class="w-full">
    <Combobox v-model="model">
      <div class="relative">
        <label :for="name" class="mb-1.5 block text-sm font-medium text-slate-700">
          {{ label }}
        </label>

        <div class="relative w-full">
          <ComboboxInput
            class="block w-full rounded-xl border-0 bg-white py-2.5 pl-4 pr-10 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 transition-all duration-200 placeholder:text-slate-400 hover:ring-slate-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
            :name="name"
            :display-value="
              () => (selectedUser ? `${selectedUser.firstname} ${selectedUser.lastname}` : '')
            "
            @change="query = $event.target.value"
          />
          <ComboboxButton
            class="absolute inset-y-0 right-0 flex items-center pr-3 focus:outline-none"
          >
            <ChevronUpDownIcon class="size-5 text-slate-400" aria-hidden="true" />
          </ComboboxButton>
        </div>

        <TransitionRoot
          leave="transition ease-in duration-100"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <ComboboxOptions
            class="absolute z-50 mt-2 max-h-60 w-full overflow-auto rounded-2xl bg-white p-1.5 text-base shadow-xl shadow-slate-200/50 ring-1 ring-slate-200 focus:outline-none sm:text-sm"
          >
            <div
              v-if="users.length === 0"
              class="relative cursor-default select-none px-4 py-3 text-center text-sm font-medium text-slate-500"
            >
              Žádní uživatelé.
            </div>

            <ComboboxOption
              v-for="(user, index) in users"
              :key="index"
              v-slot="{ selected, active }"
              as="template"
              :value="user"
            >
              <li
                :class="[
                  active ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700',
                  'relative cursor-pointer select-none rounded-xl py-2.5 pl-4 pr-4 transition-colors duration-150',
                ]"
              >
                <span
                  :class="[
                    selected ? 'font-bold text-indigo-700' : 'font-medium',
                    'block truncate',
                  ]"
                >
                  {{ `${user.firstname} ${user.lastname}` }}
                </span>
              </li>
            </ComboboxOption>
          </ComboboxOptions>
        </TransitionRoot>
      </div>
    </Combobox>
  </div>
</template>
