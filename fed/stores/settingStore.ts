import { defineStore } from 'pinia';

export const useSettingStore = defineStore({
  id: 'settingStore',
  state: () => ({
    settings: [],
  }),
  actions: {
    async fetchSettings(locale: string) {
      const client = useSanctumClient();
      await client<{
        id: number;
      }>('/api/setting/' + locale, {
        method: 'GET',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
      }).then((response) => {
        this.settings = response;
      });
    },
  },
  getters: {
    topMenu() {
      return this.settings.find((setting) => setting.type === 'topMenu');
    },
    bottomMenu() {
      return this.settings.find((setting) => setting.type === 'bottomMenu');
    },
  },
});
