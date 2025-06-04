import { useServiceApi } from '~/api/service';
import { useLoadingStore } from '~/stores/loading';

export function useApi() {
  const loading = useLoadingStore();

  const wrap = <T>(fn: (...args: any[]) => Promise<T>) => {
    return async (...args: any[]): Promise<T> => {
      try {
        loading.start();
        return await fn(...args);
      } catch (error) {
        console.error('API error:', error);
        throw error;
      } finally {
        loading.stop();
      }
    };
  };

  const service = useServiceApi(wrap);

  return { service };
}
