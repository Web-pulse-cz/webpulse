import { defineStore } from 'pinia';

export const useActivityStore = defineStore({
  id: 'activityStore',
  state: () => ({
    activities: [],
  }),
  actions: {
    async fetchActivities(is_business: boolean = false, is_personal: boolean = false) {
      const client = useSanctumClient();
      await client<{
        id: number;
      }>('/api/admin/activity', {
        method: 'GET',
        query: {
          is_business: is_business,
          is_personal: is_personal,
        },
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
      }).then((response) => {
        this.activities = response;
      });
    },
  },
  getters: {
    activitiesOptions() {
      return this.activities.map(
        (activity: { name: string; label: string; id: number; color: string | null }) => ({
          label: activity.name,
          name: activity.name,
          color: activity.color,
          value: activity.id,
        }),
      );
    },
  },
});
