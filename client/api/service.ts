import type { Service } from '~/types/service';

export function useServiceApi(
  wrap: <T>(fn: (...args: any[]) => Promise<T>) => (...args: any[]) => Promise<T>,
) {
  const client = useSanctumClient();

  const services = wrap(async (locale: string): Promise<Service[]> => {
    const response = await client(`/api/service/${locale}`, { method: 'GET' });
    return Array.isArray(response) ? JSON.parse(JSON.stringify(response)) : [];
  });

  const service = wrap(async (id: number): Promise<Service | null> => {
    const response = await client(`/api/admin/services/${id}`, { method: 'GET' });
    return response && typeof response === 'object' ? JSON.parse(JSON.stringify(response)) : null;
  });

  return { services, service };
}
