export type Locale = 'cs' | 'en' | 'pl' | 'de' | 'sk';

export interface FaqItem {
  id: number;
  question: string;
  answer: string;
}

export interface ServiceItem {
  id: number;
  type: string;
  price_type: string;
  price: string;
  image: string | null;
  active: boolean;
  name: string;
  slug: string;
  perex: string;
  description: string;
  meta_title: string;
  meta_description: string;
}

export interface MenuItem {
  link: string | null;
  name: string;
  submenu?: MenuItem[];
}

export interface MenuGroup {
  groups: MenuItem[];
}

export interface SettingItem {
  id: number;
  type: 'topMenu' | 'bottomMenu';
  value: MenuGroup;
}

export interface DemandPayload {
  fullname: string;
  phone: string;
  email: string;
  text: string;
}

export interface NewsletterPayload {
  email: string;
}