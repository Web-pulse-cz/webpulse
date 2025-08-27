import type { Post } from '~/types/Post';
import type { PostCategory } from '~/types/PostCategory';

export function useBlogApi(
  wrap: <T>(fn: (...args: any[]) => Promise<T>) => (...args: any[]) => Promise<T>,
) {
  const client = useSanctumClient();

  const categories = wrap(async (locale: string): Promise<PostCategory[]> => {
    return await client(`/api/blog/category/${locale}`, { method: 'GET' });
  });

  const categoryDetail = wrap(async (id: number, locale: string): Promise<PostCategory[]> => {
    return await client(`/api/blog/category/${id}/${locale}`, { method: 'GET' });
  });

  const posts = wrap(
    async (
      page: number,
      perPage: number,
      locale: string,
      categoryId?: number | null,
    ): Promise<Post | null> => {
      return await client(`/api/blog/post/${locale}`, {
        method: 'GET',
        query: { categoryId: categoryId, page: page, paginate: perPage },
      });
    },
  );

  const postDetail = wrap(async (id: number, locale: number): Promise<Post | null> => {
    return await client(`/api/blog/post/${id}/${locale}`, { method: 'GET' });
  });

  return { categories, categoryDetail, posts, postDetail };
}
