export interface Currency {
  id: number;
  code: string;
  rate: number;
  decimals: number;
  name: string;
  symbol_before: string;
  symbol_after: string;
}
