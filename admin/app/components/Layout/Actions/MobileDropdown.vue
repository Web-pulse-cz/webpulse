<script setup lang="ts">
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { EllipsisHorizontalIcon } from '@heroicons/vue/24/outline';

const permissions = usePermissions();

const route = useRoute();
const router = useRouter();

defineProps({
  actions: {
    type: Array,
    required: true,
    default: [] as [],
  },
  slug: {
    type: String,
    required: false,
    default: '' as string | null,
  },
});

function canEdit(slug: string) {
  return permissions.canEdit(slug);
}
const emit = defineEmits(['save', 'add-dialog', 'filter-dialog']);
</script>

<template>
  <div class="ml-4 text-right">
    <Menu v-slot="{ open }" as="div" class="relative inline-block text-left">
      <div>
        <MenuButton
          :class="[
            open
              ? 'bg-slate-50 ring-indigo-500'
              : 'bg-white ring-slate-300 hover:bg-slate-50 hover:ring-slate-400',
            'inline-flex items-center justify-center rounded-xl px-3 py-2.5 shadow-sm ring-1 ring-inset transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500',
          ]"
        >
          <EllipsisHorizontalIcon
            :class="[open ? 'text-indigo-600' : 'text-slate-500', 'size-5 transition-colors']"
            aria-hidden="true"
          />
        </MenuButton>
      </div>

      <transition
        enter-active-class="transition duration-100 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-75 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <MenuItems
          class="absolute right-0 z-50 mt-2 w-56 origin-top-right overflow-hidden rounded-2xl bg-white p-1.5 shadow-xl shadow-slate-200/50 ring-1 ring-slate-200 focus:outline-none"
        >
          <div class="space-y-1">
            <MenuItem v-for="(action, index) in actions" :key="index" v-slot="{ active }">
              <button
                type="button"
                :class="[
                  active ? 'bg-slate-50 text-indigo-600' : 'text-slate-700',
                  'group flex w-full items-center rounded-xl px-3 py-2.5 text-sm font-medium transition-colors duration-150',
                ]"
                @click="
                  action.type === 'edit'
                    ? router.push(action.path)
                    : action.type === 'add-dialog'
                      ? emit('add-dialog')
                      : action.type === 'filter-dialog'
                        ? emit('filter-dialog')
                        : action.type === 'save'
                          ? emit('save')
                          : null
                "
              >
                {{ action.name || 'Provést akci' }}
              </button>
            </MenuItem>
          </div>
        </MenuItems>
      </transition>
    </Menu>
  </div>
</template>
