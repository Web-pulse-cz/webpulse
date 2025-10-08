import { defineNuxtPlugin } from '#app';
import { useToastStore } from '~/../stores/toast';
import type { ToastOptions } from '~/../types/toast';

export default defineNuxtPlugin(() => {
  const store = useToastStore();

  const api = {
    show: (o: ToastOptions) => store.show(o),
    success: (t: string, d = '', ms?: number) => store.success(t, d, ms),
    info: (t: string, d = '', ms?: number) => store.info(t, d, ms),
    warning: (t: string, d = '', ms?: number) => store.warning(t, d, ms),
    error: (t: string, d = '', ms?: number) => store.error(t, d, ms),
    remove: (id: string) => store.remove(id),
    clear: () => store.clear(),
  };

  return {
    provide: {
      toast: api,
    },
  };
});
