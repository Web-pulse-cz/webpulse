import type { Category } from './Category';

export interface Review {
  id: number;
  image: string;
  name: string;
  slug: string;
  perex: string;
  text: string;
  meta_title: string;
  meta_description: string;
  categories: Category[];
}
