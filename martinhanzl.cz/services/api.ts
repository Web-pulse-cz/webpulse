import { FaqItem, ServiceItem, SettingItem, DemandPayload } from '../types';

const BASE_URL = 'https://api.web-pulse.cz/api';

async function fetchJson<T>(url: string): Promise<T> {
  const response = await fetch(url);
  if (!response.ok) {
    throw new Error(`Failed to fetch ${url}: ${response.statusText}`);
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
    },
    body: JSON.stringify(data),
  });

  if (!response.ok) {
    const errorData = await response.json().catch(() => ({}));
    throw new Error(errorData.message || `Form submission failed: ${response.statusText}`);
  }
  
  return response.json();
};
