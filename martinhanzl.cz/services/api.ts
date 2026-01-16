import { FaqItem, ServiceItem, SettingItem, DemandPayload } from '../types';

const BASE_URL = 'https://api.web-pulse.cz/api';
const SITE_HASH = 'EsAtU1TeCVJacAf6GCK84G7GPI15uZjPWKDtcYf8kJaFNaF88UrQIgp5qpqQnWmfrN3Y7c3GZQKDIL2jC2M4A8LlT9gROxmpaPYwaOwXfrVUJCYzKkhzfQU8aKUMMGlA';

async function fetchJson<T>(url: string): Promise<T> {
    const response = await fetch(url, {
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Site-Hash': SITE_HASH,
        },
    });
    if (!response.ok) {
        throw new Error(`API Error: ${response.statusText}`);
    }
    return response.json();
}


export const fetchFaq = async (): Promise<FaqItem[]> => {
  return fetchJson<FaqItem[]>(`${BASE_URL}/faq/en`);
};

export const fetchServices = async (): Promise<ServiceItem[]> => {
  return fetchJson<ServiceItem[]>(`${BASE_URL}/service/en`);
};

export const fetchSettings = async (): Promise<SettingItem[]> => {
  return fetchJson<SettingItem[]>(`${BASE_URL}/setting/en`);
};

export const sendDemand = async (data: DemandPayload): Promise<any> => {
  const response = await fetch(`${BASE_URL}/demand/en`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Site-Hash': SITE_HASH,
    },
    body: JSON.stringify(data),
  });

  if (!response.ok) {
    const errorData = await response.json().catch(() => ({}));
    throw new Error(errorData.message || `Form submission failed: ${response.statusText}`);
  }
  
  return response.json();
};
