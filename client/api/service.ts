import type { Service } from '~/types/service';
import { useSanctumClient } from '#auth';

export function useServiceApi() {
  const client = useSanctumClient();

  const services = async (locale: string): Promise<Service[]> => {
    const response = await client<Service[]>(`/api/service/${locale}`, { method: 'GET' });
    console.log('Client response:', response);
  };

  const service = async (id: number): Promise<Service | null> => {
    const response = await client<Service>(`/api/admin/services/${id}`, { method: 'GET' });
    return response ?? null;
  };

  return {
    services,
    service,
  };
}
