import type { Quiz } from '../types/Quiz';

export function useQuizApi(
  wrap: <T>(fn: (...args: any[]) => Promise<T>) => (...args: any[]) => Promise<T>,
) {
  const client = useSanctumClient();
  const runtimeConfig = useRuntimeConfig();

  const quizzes = wrap(async (search?: string): Promise<Quiz[]> => {
    return await client(`/api/quiz`, {
      method: 'GET',
      query: { search: search },
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Site-Hash': runtimeConfig.public.siteHash,
      },
    });
  });

  const quiz = wrap(async (id: number): Promise<Quiz> => {
    return await client(`/api/quiz/${id}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Site-Hash': runtimeConfig.public.siteHash,
      },
    });
  });

  const submit = wrap(async (id: number, data: Quiz): Promise<Quiz> => {
    return await client(`/api/quiz/${id}`, {
      method: 'POST',
      body: data,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Site-Hash': runtimeConfig.public.siteHash,
      },
    });
  });

  return { quizzes, quiz, submit };
}
