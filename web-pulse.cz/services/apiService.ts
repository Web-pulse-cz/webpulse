import { DemandPayload, FaqItem, Locale, ServiceItem, SettingItem } from '../types';

const API_BASE = 'https://martinhanzl.cz/api';

async function fetchJson<T>(url: string): Promise<T> {
  const response = await fetch(url, {
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    }
  });
  if (!response.ok) {
    throw new Error(`API Error: ${response.statusText}`);
  }
  return response.json();
}

async function postJson<T, R>(url: string, body: T): Promise<R> {
  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(body),
  });
  if (!response.ok) {
    throw new Error(`API Error: ${response.statusText}`);
  }
  return response.json();
}

export const apiService = {
  getFaqs: (locale: Locale) => fetchJson<FaqItem[]>(`${API_BASE}/faq/${locale}`),
  
  getServices: (locale: Locale) => fetchJson<ServiceItem[]>(`${API_BASE}/service/${locale}`),
  
  getSettings: (locale: Locale) => fetchJson<SettingItem[]>(`${API_BASE}/setting/${locale}`),
  
  postDemand: (locale: Locale, data: DemandPayload) => 
    postJson<DemandPayload, any>(`${API_BASE}/demand/${locale}`, data),
    
  postNewsletter: (locale: Locale, email: string) => 
    postJson<{ email: string }, any>(`${API_BASE}/newsletter/${locale}`, { email }),
};