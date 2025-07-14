import type { Service } from '~/types/service';

export function useServiceApi(
  wrap: <T>(fn: (...args: any[]) => Promise<T>) => (...args: any[]) => Promise<T>,
) {
  const client = useSanctumClient();

  const services = wrap(async (locale: string): Promise<Service[]> => {
    return await client(`/api/service/${locale}`, { method: 'GET' });
  });

  const service = wrap(async (id: number): Promise<Service | null> => {
    return await client(`/api/service/${id}`, { method: 'GET' });
  });

  return { services, service };
}
