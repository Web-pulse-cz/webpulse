import type { PostCategory } from './PostCategory';

export interface Post {
  id: number;
  image: string;
  published_from: string;
  published_to: string;
  name: string;
  slug: string;
  perex: string;
  text: string;
  meta_title: string;
  meta_description: string;
  categories: PostCategory[];
  created_at: string;
}
