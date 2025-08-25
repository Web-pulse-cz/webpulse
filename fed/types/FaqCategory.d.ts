import type { Faq } from './Faq';

export interface FaqCategory {
  id: number;
  name: string;
  slug: string;
  meta_title: string;
  meta_description: string;
  faqs: Faq[];
}
