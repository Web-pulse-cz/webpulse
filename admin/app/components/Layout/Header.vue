<script setup lang="ts">
import type { Component } from 'vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import {
  StarIcon,
  PrinterIcon,
  PlusIcon,
  MinusIcon,
  CheckIcon,
  ArrowLeftStartOnRectangleIcon,
  FunnelIcon,
  Cog6ToothIcon,
  EllipsisHorizontalIcon,
} from '@heroicons/vue/24/outline';

const permissions = usePermissions();

const user = useSanctumUser();
const route = useRoute();
// Odkazy rychlého přístupu mohly být uložené i s ?query nebo #hash
function stripQuery(link: string): string {
  return (link ?? '').split(/[?#]/)[0];
}

const router = useRouter();
const quickAccessDialogShow = ref(false);

const props = defineProps({
  title: {
    type: String,
    required: true as boolean,
  },
  description: {
    type: String,
    required: false as boolean,
    default: '',
  },
  breadcrumbs: {
    type: Array,
    required: true,
    default: [] as [],
  },
  actions: {
    type: Array,
    required: false,
    default: [] as [],
  },
  slug: {
    type: String,
    required: false,
    default: '' as string | null,
  },
  filters: {
    type: Array,
    required: false,
    default: [] as [],
  },
  filtersQuery: {
    type: Object,
    required: false,
    default: null,
  },
  inlineFilters: {
    type: Array,
    required: false,
    default: () => [],
  },
  modifyBottom: {
    type: Boolean,
    required: false,
    default: true as boolean,
  },
  // Probíhá ukládání → ukládací tlačítka mají spinner a jsou zakázaná
  saving: {
    type: Boolean,
    required: false,
    default: false,
  },
});

const emit = defineEmits([
  'save',
  'update-filters',
  'apply-filters',
  'add-dialog',
  'remove-dialog',
  'add-category',
  'filter-dialog',
  'open-cashflow-dialog',
  'open-dashboard-settings',
]);

const quickAccessItem = ref({
  id: null,
  name: props.title,
  link: route.path,
  target: null,
});

type Permission = 'save' | 'edit' | 'none';

interface HeaderAction {
  type: string;
  text?: string;
  [key: string]: unknown;
}

interface ActionConfig {
  label: string | null; // null = použije se action.text
  icon: Component;
  variant: 'primary' | 'secondary';
  permission: Permission;
  isSave?: boolean;
  // Na mobilu zůstává jako samostatné tlačítko (ostatní jdou do menu „…“)
  mobileVisible?: boolean;
  // Pořadí: 0 = běžné akce, 1 = Uložit a odejít, 2 = primární Uložit (vpravo)
  order: number;
  run: (action: HeaderAction) => void;
}

const saveAndLeave: ActionConfig = {
  label: 'Uložit a odejít',
  icon: ArrowLeftStartOnRectangleIcon,
  variant: 'secondary',
  permission: 'save',
  isSave: true,
  mobileVisible: true,
  order: 1,
  run: () => emit('save', true),
};

const ACTION_CONFIG: Record<string, ActionConfig> = {
  'save-and-leave': saveAndLeave,
  // @deprecated – alias pro 'save-and-leave'
  'save-and-stay': saveAndLeave,
  save: {
    label: 'Uložit',
    icon: CheckIcon,
    variant: 'primary',
    permission: 'save',
    isSave: true,
    mobileVisible: true,
    order: 2,
    run: () => emit('save', false),
  },
  add: {
    label: null,
    icon: PlusIcon,
    variant: 'primary',
    permission: 'edit',
    mobileVisible: true,
    order: 0,
    run: () => router.push(route.path + '/pridat'),
  },
  'add-dialog': {
    label: null,
    icon: PlusIcon,
    variant: 'primary',
    permission: 'edit',
    order: 0,
    run: () => emit('add-dialog'),
  },
  'remove-dialog': {
    label: null,
    icon: MinusIcon,
    variant: 'secondary',
    permission: 'edit',
    order: 0,
    run: () => emit('remove-dialog'),
  },
  'add-category': {
    label: null,
    icon: PlusIcon,
    variant: 'primary',
    permission: 'none',
    order: 0,
    run: () => emit('add-category'),
  },
  'add-cashflow': {
    label: null,
    icon: PlusIcon,
    variant: 'primary',
    permission: 'none',
    order: 0,
    run: () => emit('open-cashflow-dialog'),
  },
  'dashboard-settings': {
    label: null,
    icon: Cog6ToothIcon,
    variant: 'secondary',
    permission: 'none',
    order: 0,
    run: () => emit('open-dashboard-settings'),
  },
  'filter-dialog': {
    label: null,
    icon: FunnelIcon,
    variant: 'secondary',
    permission: 'none',
    order: 0,
    run: () => emit('filter-dialog'),
  },
  print: {
    label: 'Tisk',
    icon: PrinterIcon,
    variant: 'secondary',
    permission: 'none',
    order: 0,
    run: () => window.print(),
  },
};

function canEditBySite(slug: string) {
  return permissions.moduleBelongsToSite(slug);
}

function isAllowed(permission: Permission): boolean {
  if (permission === 'save') return canEditBySite(props.slug) || props.slug === '';
  if (permission === 'edit') return canEditBySite(props.slug);
  return true;
}

interface ResolvedAction {
  key: string;
  label: string;
  config: ActionConfig;
  action: HeaderAction;
}

const resolvedActions = computed<ResolvedAction[]>(() => {
  return (props.actions as HeaderAction[])
    .map((action, index) => ({ action, index, config: ACTION_CONFIG[action?.type] }))
    .filter(({ config }) => config && isAllowed(config.permission))
    .sort((a, b) => a.config.order - b.config.order || a.index - b.index)
    .map(({ action, index, config }) => ({
      key: `${action.type}-${index}`,
      label: config.label ?? action.text ?? '',
      config,
      action,
    }));
});

const mobileVisibleActions = computed(() =>
  resolvedActions.value.filter((item) => item.config.mobileVisible),
);
const mobileOverflowActions = computed(() =>
  resolvedActions.value.filter((item) => !item.config.mobileVisible),
);

const hasSaveAction = computed(() => resolvedActions.value.some((item) => item.config.isSave));

function runAction(item: ResolvedAction) {
  if (item.config.isSave && props.saving) return;
  item.config.run(item.action);
}

function onKeydown(event: KeyboardEvent) {
  if (!hasSaveAction.value) return;
  if (!(event.ctrlKey || event.metaKey) || event.altKey || event.shiftKey) return;
  if (event.key.toLowerCase() !== 's') return;
  event.preventDefault();
  if (props.saving) return;
  emit('save', false);
}

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));

