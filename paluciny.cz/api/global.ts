import type { Demand } from '../types/Demand';

export function useGlobalApi(
  wrap: <T>(fn: (...args: unknown[]) => Promise<T>) => (...args: unknown[]) => Promise<T>,
) {
  const client = useSanctumClient();
  const runtimeConfig = useRuntimeConfig();

  const newsletter = wrap(async (email: string, locale: string): Promise<Record<string, never>> => {
    return await client(`/api/newsletter/${locale}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Site-Hash': runtimeConfig.public.siteHash,
      },
      body: {
        email: email,
      },
    });
  });
  const demand = wrap(
    async (
      values: {
        fullname: string;
        phone: string;
        email: string;
        text: string;
      },
      locale: string,
    ): Promise<Demand> => {
      return await client(`/api/demand/${locale}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-Site-Hash': runtimeConfig.public.siteHash,
        },
        body: {
          fullname: values.fullname,
          phone: values.phone,
          email: values.email,
          text: values.text,
        },
      });
    },
  );

  return { newsletter, demand };
}
