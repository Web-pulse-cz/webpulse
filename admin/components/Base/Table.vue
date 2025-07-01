<script setup lang="ts">
import { ref } from 'vue';
import {
  BoltIcon,
  MagnifyingGlassIcon,
  TrashIcon,
  ClipboardDocumentIcon,
  CheckIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';
import { ChevronDownIcon, ChevronUpIcon, StarIcon } from '@heroicons/vue/24/solid';
import { useUserGroupStore } from '~/stores/userGroupStore';

const toast = useToast();

const user = useSanctumUser();
const userGroupStore = useUserGroupStore();

const route = useRoute();
const router = useRouter();
const showDeleteDialog = ref(false);
const deleteDialogItem = ref(null);

defineProps({
  items: {
    type: Array,
    required: true,
    default: [] as [],
  },
  columns: {
    type: Array,
    required: true,
    default: [] as [],
  },
  enums: {
    type: Object,
    required: false,
    default: null,
  },
  actions: {
    type: Array,
    required: false,
    default: [] as [],
  },
  loading: {
    type: Boolean,
    required: false,
    default: false as boolean,
  },
  error: {
    type: Boolean,
    required: false,
    default: false as boolean,
  },
  singular: {
    type: String,
    required: false,
    default: '' as string,
  },
  plural: {
    type: String,
    required: false,
    default: '' as string,
  },
  query: {
    type: Object,
    required: false,
    default: null,
  },
  slug: {
    type: String,
    required: false,
    default: '' as string,
  },
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
        currentPermissionSlug.permissions.edit
      ) {
        return true;
      }
    }
  }
  return false;
}

function canDelete(slug: string) {
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
        currentPermissionSlug.permissions.delete == true
      ) {
        return true;
      }
    }
  }
  return false;
}

async function copyToClipboard(item, key) {
  console.log(item[key]);
  await navigator.clipboard
    .writeText(item[key])
    .then(() => {
      toast.add({
        title: 'Kopírováno',
        description: 'Zpráva byla úspěšně zkopírována do schránky.',
        color: 'green',
      });
    })
    .catch(() => {
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se zkopírovat zprávu do schránky.',
        color: 'red',
      });
    });
}

function redirect(itemId: number, action: object) {
  if (action.path && action.hash) {
    router.push(`${action.path}/${itemId}${action.hash}`);
  } else if (action.hash) {
    router.push(`${route.fullPath}/${itemId}${action.hash}`);
  } else if (action.path) {
    router.push(`${action.path}/${itemId}`);
  } else {
    router.push(`${route.fullPath}/${itemId}`);
  }
}

