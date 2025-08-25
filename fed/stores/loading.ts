import { defineStore } from 'pinia';

export const useLoadingStore = defineStore('loading', {
  state: () => ({
    activeRequests: 0,
  }),
  getters: {
    isLoading: (state) => state.activeRequests > 0,
  },
  actions: {
    start() {
      this.activeRequests++;
    },
    stop() {
      if (this.activeRequests > 0) this.activeRequests--;
    },
  },
});
