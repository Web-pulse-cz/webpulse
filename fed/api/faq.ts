import type { FaqCategory } from '~/types/FaqCategory';

export function useFaqApi(
  wrap: <T>(fn: (...args: any[]) => Promise<T>) => (...args: any[]) => Promise<T>,
) {
  const client = useSanctumClient();

  const categories = wrap(async (locale: string): Promise<FaqCategory[]> => {
    return await client(`/api/faq/category/${locale}`, { method: 'GET' });
  });

  const categoryDetail = wrap(async (id: number, locale: string): Promise<FaqCategory[]> => {
    return await client(`/api/faq/category/${id}/${locale}`, { method: 'GET' });
  });

  return { categories, categoryDetail };
}
