import type { Logo } from '~/types/logo';

export function useLogoApi(
  wrap: <T>(fn: (...args: unknown[]) => Promise<T>) => (...args: unknown[]) => Promise<T>,
) {
  const client = useSanctumClient();
  const runtimeConfig = useRuntimeConfig();

  const logo = wrap(async (locale: string): Promise<Logo | null> => {
    return await client(`/api/logo/${locale}}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Site-Hash': runtimeConfig.public.siteHash,
      },
    });
  });

  return { logo };
}
