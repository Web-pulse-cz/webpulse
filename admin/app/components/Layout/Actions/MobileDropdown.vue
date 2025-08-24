<script setup lang="ts">
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { EllipsisHorizontalIcon } from '@heroicons/vue/24/outline';
import { useUserGroupStore } from '~/../stores/userGroupStore';

const userGroupStore = useUserGroupStore();

const user = useSanctumUser();
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
const emit = defineEmits(['save', 'add-dialog', 'filter-dialog']);
</script>

<template>
  <div class="ml-4 text-right">
    <Menu as="div" class="relative inline-block text-left">
      <div>
        <MenuButton
          class="inline-flex rounded-md bg-white px-3.5 py-2.5 text-sm text-grayDark shadow-sm ring-1 ring-inset ring-grayLight hover:bg-gray-50 focus-visible:outline-grayLight"
        >
          <EllipsisHorizontalIcon
            class="-mr-1 h-5 w-5 text-primaryCustom hover:text-primaryLight"
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
          class="absolute right-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
        >
          <div class="px-1 py-1">
            <pre>{{ actions }}</pre>
            <MenuItems>
              <MenuItem v-for="(action, index) in actions" :key="index">
                <a
                  class="text-primaryCustom"
                  href="#"
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
                  Documentation
                </a>
              </MenuItem>
            </MenuItems>
          </div>
        </MenuItems>
      </transition>
    </Menu>
  </div>
</template>
