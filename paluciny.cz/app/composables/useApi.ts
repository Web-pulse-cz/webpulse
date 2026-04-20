import { useGlobalApi } from '~/../api/global';
import { useServiceApi } from '~/../api/service';
import { useBlogApi } from '~/../api/blog';
import { usePageApi } from '~/../api/page';
import { useLogoApi } from '~/../api/logo';
import { useFaqApi } from '~/../api/faq';
import { useQuizApi } from '~/../api/quiz';
import { useNoveltyApi } from '~/../api/novelty';
import { useEventApi } from '~/../api/event';
import { useLoadingStore } from '~/../stores/loading';

export function useApi() {
  const loading = useLoadingStore();

  const wrapWithLoading = <T>(fn: (...args: unknown[]) => Promise<T>) => {
    return async (...args: unknown[]): Promise<T> => {
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

  const wrapSilent = <T>(fn: (...args: unknown[]) => Promise<T>) => {
    return async (...args: unknown[]): Promise<T> => {
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
  const novelty = useNoveltyApi(wrapWithLoading);
  const event = useEventApi(wrapWithLoading);

  return {
    global,
    service,
    blog,
    page,
    logo,
    faq,
    quiz,
    novelty,
    event,
  };
}
