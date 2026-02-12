import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import { defineNuxtPlugin } from '#app';

export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.vueApp.use(PrimeVue);
  nuxtApp.vueApp.use(ToastService);
});
