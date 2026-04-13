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
        :class="[
          open
            ? 'text-slate-900 ring-indigo-500'
            : 'text-slate-700 ring-slate-300 hover:bg-slate-50 hover:ring-slate-400',
          'group inline-flex w-full items-center justify-between rounded-xl bg-white px-4 py-2.5 text-sm font-medium shadow-sm ring-1 ring-inset transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500',
        ]"
      >
        <span>{{ title }}</span>
        <ChevronDownIcon
          :class="[
            open ? 'rotate-180 text-indigo-600' : 'text-slate-400 group-hover:text-slate-500',
            'ml-2 h-5 w-5 shrink-0 transition-transform duration-200',
          ]"
          aria-hidden="true"
        />
      </PopoverButton>

      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
      >
        <PopoverPanel
          class="absolute left-0 z-50 mt-2 w-screen max-w-[calc(100vw-2rem)] transform sm:max-w-sm lg:max-w-3xl"
        >
          <div
            class="overflow-hidden rounded-2xl bg-white shadow-xl shadow-slate-200/50 ring-1 ring-slate-200"
          >
            <div class="relative grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 sm:p-8 lg:grid-cols-3">
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