const isInQuickAccess = computed(() => {
  if (user.value) {
    return user.value.quick_access.some((item) => stripQuery(item.link) === route.path);
  }
  return false;
});
function openQuickAccessDialog(searchForItem: boolean = false) {
  quickAccessDialogShow.value = true;
  if (searchForItem) {
    quickAccessItem.value = user.value.quick_access.find(
      (item) => stripQuery(item.link) === route.path,
    );
  }
}

const emitUpdateFilters = (query: Record<string, any>) => {
  emit('update-filters', query);
};
</script>

<template>
  <div
    :class="[
      modifyBottom ? 'rounded-2xl' : 'rounded-t-2xl',
      'no-print bg-white p-6 shadow-sm ring-1 ring-slate-200 transition-all duration-300 sm:p-8',
    ]"
  >
    <LayoutBreadcrumbs :pages="breadcrumbs" class="mb-6" />

    <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
      <div class="min-w-0 flex-1">
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 lg:text-3xl">
          {{ title }}
        </h1>
        <p v-if="description" class="mt-1 text-sm text-slate-500">
          {{ description }}
        </p>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          :aria-label="
            isInQuickAccess ? 'Odebrat z rychlého přístupu' : 'Přidat do rychlého přístupu'
          "
          :title="isInQuickAccess ? 'Odebrat z rychlého přístupu' : 'Přidat do rychlého přístupu'"
          :aria-pressed="isInQuickAccess ? 'true' : 'false'"
          :class="[
            isInQuickAccess
              ? 'bg-amber-50 ring-amber-200'
              : 'bg-white ring-slate-200 hover:bg-slate-50',
            'inline-flex items-center justify-center rounded-full p-2.5 shadow-sm ring-1 ring-inset transition-all duration-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600',
          ]"
          @click="openQuickAccessDialog(isInQuickAccess)"
        >
          <StarIcon
            :class="[
              isInQuickAccess ? 'fill-amber-400 text-amber-400' : 'text-slate-400',
              'size-5 lg:size-6',
            ]"
            aria-hidden="true"
          />
        </button>

        <div class="hidden lg:flex lg:items-center lg:gap-3">
          <BaseButton
            v-for="item in resolvedActions"
            :key="item.key"
            :variant="item.config.variant"
            size="lg"
            :loading="!!item.config.isSave && saving"
            @click="runAction(item)"
          >
            <component
              :is="item.config.icon"
              v-if="!(item.config.isSave && saving)"
              class="mr-2 size-4"
              aria-hidden="true"
            />
            {{ item.label }}
          </BaseButton>
        </div>
      </div>
    </div>

    <div v-if="resolvedActions.length" class="mt-6 flex flex-wrap items-center gap-3 lg:hidden">
      <Menu v-if="mobileOverflowActions.length" v-slot="{ open }" as="div" class="relative">
        <MenuButton
          aria-label="Další akce"
          :class="[
            open
              ? 'bg-slate-50 ring-indigo-500'
              : 'bg-white ring-slate-300 hover:bg-slate-50 hover:ring-slate-400',
            'inline-flex items-center justify-center rounded-xl px-3 py-2 shadow-sm ring-1 ring-inset transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500',
          ]"
        >
          <EllipsisHorizontalIcon
            :class="[open ? 'text-indigo-600' : 'text-slate-500', 'size-5 transition-colors']"
            aria-hidden="true"
          />
        </MenuButton>
        <transition
          enter-active-class="transition duration-100 ease-out"
          enter-from-class="transform scale-95 opacity-0"
          enter-to-class="transform scale-100 opacity-100"
          leave-active-class="transition duration-75 ease-in"
          leave-from-class="transform scale-100 opacity-100"
          leave-to-class="transform scale-95 opacity-0"
        >
          <MenuItems
            class="absolute left-0 z-50 mt-2 w-60 origin-top-left overflow-hidden rounded-2xl bg-white p-1.5 shadow-xl shadow-slate-200/50 ring-1 ring-slate-200 focus:outline-none"
          >
            <MenuItem v-for="item in mobileOverflowActions" :key="item.key" v-slot="{ active }">
              <button
                type="button"
                :class="[
                  active ? 'bg-slate-50 text-indigo-600' : 'text-slate-700',
                  'group flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors duration-150',
                ]"
                @click="runAction(item)"
              >
                <component
                  :is="item.config.icon"
                  :class="[active ? 'text-indigo-600' : 'text-slate-400', 'size-4']"
                  aria-hidden="true"
                />
                {{ item.label }}
              </button>
            </MenuItem>
          </MenuItems>
        </transition>
      </Menu>

      <BaseButton
        v-for="item in mobileVisibleActions"
        :key="item.key"
        :variant="item.config.variant"
        size="md"
        :loading="!!item.config.isSave && saving"
        @click="runAction(item)"
      >
        <component
          :is="item.config.icon"
          v-if="!(item.config.isSave && saving)"
          class="mr-1.5 size-4"
          aria-hidden="true"
        />
        {{ item.label }}
      </BaseButton>
    </div>

    <div v-if="inlineFilters && inlineFilters.length" class="mt-8">
      <div class="h-px w-full bg-slate-100" />
      <div class="mt-6 flex flex-wrap items-end gap-3">
        <div v-for="(filter, key) in inlineFilters" :key="key">
          <label v-if="filter.label" class="label-caps mb-1 block text-slate-400">{{
            filter.label
          }}</label>
          <input
            v-if="filter.type === 'date' || filter.type === 'text'"
            :type="filter.type"
            :value="filter.modelValue"
            :placeholder="filter.placeholder || ''"
            class="block rounded-xl border-0 bg-white py-2 pl-3 pr-8 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:ring-slate-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
            :class="filter.class || 'w-40'"
            @input="
              $emit('apply-filters', {
                key: filter.key,
                value: ($event.target as HTMLInputElement).value,
              })
            "
          />
          <select
            v-else-if="filter.type === 'select'"
            :value="filter.modelValue"
            class="block rounded-xl border-0 bg-white py-2 pl-3 pr-8 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:ring-slate-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
            :class="filter.class || 'w-40'"
            @change="
              $emit('apply-filters', {
                key: filter.key,
                value: ($event.target as HTMLSelectElement).value,
              })
            "
          >
            <option v-for="opt in filter.options" :key="opt.value" :value="opt.value">
              {{ opt.name }}
            </option>
          </select>
        </div>
        <BaseButton
          variant="primary"
          size="md"
          type="button"
          @click="$emit('apply-filters', { key: '_apply' })"
        >
          <FunnelIcon class="mr-1.5 size-4" />
          Filtrovat
        </BaseButton>
      </div>
    </div>

    <div v-if="filters && filters.length" class="mt-8">
      <div class="h-px w-full bg-slate-100" />
      <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8">
        <ContactFilterDropdown
          v-for="(filter, key) in filters"
          :key="key"
          :title="filter.title"
          :data="filter.data"
          :multiple="filter.multiple"
          :type="filter.type"
          :slug="filter.slug"
          :filters-query="filtersQuery"
          @update-filters="emitUpdateFilters"
        />
      </div>
    </div>

    <QuickAccessDialog v-model:show="quickAccessDialogShow" v-model:form="quickAccessItem" />
  </div>
</template>
