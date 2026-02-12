import { defineStore } from 'pinia';

export const useUserGroupStore = defineStore({
  id: 'userGroupStore',
  state: () => ({
    userGroups: [],
  }),
  actions: {
    async fetchUserGroups() {
      const client = useSanctumClient();
      await client<{
        id: number;
      }>('/api/admin/user/group', {
        method: 'GET',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
      }).then((response) => {
        this.userGroups = response;
      });
    },
  },
  getters: {
    userGroupsOptions() {
      return this.userGroups.map((userGroup: { name: string; label: string; id: number }) => ({
        label: userGroup.name,
        name: userGroup.name,
        value: userGroup.id,
      }));
    },
  },
});
