import type { TaxRate } from './taxRate';
import type { Currency } from './currency';

export interface Service {
  id: number;
  type: string;
  price_type: string;
  price: number;
  tax_rate_id: number;
  tax_rate: TaxRate;
  currency_id: number;
  currency: Currency;
  image: string;
  active: boolean;
  name: string;
  slug: string;
  perex: string;
  description: string;
  meta_title: string;
  meta_description: string;
}
