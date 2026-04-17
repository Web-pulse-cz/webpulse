export function useNoveltyApi(
  wrap: <T>(fn: (...args: unknown[]) => Promise<T>) => (...args: unknown[]) => Promise<T>,
) {
  const client = useSanctumClient();
  const runtimeConfig = useRuntimeConfig();

  const novelties = wrap(async (locale: string, perPage: number = 3): Promise<unknown> => {
    return await client(`/api/novelty/${locale}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Site-Hash': runtimeConfig.public.siteHash,
      },
      query: { paginate: perPage },
    });
  });

  const noveltyDetail = wrap(async (id: number, locale: string): Promise<unknown> => {
    return await client(`/api/novelty/${id}/${locale}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Site-Hash': runtimeConfig.public.siteHash,
      },
    });
  });

  return { novelties, noveltyDetail };
}
