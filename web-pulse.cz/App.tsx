import React, { useState, useEffect } from 'react';
import { Locale, FaqItem, ServiceItem, MenuItem, SettingItem } from './types';
import { apiService } from './services/apiService';
import { Navbar } from './components/Navbar';
import { Hero } from './components/Hero';
import { Services } from './components/Services';
import { Features } from './components/Features';
import { Faq } from './components/Faq';
import { ContactForm } from './components/ContactForm';
import { Footer } from './components/Footer';

function App() {
  const [locale, setLocale] = useState<Locale>('en');
  const [faqs, setFaqs] = useState<FaqItem[]>([]);
  const [services, setServices] = useState<ServiceItem[]>([]);
  const [topMenu, setTopMenu] = useState<MenuItem[]>([]);
  const [bottomMenu, setBottomMenu] = useState<MenuItem[]>([]);
  
  const [loadingServices, setLoadingServices] = useState(true);
  const [loadingFaq, setLoadingFaq] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      setLoadingServices(true);
      setLoadingFaq(true);
      
      try {
        // Fetch Settings (Menus)
        const settings = await apiService.getSettings(locale);
        const topMenuSetting = settings.find(s => s.type === 'topMenu');
        const bottomMenuSetting = settings.find(s => s.type === 'bottomMenu');
        
        if (topMenuSetting) setTopMenu(topMenuSetting.value.groups);
        if (bottomMenuSetting) setBottomMenu(bottomMenuSetting.value.groups);

        // Fetch Services
        const fetchedServices = await apiService.getServices(locale);
        setServices(fetchedServices);
        setLoadingServices(false);

        // Fetch FAQs
        const fetchedFaqs = await apiService.getFaqs(locale);
        setFaqs(fetchedFaqs);
        setLoadingFaq(false);

      } catch (error) {
        console.error("Failed to fetch data:", error);
        setLoadingServices(false);
        setLoadingFaq(false);
      }
    };

    fetchData();
  }, [locale]);

  return (
    <div className="min-h-screen bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark font-sans selection:bg-primary selection:text-white">
      <Navbar 
        menuItems={topMenu} 
        currentLocale={locale} 
        onLocaleChange={setLocale} 
      />
      
      <main>
        <Hero />
        <Services services={services} loading={loadingServices} />
        <Features />
        <Faq items={faqs} loading={loadingFaq} />
        <ContactForm currentLocale={locale} />
      </main>

      <Footer menuItems={bottomMenu} currentLocale={locale} />
    </div>
  );
}

export default App;