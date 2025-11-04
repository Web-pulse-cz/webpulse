import type { Quiz } from '../types/Quiz';

export function useQuizApi(
  wrap: <T>(fn: (...args: any[]) => Promise<T>) => (...args: any[]) => Promise<T>,
) {
  const client = useSanctumClient();

  const quizzes = wrap(async (search?: string, filters?: []): Promise<Quiz[]> => {
    return await client(`/api/quiz`, {
      method: 'GET',
      query: { search: search, orderBy: 'created_at', orderWay: 'desc', 'filters[]': filters },
    });
  });

  const quiz = wrap(async (id: number): Promise<Quiz> => {
    return await client(`/api/quiz/${id}`, { method: 'GET' });
  });

  const filter = wrap(async (id: number): Promise<Quiz> => {
    return await client(`/api/quiz/filter`, { method: 'GET' });
  });

  const submit = wrap(async (id: number, data: Quiz): Promise<Quiz> => {
    return await client(`/api/quiz/${id}`, {
      method: 'POST',
      body: data,
      headers: { 'Content-Type': 'application/json' },
    });
  });

  return { quizzes, quiz, filter, submit };
}
