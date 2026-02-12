import { defineStore } from 'pinia';

export const useCurrencyStore = defineStore({
  id: 'currencyStore',
  state: () => ({
    currencies: [],
  }),
  actions: {
    async fetchCurrencies() {
      const client = useSanctumClient();
      await client<{
        id: number;
      }>('/api/admin/currency', {
        method: 'GET',
        // todo: order by name
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
      }).then((response) => {
        this.currencies = response;
      });
    },
  },
  getters: {
    currenciesOptions() {
      return this.currencies.map(
        (country: { name: string; label: string; id: number; code: string }) => ({
          label: country.name,
          name: country.name,
          value: country.id,
        }),
      );
    },
  },
});
