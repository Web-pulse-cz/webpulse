import { useGlobalApi } from '~/../api/global';
import { useQuizApi } from '~/../api/quiz';
import { useLoadingStore } from '~/../stores/loading';

export function useApi() {
  const loading = useLoadingStore();

  const wrapWithLoading = <T>(fn: (...args: any[]) => Promise<T>) => {
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

  const wrapSilent = <T>(fn: (...args: any[]) => Promise<T>) => {
    return async (...args: any[]): Promise<T> => {
      try {
        return await fn(...args);
      } catch (error) {
        console.error('API error:', error);
        throw error;
      }
    };
  };

  const global = useGlobalApi(wrapSilent);
  const quiz = useQuizApi(wrapWithLoading);

  return {
    global,
    quiz,
  };
}
