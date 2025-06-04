import { useServiceApi } from '~/api/service';

export function useApi() {
  const wrap = <T>(fn: (...args: any[]) => Promise<T>) => {
    return async (...args: any[]): Promise<T> => {
      try {
        return await fn(...args);
      } catch (error) {
        console.error('API error:', error);
        throw error;
      }
    };
  };

  const service = useServiceApi();

  return {
    service,
  };
}
