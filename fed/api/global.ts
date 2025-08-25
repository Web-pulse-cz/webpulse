import type { Demand } from '../types/Demand';

export function useGlobalApi(
  wrap: <T>(fn: (...args: any[]) => Promise<T>) => (...args: any[]) => Promise<T>,
) {
  const client = useSanctumClient();

  const newsletter = wrap(async (email: string, locale: string): Promise<{}> => {
    return await client(`/api/newsletter/${locale}`, {
      method: 'POST',
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
