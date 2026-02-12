export interface FaqItem {
  id: number;
  question: string;
  answer: string;
}

export interface TaxRate {
  id: number;
  name: string;
  rate: number;
}

export interface Currency {
  id: number;
  code: string;
  rate: string;
  decimals: number;
  name: string;
  symbol_before: string;
  symbol_after: string | null;
}

export interface ServiceItem {
  id: number;
  type: 'service' | 'product';
  price_type: 'hourly' | 'total';
  price: string;
  tax_rate_id: number;
  tax_rate: TaxRate;
  currency_id: number;
  currency: Currency;
  image: string | null;
  active: boolean;
  name: string;
  slug: string;
  perex: string;
  description: string;
  meta_title: string;
  meta_description: string;
}

export interface SubMenuItem {
  link: string | null;
  name: string;
}

export interface MenuGroup {
  link: string | null;
  name: string;
  submenu: SubMenuItem[];
}

export interface MenuValue {
  groups: MenuGroup[];
}

export interface SettingItem {
  id: number;
  type: string;
  value: MenuValue;
}

export interface DemandPayload {
  fullname: string;
  phone: string;
  email: string;
  text: string;
}