const emit = defineEmits(['delete-item', 'update-sort', 'update-page', 'open-dialog']);
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8">
    <div class="flow-root">
      <div class="-mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 pb-0 align-middle">
          <table class="min-w-full divide-y divide-grayLight">
            <thead>
              <tr>
                <th
                  v-for="(column, key) in columns"
                  :key="key"
                  scope="col"
                  :class="{
                    'hidden md:table-cell': column.hidden,
                    'py-2.5 pl-2 pr-1.5 text-left text-xs font-semibold text-grayDark lg:py-3.5 lg:pl-8 lg:pr-3 lg:text-sm': true,
                    'cursor-pointer': column.sortable,
                    [`w-[${column.width}px]`]: column.width,
                  }"
                  @click="column.sortable ? $emit('update-sort', column.key) : null"
                >
                  <div class="flex items-center">
                    <span>{{ column.name }}</span>
                    <ChevronDownIcon
                      v-if="
                        query &&
                        column.sortable &&
                        query.orderBy === column.key &&
                        query.orderWay === 'asc'
                      "
                      class="ml-2 size-3 text-primaryCustom lg:size-4"
                    />
                    <ChevronUpIcon
                      v-if="
                        query &&
                        column.sortable &&
                        query.orderBy === column.key &&
                        query.orderWay === 'desc'
                      "
                      class="ml-2 size-3 text-primaryCustom lg:size-4"
                    />
                  </div>
                </th>
                <th scope="col" class="relative w-[150px] py-3.5 pl-3 pr-4 sm:pr-6 lg:pr-8" />
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="(item, key) in items.data"
                v-if="!loading && !error && items.data && items.data.length"
                :key="key"
              >
                <td
                  v-for="(column, index) in columns"
                  :key="index"
                  scope="col"
                  :class="[
                    column.hidden ? 'hidden md:table-cell' : '',
                    `w-[${column.width}px] whitespace-nowrap py-2 pl-2 pr-1.5 text-xs font-medium text-grayCustom lg:py-4 lg:pl-8 lg:pr-3 lg:text-sm`,
                  ]"
                >
                  <span v-if="column.type === 'text' || column.type === 'number'">
                    {{ item[column.key] ?? '-' }}
                  </span>
                  <span v-if="column.type === 'percent'"> {{ item[column.key] ?? '-' }}% </span>
                  <PropsBadge v-else-if="column.type === 'badge'" :color="item[column.colorKey]">
                    {{ item[column.key] }}
                  </PropsBadge>
                  <span v-else-if="column.type === 'enum'">
                    {{ enums[column.key][item[column.key]] }}
                  </span>
                  <span v-else-if="column.type === 'date'">
                    {{ new Date(item[column.key]).toLocaleDateString() }}
                  </span>
                  <span v-else-if="column.type === 'datetime'">
                    {{ new Date(item[column.key]).toLocaleString() }}
                  </span>
                  <img
                    v-if="column.type === 'image'"
                    class="size-24 bg-gray-50"
                    :src="`http://api.martinhanzl.cz/content/images/${column.path}/${item[column.key]}`"
                    alt=""
                  />
                  <span v-else-if="column.type === 'stars'" class="flex gap-x-1.5">
                    <StarIcon
                      v-for="star in 5"
                      :key="star"
                      :class="`h-4 w-4 ${
                        star <= item[column.key] ? 'text-yellow-400' : 'text-grayLight'
                      }`"
                    />
                  </span>
                  <span v-else-if="column.type === 'status'">
                    <CheckIcon
                      v-if="item[column.key]"
                      class="size-3 cursor-pointer text-success lg:size-5"
                    />
                    <XMarkIcon v-else class="size-3 cursor-pointer text-danger lg:size-5" />
                  </span>
                  <NuxtLink
                    v-else-if="column.type === 'link'"
                    :to="item[column.key]"
                    :target="column.target"
                    class="text-primaryLight"
                  >
                    {{ item[column.key] }}
                  </NuxtLink>
                </td>
                <td
                  class="relative flex w-[150px] items-center justify-end whitespace-nowrap py-2 pl-1.5 pr-2 text-right text-xs font-medium sm:pr-6 lg:py-4 lg:pl-3 lg:pr-8 lg:text-sm"
                >
                  <span v-for="(action, index) in actions" :key="index">
                    <MagnifyingGlassIcon
                      v-if="
                        (action.type === 'edit' && canEdit(slug)) ||
                        (action.type === 'edit' && slug === '')
                      "
                      class="ml-2 size-3 cursor-pointer text-primaryCustom hover:text-primaryLight lg:ml-4 lg:size-5"
                      @click="redirect(item.id, action)"
                    />
                    <ClipboardDocumentIcon
                      v-if="action.type === 'copy'"
                      class="ml-2 size-4 cursor-pointer text-secondary hover:text-secondaryLight lg:ml-4 lg:size-5"
                      @click="copyToClipboard(item, action.key)"
                    />
                    <BoltIcon
                      v-if="
                        (action.type === 'edit-dialog' && canEdit(slug)) ||
                        (action.type === 'edit-dialog' && slug === '')
                      "
                      class="ml-2 size-4 cursor-pointer text-warning hover:text-warningLight lg:ml-4 lg:size-5"
                      @click="emit('open-dialog', item)"
                    />
                    <TrashIcon
                      v-if="
                        (action.type === 'delete' && canDelete(slug)) ||
                        (action.type === 'delete' && slug === '')
                      "
                      class="ml-2 size-4 cursor-pointer text-danger hover:text-dangerLight lg:ml-4 lg:size-5"
                      @click="
                        showDeleteDialog = true;
                        deleteDialogItem = item;
                      "
                    />
                  </span>
                </td>
              </tr>
              <tr v-else-if="!loading && error">
                <td
                  :colspan="columns.length + 1"
                  class="relative whitespace-nowrap py-8 pl-3 pr-4 text-center text-xs font-semibold text-grayCustom sm:pr-6 lg:pr-8 lg:text-sm"
                >
                  {{ `${plural} se nepodařilo načíst` }}
                </td>
              </tr>
              <tr v-else-if="!loading && !error && items.data && items.data.length === 0">
                <td
                  :colspan="columns.length + 1"
                  class="relative whitespace-nowrap py-8 pl-3 pr-4 text-center text-xs font-semibold text-grayCustom sm:pr-6 lg:pr-8 lg:text-sm"
                >
                  {{ `Nemáte žádné ${plural.toLowerCase()}` }}
                </td>
              </tr>
              <tr v-else-if="loading">
                <td
                  :colspan="columns.length + 1"
                  class="relative whitespace-nowrap py-8 pl-3 pr-4 text-center text-xs font-semibold text-grayCustom sm:pr-6 lg:pr-8 lg:text-sm"
                >
                  {{ `${plural} se načítají` }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <BasePagination
          v-if="!loading && !error && items.data && items.data.length && query"
          :page="items.currentPage"
          :per-page="items.perPage"
          :total="items.total"
          :last-page="items.lastPage"
          @update-page="emit('update-page', $event)"
        />
        <BaseDialogDelete
          v-model:show="showDeleteDialog"
          v-model:item="deleteDialogItem"
          @delete-item="
            emit('delete-item', deleteDialogItem.id);
            showDeleteDialog = false;
          "
        />
      </div>
    </div>
  </div>
</template>
