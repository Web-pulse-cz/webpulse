<script setup lang="ts">
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue';
import { ChevronDownIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
  title: {
    type: String,
    required: true,
    default: '' as string,
  },
  data: {
    type: Array,
    required: true,
    default: [] as [],
  },
  multiple: {
    type: Array,
    required: true,
    default: [] as [],
  },
  slug: {
    type: String,
    required: true,
    default: '' as string,
  },
  type: {
    type: String,
    required: true,
    default: '' as string,
  },
  filtersQuery: {
    type: Object,
    required: false,
    default: {} as {},
  },
});

const emit = defineEmits(['update-filters']);

function addRemoveToFiltersQuery(slug: string, value: number) {
  if (!props.filtersQuery.filters) {
    props.filtersQuery.filters = {};
  }

  if (!props.filtersQuery.filters[slug]) {
    props.filtersQuery.filters[slug] = [];
  }

  const index = props.filtersQuery.filters[slug].indexOf(value);
  if (index > -1) {
    props.filtersQuery.filters[slug].splice(index, 1);
  } else {
    props.filtersQuery.filters[slug].push(value);
  }

  if (props.filtersQuery.filters[slug].length === 0) {
    delete props.filtersQuery.filters[slug];
  }

  // Save to session storage
  sessionStorage.setItem('filtersQuery', JSON.stringify(props.filtersQuery));

  emit('update-filters', props.filtersQuery);
}
</script>

<template>
  <div class="w-full">
    <Popover v-slot="{ open }" class="relative">
      <PopoverButton
        class="group inline-flex w-full items-center justify-between rounded-md bg-white px-2 py-1.5 text-xs text-grayDark shadow-sm ring-1 ring-inset ring-grayLight hover:bg-gray-50 focus-visible:outline-grayLight lg:px-3.5 lg:py-2.5 lg:text-sm"
      >
        <span>{{ title }}</span>
        <ChevronDownIcon
          class="-mr-1 ml-2 h-5 w-5 text-primaryCustom hover:text-primaryLight"
          aria-hidden="true"
        />
      </PopoverButton>

      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-1 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-1 opacity-0"
      >
        <PopoverPanel
          class="absolute left-80 z-10 mt-3 w-screen max-w-sm -translate-x-1/2 transform px-4 sm:px-0 lg:max-w-3xl"
        >
          <div class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black/5">
            <div class="relative grid gap-4 bg-white p-7 lg:grid-cols-3">
              <div v-for="(filterItem, key) in data" :key="key">
                <BaseFormCheckbox
                  v-if="multiple === true"
                  :name="filterItem.name"
                  :label="filterItem.name"
                  :model="filterItem.id"
                  :type="'badge'"
                  :color="filterItem.color"
                  :checked="
                    filtersQuery &&
                    filtersQuery.filters &&
                    filtersQuery.filters[slug] &&
                    filtersQuery.filters[slug].includes(filterItem.id)
                  "
                  @change="addRemoveToFiltersQuery(slug, filterItem.id)"
                />
              </div>
            </div>
          </div>
        </PopoverPanel>
      </transition>
    </Popover>
  </div>
</template>
