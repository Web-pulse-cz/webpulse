<script setup lang="ts">
import {
  StarIcon,
  PrinterIcon,
  ServerIcon,
  PlusIcon,
  CheckIcon,
  ArrowLeftStartOnRectangleIcon,
  FunnelIcon,
} from '@heroicons/vue/24/outline';
import { inject, ref } from 'vue';
import { useUserGroupStore } from '~/../stores/userGroupStore';

const userGroupStore = useUserGroupStore();

const user = useSanctumUser();
const route = useRoute();
const router = useRouter();
const quickAccessDialogShow = ref(false);

const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const props = defineProps({
  title: {
    type: String,
    required: true as boolean,
  },
  description: {
    type: String,
    required: false as boolean,
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
  modifyBottom: {
    type: Boolean,
    required: false,
    default: true as boolean,
  },
});

const emit = defineEmits([
  'save',
  'update-filters',
  'add-dialog',
  'filter-dialog',
  'open-cashflow-dialog',
]);
const quickAccessItem = ref({
  id: null,
  name: props.title,
  link: route.fullPath,
  target: null,
});

function canEdit(slug: string) {
  if (user && user.value && user.value.user_group_id && userGroupStore.userGroups) {
    const userGroup = userGroupStore.userGroups.find(
      (group) => group.id === user.value.user_group_id,
    );
    if (userGroup && userGroup.permissions) {
      const currentPermissionSlug = userGroup.permissions.find(
        (permission) => permission.slug === slug,
      );
      if (
        currentPermissionSlug &&
        currentPermissionSlug.slug === slug &&
        currentPermissionSlug.permissions.edit == true
      ) {
        return true;
      }
    }
  }
  return false;
}

function canEditBySite(slug: string) {
  if (user && user.value && user.value.sites) {
    const currentSite = user.value.sites.find((site) => site.hash === selectedSiteHash.value);
    if (
      currentSite &&
      currentSite.settings &&
      currentSite.settings.enabled_modules &&
      currentSite.is_active
    ) {
      const currentModuleSlug = currentSite.settings.enabled_modules.find(
        (module) => module === slug,
      );
      if (currentModuleSlug) {
        return true;
      }
    }
  }
  return false;
}

const isInQuickAccess = computed(() => {
  if (user.value) {
    return user.value.quick_access.some((item) => item.link === route.fullPath);
  }
  return false;
});
function openQuickAccessDialog(searchForItem: boolean = false) {
  quickAccessDialogShow.value = true;
  if (searchForItem) {
    quickAccessItem.value = user.value.quick_access.find((item) => item.link === route.fullPath);
  }
}

function print() {
  window.print();
}

const emitUpdateFilters = () => {
  emit('update-filters', props.filtersQuery);
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
        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 lg:text-3xl">
          {{ title }}
        </h2>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          :class="[
            isInQuickAccess
              ? 'bg-amber-50 ring-amber-200'
              : 'bg-white ring-slate-200 hover:bg-slate-50',
            'inline-flex items-center justify-center rounded-full p-2.5 shadow-sm ring-1 ring-inset transition-all duration-200',
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
          <template v-for="(action, key) in actions" :key="key">
            <BaseButton
              v-if="action.type === 'save-and-stay' && (canEditBySite(slug) || slug === '')"
              variant="secondary"
              size="lg"
              @click="emit('save', true)"
            >
              <ArrowLeftStartOnRectangleIcon class="mr-2 size-4" />
              Uložit a odejít
            </BaseButton>

            <BaseButton
              v-if="action.type === 'save' && (canEditBySite(slug) || slug === '')"
              variant="primary"
              size="lg"
              @click="emit('save', false)"
            >
              <CheckIcon class="mr-2 size-4" />
              Uložit
            </BaseButton>

            <BaseButton
              v-if="action.type === 'add' && canEditBySite(slug)"
              variant="primary"
              size="lg"
              @click="router.push(route.fullPath + '/pridat')"
            >
              <PlusIcon class="mr-2 size-4" />
              {{ action.text }}
            </BaseButton>

            <BaseButton
              v-if="action.type === 'add-dialog' && canEditBySite(slug)"
              variant="primary"
              size="lg"
              @click="emit('add-dialog')"
            >
              <PlusIcon class="mr-2 size-4" />
              {{ action.text }}
            </BaseButton>

            <BaseButton
              v-if="action.type === 'add-cashflow'"
              variant="primary"
              size="lg"
              @click="emit('open-cashflow-dialog')"
            >
              <PlusIcon class="mr-2 size-4" />
              {{ action.text }}
            </BaseButton>

            <BaseButton
              v-if="action.type === 'filter-dialog'"
              variant="secondary"
              size="lg"
              @click="emit('filter-dialog')"
            >
              <FunnelIcon class="mr-2 size-4" />
              {{ action.text }}
            </BaseButton>

            <BaseButton v-if="action.type === 'print'" variant="secondary" size="lg" @click="print">
              <PrinterIcon class="mr-2 size-4" />
              Tisk
            </BaseButton>
          </template>
        </div>
      </div>
    </div>

    <div v-if="actions && actions.length" class="mt-6 flex flex-wrap gap-3 lg:hidden">
      <template v-for="(action, key) in actions" :key="key">
        <BaseButton
          v-if="action.type === 'save-and-stay' && (canEditBySite(slug) || slug === '')"
          variant="secondary"
          size="md"
          @click="emit('save', true)"
        >
          <ArrowLeftStartOnRectangleIcon class="mr-1.5 size-4" />
          Uložit a odejít
        </BaseButton>
        <BaseButton
          v-if="action.type === 'save' && (canEditBySite(slug) || slug === '')"
          variant="primary"
          size="md"
          @click="emit('save', false)"
        >
          <CheckIcon class="mr-1.5 size-4" />
          Uložit
        </BaseButton>
        <BaseButton
          v-if="action.type === 'add' && canEditBySite(slug)"
          variant="primary"
          size="md"
          @click="router.push(route.fullPath + '/pridat')"
        >
          <PlusIcon class="mr-1.5 size-4" />
          {{ action.text }}
        </BaseButton>
      </template>
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
