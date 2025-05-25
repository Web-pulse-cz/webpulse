import { defineNuxtPlugin } from '#app';

export default defineNuxtPlugin((nuxtApp) => {
	const formatPrice = (
		price: number,
		currency: object,
		taxRate: object,
	): string => {
		const rate = 1 + taxRate.rate / 100;
		const finalPrice = price * rate;
		const symbolBefore
      = currency.symbol_before !== null ? currency.symbol_before : '';
		const symbolAfter
      = currency.symbol_after !== null ? currency.symbol_after : '';

		return `${symbolBefore}${finalPrice}${symbolAfter}`;

		/* const priceWithTax = price * (1 + taxRate);
    return new Intl.NumberFormat("cs-CZ", {
      style: "currency",
      currency: currency,
    }).format(priceWithTax); */
	};

	nuxtApp.vueApp.config.globalProperties.$formatPrice = formatPrice;
});
