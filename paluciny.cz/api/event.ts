export function useEventApi(
  wrap: <T>(fn: (...args: unknown[]) => Promise<T>) => (...args: unknown[]) => Promise<T>,
) {
  const client = useSanctumClient();
  const runtimeConfig = useRuntimeConfig();

  const events = wrap(
    async (locale: string, perPage?: number, categoryId?: number): Promise<unknown> => {
      const query: Record<string, unknown> = {};
      if (perPage) query.paginate = perPage;
      if (categoryId) query.category = categoryId;

      return await client(`/api/event/${locale}`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-Site-Hash': runtimeConfig.public.siteHash,
        },
        query,
      });
    },
  );

  const eventDetail = wrap(async (id: number, locale: string): Promise<unknown> => {
    return await client(`/api/event/${id}/${locale}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Site-Hash': runtimeConfig.public.siteHash,
      },
    });
  });

  const eventCategories = wrap(async (locale: string): Promise<unknown> => {
    return await client(`/api/event/category/${locale}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Site-Hash': runtimeConfig.public.siteHash,
      },
    });
  });

  const eventCategoryDetail = wrap(async (id: number, locale: string): Promise<unknown> => {
    return await client(`/api/event/category/${id}/${locale}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Site-Hash': runtimeConfig.public.siteHash,
      },
    });
  });

  const eventRegister = wrap(
    async (data: Record<string, unknown>, locale: string): Promise<unknown> => {
      return await client(`/api/event/registration/${locale}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-Site-Hash': runtimeConfig.public.siteHash,
        },
        body: data,
      });
    },
  );

  return { events, eventDetail, eventCategories, eventCategoryDetail, eventRegister };
}
