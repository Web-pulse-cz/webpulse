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

const model = defineModel({
  type: Number,
  required: true,
});

const props = defineProps({
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
  contactOptions: {
    type: Object,
    required: false,
  },
});
const { $toast } = useNuxtApp();

const error = ref(false);
const loading = ref(false);

const contacts = ref([
  {
    id: props.contactOptions.id,
    firstname: props.contactOptions.firstname,
    lastname: props.contactOptions.lastname,
  },
]);

const query = ref('');

async function loadItems() {
  if (query.value === '' || query.value.length < 3) {
    return;
  }

  loading.value = true;
  const client = useSanctumClient();

  await client<{ id: number; firstname: string; lastname: string }>('/api/admin/contact', {
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
      contacts.value = response;
      contacts.value.unshift({
        id: props.contactOptions.id,
        firstname: props.contactOptions.firstname,
        lastname: props.contactOptions.lastname,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst kontakty. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}
const debouncedLoadItems = debounce(loadItems, 400);

watch(query, debouncedLoadItems);

const selectedContact = computed(() => {
  return (
    contacts.value.find((contact) => contact.id === model.value) || { firstname: '', lastname: '' }
  );
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
              () =>
                selectedContact ? `${selectedContact.firstname} ${selectedContact.lastname}` : ''
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
              v-if="contacts.length === 0"
              class="relative cursor-default select-none px-4 py-3 text-center text-sm font-medium text-slate-500"
            >
              Žádné kontakty.
            </div>

            <ComboboxOption
              v-for="(contact, index) in contacts"
              :key="index"
              v-slot="{ selected, active }"
              as="template"
              :value="contact.id"
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
                  {{ `${contact.firstname} ${contact.lastname}` }}
                </span>
              </li>
            </ComboboxOption>
          </ComboboxOptions>
        </TransitionRoot>
      </div>
    </Combobox>
  </div>
</template>
