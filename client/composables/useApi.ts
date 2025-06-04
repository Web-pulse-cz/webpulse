import { useServiceApi } from '~/api/service';
import { useToastMessage } from '~/composables/useToastMessage';

export function useApi() {
  const { showSuccess, showError } = useToastMessage();

  const wrap = <T>(fn: (...args: any[]) => Promise<T>) => {
    return async (...args: any[]): Promise<T> => {
      try {
        showSuccess('Probíhá komunikace se serverem...');
        const result = await fn(...args);
        return result;
      } catch (error) {
        console.error('API error:', error);
        showError('Došlo k chybě při komunikaci se serverem.');
        throw error;
      }
    };
  };

  const serviceApi = useServiceApi();

  return {
    service: {
      services: wrap(serviceApi.services),
      service: wrap(serviceApi.service),
    },
  };
}
