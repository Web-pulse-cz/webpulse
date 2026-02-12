import type { FaqCategory } from '~/types/FaqCategory';

export function useFaqApi(
  wrap: <T>(fn: (...args: any[]) => Promise<T>) => (...args: any[]) => Promise<T>,
) {
  const client = useSanctumClient();
  const runtimeConfig = useRuntimeConfig();

  const categories = wrap(async (locale: string): Promise<FaqCategory[]> => {
    return await client(`/api/faq/category/${locale}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Site-Hash': runtimeConfig.public.siteHash,
      },
    });
  });

  const categoryDetail = wrap(async (id: number, locale: string): Promise<FaqCategory[]> => {
    return await client(`/api/faq/category/${id}/${locale}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Site-Hash': runtimeConfig.public.siteHash,
      },
    });
  });

  return { categories, categoryDetail };
}
