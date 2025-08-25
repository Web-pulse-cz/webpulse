import type { Page } from '~/types/page';

export function usePageApi(
  wrap: <T>(fn: (...args: any[]) => Promise<T>) => (...args: any[]) => Promise<T>,
) {
  const client = useSanctumClient();

  const page = wrap(async (id: number, locale: string): Promise<Page | null> => {
    return await client(`/api/page/${id}/${locale}}`, { method: 'GET' });
  });

  return { page };
}
