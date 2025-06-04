import { useServiceApi } from '~/api/service';

export function useApi() {
  const wrap = <T>(fn: (...args: any[]) => Promise<T>) => {
    return async (...args: any[]): Promise<T> => {
      try {
        const result = await fn(...args);
        return result;
      } catch (error) {
        console.error('API error:', error);
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
