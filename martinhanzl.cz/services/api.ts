import { FaqItem, ServiceItem, SettingItem, DemandPayload } from '../types';

const BASE_URL = 'https://api.web-pulse.cz/api';

const HEADERS = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Site-Hash': 'EsAtU1TeCVJacAf6GCK84G7GPI15uZjPWKDtcYf8kJaFNaF88UrQIgp5qpqQnWmfrN3Y7c3GZQKDIL2jC2M4A8LlT9gROxmpaPYwaOwXfrVUJCYzKkhzfQU8aKUMMGlA'
};

async function fetchJson<T>(url: string): Promise<T> {
  const response = await fetch(url, {
    headers: HEADERS
  });
  if (!response.ok) {
    throw new Error(`Failed to fetch ${url}: ${response.statusText}`);
  }
  return response.json();
}

export const fetchFaq = async (): Promise<FaqItem[]> => {
  return fetchJson<FaqItem[]>(`${BASE_URL}/faq/cs`);
};

export const fetchServices = async (): Promise<ServiceItem[]> => {
  return fetchJson<ServiceItem[]>(`${BASE_URL}/service/cs`);
};

export const fetchSettings = async (): Promise<SettingItem[]> => {
  return fetchJson<SettingItem[]>(`${BASE_URL}/setting/cs`);
};

export const sendDemand = async (data: DemandPayload): Promise<any> => {
  const response = await fetch(`${BASE_URL}/demand/cs`, {
    method: 'POST',
    headers: HEADERS,
    body: JSON.stringify(data),
  });

  if (!response.ok) {
    const errorData = await response.json().catch(() => ({}));
    throw new Error(errorData.message || `Form submission failed: ${response.statusText}`);
  }
  
  return response.json();
};