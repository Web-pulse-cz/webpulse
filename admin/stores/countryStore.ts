import { defineStore } from 'pinia';

export const useCountryStore = defineStore({
  id: 'countryStore',
  state: () => ({
    countries: [],
  }),
  actions: {
    async fetchCountries() {
      const client = useSanctumClient();
      await client<{
        id: number;
      }>('/api/admin/country', {
        method: 'GET',
        // todo: order by name
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
      }).then((response) => {
        this.countries = response;
      });
    },
  },
  getters: {
    countriesOptions() {
      return this.countries.map(
        (country: { name: string; label: string; id: number; code: string }) => ({
          label: country.name,
          name: country.name,
          value: country.id,
        }),
      );
    },
  },
});
