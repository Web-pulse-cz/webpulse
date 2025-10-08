import { storeToRefs } from 'pinia';
import { useToastStore } from '~/../stores/toast';
import type { ToastOptions } from '~/../types/toast';

export function useToast() {
  const store = useToastStore();
  const { items, position } = storeToRefs(store);

  const show = (o: ToastOptions) => store.show(o);
  const info = (summary: string, detail = '', duration?: number) =>
    store.info(summary, detail, duration);
  const success = (summary: string, detail = '', duration?: number) =>
    store.success(summary, detail, duration);
  const warning = (summary: string, detail = '', duration?: number) =>
    store.warning(summary, detail, duration);
  const error = (summary: string, detail = '', duration?: number) =>
    store.error(summary, detail, duration);
  const remove = (id: string) => store.remove(id);
  const clear = () => store.clear();
  const pause = (id: string) => store.pause(id);
  const resume = (id: string) => store.resume(id);

  const colorClasses = store.colorClasses;

  return {
    items,
    position,
    show,
    info,
    success,
    warning,
    error,
    remove,
    clear,
    pause,
    resume,
    colorClasses,
  };
}
