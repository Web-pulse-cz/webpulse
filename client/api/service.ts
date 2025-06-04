import type { Service } from '~/types/service';
export function useServiceApi() {
  const client = useSanctumClient();

  const services = async (locale: string): Promise<Service[]> => {
    const response = await client(`/api/service/${locale}`, { method: 'GET' });

    // Ověříme, že response je pole (např. při testu backendu)
    if (!Array.isArray(response)) {
      console.warn('Unexpected API response format:', response);
      return [];
    }

    // Převedeme na plain objekty
    return response.map((item) => JSON.parse(JSON.stringify(item)));
  };

  const service = async (id: number): Promise<Service | null> => {
    const response = await client(`/api/admin/services/${id}`, { method: 'GET' });

    if (!response || typeof response !== 'object') {
      console.warn('Unexpected API detail response:', response);
      return null;
    }

    return JSON.parse(JSON.stringify(response));
  };

  return {
    services,
    service,
  };
}
