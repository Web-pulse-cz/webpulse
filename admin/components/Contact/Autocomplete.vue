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
const toast = useToast();

const model = ref<number | null>(null);

const error = ref(false);
const loading = ref(false);

const contacts = ref([{ id: null, firstname: 'Osobní', lastname: 'kontakt' }]);

const query = ref('');

const emit = defineEmits(['update:modelValue']);

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
      contacts.value.unshift({ id: null, firstname: 'Osobní', lastname: 'kontakt' });
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst kontakty. Zkuste to prosím později.',
        color: 'red',
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

watch(model, (newValue) => {
  emit('update:modelValue', newValue);
});
</script>

<template>
  <div class="w-full">
    <Combobox v-model="model">
      <div class="relative mt-1">
        <div class="w-full cursor-default overflow-hidden">
          <label :for="name" class="block text-xs font-medium text-grayCustom lg:text-sm/6">{{
            label
          }}</label>
          <ComboboxInput
            class="mt-1.5 block w-full rounded-md border-0 py-1.5 text-xs text-grayDark shadow-sm ring-1 ring-inset ring-grayLight placeholder:text-grayLight focus:ring-1 focus:ring-inset focus:ring-primaryLight lg:py-2 lg:text-sm/6"
            :name="name"
            :display-value="
              () =>
                selectedContact ? `${selectedContact.firstname} ${selectedContact.lastname}` : ''
            "
            @change="query = $event.target.value"
          />
          <ComboboxButton class="absolute inset-y-0 right-0 top-8 flex items-center pr-2">
            <ChevronUpDownIcon class="h-3 w-3 text-grayLight lg:h-5 lg:w-5" aria-hidden="true" />
          </ComboboxButton>
        </div>
        <TransitionRoot
          leave="transition ease-in duration-100"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <ComboboxOptions
            class="absolute mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-xs shadow-lg ring-1 ring-black/5 focus:outline-none lg:text-sm"
          >
            <div
              v-if="contacts.length === 0"
              class="relative cursor-default select-none px-2 py-1.5 text-gray-700 lg:px-4 lg:py-2"
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
                class="relative cursor-default select-none py-1.5 pl-5 pr-4 lg:py-2 lg:pl-10"
                :class="{
                  'bg-secondary text-grayDark': active,
                  'text-grayDark': !active,
                }"
              >
                <span
                  class="block truncate"
                  :class="{ 'font-medium': selected, 'font-normal': !selected }"
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
