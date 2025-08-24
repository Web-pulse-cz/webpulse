import { defineStore } from 'pinia';

export const useCashflowCategoryStore = defineStore({
  id: 'cashflowCategoryStore',
  state: () => ({
    categories: [],
  }),
  actions: {
    async fetchCategories() {
      const client = useSanctumClient();
      await client<{
        id: number;
      }>('/api/admin/cashflow/category', {
        method: 'GET',
        query: {
          only_categories: true,
        },
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
      }).then((response) => {
        this.categories = response;
      });
    },
  },
  getters: {
    categoriesOptions() {
      return this.categories.map((category: { name: string; label: string; id: number }) => ({
        label: category.name,
        name: category.name,
        value: category.id,
      }));
    },
  },
});
