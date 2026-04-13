/**
 * Czech formatting utilities for numbers, currencies, and dates.
 */
export function useFormat() {
	/**
	 * Format number to Czech locale (1 234,56)
	 */
	function formatNumber(value: number | string | null | undefined, decimals = 2): string {
		if (value === null || value === undefined || value === '') return '—';
		const num = typeof value === 'string' ? parseFloat(value) : value;
		if (isNaN(num)) return '—';
		return num.toLocaleString('cs-CZ', {
			minimumFractionDigits: decimals,
			maximumFractionDigits: decimals,
		});
	}

	/**
	 * Format currency (1 234,56 Kč)
	 */
	function formatCurrency(value: number | string | null | undefined, currency = 'Kč', decimals = 2): string {
		if (value === null || value === undefined || value === '') return '—';
		return `${formatNumber(value, decimals)} ${currency}`;
	}

	/**
	 * Format date to Czech locale (13. 04. 2026)
	 */
	function formatDate(value: string | null | undefined): string {
		if (!value) return '—';
		const date = new Date(value);
		if (isNaN(date.getTime())) return value;
		return date.toLocaleDateString('cs-CZ', {
			day: '2-digit',
			month: '2-digit',
			year: 'numeric',
		});
	}

	/**
	 * Format datetime to Czech locale (13. 04. 2026 14:30)
	 */
	function formatDateTime(value: string | null | undefined): string {
		if (!value) return '—';
		const date = new Date(value);
		if (isNaN(date.getTime())) return value;
		return date.toLocaleString('cs-CZ', {
			day: '2-digit',
			month: '2-digit',
			year: 'numeric',
			hour: '2-digit',
			minute: '2-digit',
		});
	}

	return {
		formatNumber,
		formatCurrency,
		formatDate,
		formatDateTime,
	};
}
