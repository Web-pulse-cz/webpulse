import { useGlobalApi } from '~/api/global';
import { useServiceApi } from '~/api/service';
import { useBlogApi } from '~/api/blog';
import { usePageApi } from '~/api/page';
import { useLogoApi } from '~/api/logo';
import { useFaqApi } from '~/api/faq';
import { useQuizApi } from '~/api/quiz';

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
  const service = useServiceApi(wrapWithLoading);
  const blog = useBlogApi(wrapWithLoading);
  const page = usePageApi(wrapWithLoading);
  const logo = useLogoApi(wrapSilent);
  const faq = useFaqApi(wrapWithLoading);
  const quiz = useQuizApi(wrapWithLoading);

  return {
    global,
    service,
    blog,
    page,
    logo,
    faq,
    quiz,
  };
}
