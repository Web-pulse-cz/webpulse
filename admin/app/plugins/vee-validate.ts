import { defineRule } from 'vee-validate';

export default defineNuxtPlugin(() => {
	defineRule('required', (value: unknown) => {
		if (value === null || value === undefined || value === '') {
			return 'Pole je povinné.';
		}
		if (Array.isArray(value) && value.length === 0) {
			return 'Pole je povinné.';
		}
		return true;
	});

	defineRule('email', (value: string) => {
		if (!value) return true;
		const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		if (!emailRegex.test(value)) {
			return 'Pole musí být platný e-mail.';
		}
		return true;
	});

	defineRule('min', (value: string, params: Record<string, unknown>) => {
		if (!value) return true;
		const min = Number(params.min ?? params[0]);
		if (String(value).length < min) {
			return `Pole musí obsahovat alespoň ${min} znaků.`;
		}
		return true;
	});

	defineRule('max', (value: string, params: Record<string, unknown>) => {
		if (!value) return true;
		const max = Number(params.max ?? params[0]);
		if (String(value).length > max) {
			return `Pole může obsahovat maximálně ${max} znaků.`;
		}
		return true;
	});

	defineRule('url', (value: string) => {
		if (!value) return true;
		try {
			new URL(value);
			return true;
		} catch {
			return 'Pole musí být platná URL adresa.';
		}
	});

	defineRule('numeric', (value: unknown) => {
		if (value === null || value === undefined || value === '') return true;
		if (isNaN(Number(value))) {
			return 'Pole musí být číslo.';
		}
		return true;
	});
});
