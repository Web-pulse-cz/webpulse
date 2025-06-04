import { defineNuxtPlugin } from '#app';

export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.provide('addToast', (msg: string) => {
    const toastEvent = new CustomEvent('add-toast', { detail: msg });
    window.dispatchEvent(toastEvent);
  });
});
