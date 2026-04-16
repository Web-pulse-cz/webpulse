import type { Page } from '~/types/page';

export function usePageApi(
  wrap: <T>(fn: (...args: unknown[]) => Promise<T>) => (...args: unknown[]) => Promise<T>,
) {
  const client = useSanctumClient();
  const runtimeConfig = useRuntimeConfig();

  const page = wrap(async (id: number, locale: string): Promise<Page | null> => {
    return await client(`/api/page/${id}/${locale}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Site-Hash': runtimeConfig.public.siteHash,
      },
    });
  });

  return { page };
}
