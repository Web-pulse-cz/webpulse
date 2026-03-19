import { defineStore } from 'pinia';

export const useActivityStore = defineStore({
  id: 'activityStore',
  state: () => ({
    activities: [],
  }),
  actions: {
    async fetchActivities(
      only_business_activities: boolean = false,
      only_personal_activities: boolean = false,
    ) {
      const client = useSanctumClient();
      await client<{
        id: number;
      }>('/api/admin/activity', {
        method: 'GET',
        query: {
          only_business_activities: only_business_activities,
          only_personal_activities: only_personal_activities,
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
