export function useServiceApi() {
  const client = useSanctumClient();

  const services = async <T>(locale: string) => {
    try {
      return await client<T>(`/api/service/${locale}`, {
        method: 'GET',
      });
    } catch (error) {
      console.error('API GET Service error:', error);
      throw error;
    }
  };

  return { services };
}
