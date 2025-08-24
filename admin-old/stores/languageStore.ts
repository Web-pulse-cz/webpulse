import { defineStore } from 'pinia';

export const useLanguageStore = defineStore({
  id: 'languageStore',
  state: () => ({
    languages: [],
  }),
  actions: {
    async fetchLanguages() {
      const client = useSanctumClient();
      await client<{
        id: number;
      }>('/api/admin/language', {
        method: 'GET',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
      }).then((response) => {
        this.languages = response;
      });
    },
  },
  getters: {
    languageOptions() {
      return this.languages.map(
        (language: { name: string; label: string; id: number; code: string }) => ({
          label: language.name,
          name: language.name,
          value: language.code,
        }),
      );
    },
  },
});
