import { defineStore } from 'pinia';

export const useTaxRateStore = defineStore({
  id: 'taxRateStore',
  state: () => ({
    taxRates: [],
  }),
  actions: {
    async fetchTaxRates() {
      const client = useSanctumClient();
      await client<{
        id: number;
      }>('/api/admin/tax-rate', {
        method: 'GET',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
      }).then((response) => {
        this.taxRates = response;
      });
    },
  },
  getters: {
    taxRateOptions() {
      return this.taxRates.map(
        (taxRate: { name: string; label: string; id: number; code: string }) => ({
          label: taxRate.name,
          name: taxRate.name,
          value: taxRate.id,
        }),
      );
    },
  },
});
