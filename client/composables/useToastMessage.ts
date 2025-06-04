import { inject } from 'vue';

export function useToastMessage() {
  const addToast = inject<(msg: string) => void>('addToast');

  const showSuccess = (msg: string) => addToast?.(msg);
  const showError = (msg: string) => addToast?.(`❌ ${msg}`);

  return { showSuccess, showError };
}
