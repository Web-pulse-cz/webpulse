import React, { useEffect, useState } from 'react';
import Navbar from './components/Navbar';
import Hero from './components/Hero';
import Services from './components/Services';
import Experience from './components/Experience';
import Projects from './components/Projects';
import TechStack from './components/TechStack';
import FAQ from './components/FAQ';
import Contact from './components/Contact';
import Footer from './components/Footer';
import { fetchFaq, fetchServices, fetchSettings } from './services/api';
import { FaqItem, ServiceItem, MenuGroup } from './types';

function App() {
  const [loading, setLoading] = useState(true);
  const [faqItems, setFaqItems] = useState<FaqItem[]>([]);
  const [serviceItems, setServiceItems] = useState<ServiceItem[]>([]);
  const [topMenu, setTopMenu] = useState<MenuGroup[]>([]);
  const [bottomMenu, setBottomMenu] = useState<MenuGroup[]>([]);

  useEffect(() => {
    const loadData = async () => {
      try {
        const [faqs, services, settings] = await Promise.all([
          fetchFaq(),
          fetchServices(),
          fetchSettings(),
        ]);

        setFaqItems(faqs);
        setServiceItems(services);

        const topSettings = settings.find((s) => s.type === 'topMenu');
        if (topSettings && topSettings.value.groups) {
          setTopMenu(topSettings.value.groups);
        }

        const bottomSettings = settings.find((s) => s.type === 'bottomMenu');
        if (bottomSettings && bottomSettings.value.groups) {
          setBottomMenu(bottomSettings.value.groups);
        }
      } catch (error) {
        console.error("Failed to load initial data", error);
      } finally {
        setLoading(false);
      }
    };

    loadData();
  }, []);

  // Simple intersection observer for scroll reveal effects
  useEffect(() => {
    if (loading) return;

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
        }
      });
    }, { threshold: 0.1 });

    const elements = document.querySelectorAll('.reveal-on-scroll');
    elements.forEach((el) => observer.observe(el));

    return () => observer.disconnect();
  }, [loading]);

  if (loading) {
    return (
      <div className="min-h-screen bg-background-light dark:bg-background-dark flex items-center justify-center">
        <div className="flex flex-col items-center gap-4">
          <div className="w-12 h-12 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white min-h-screen font-body selection:bg-primary selection:text-white">
      <Navbar menuGroups={topMenu} />
      <Hero />
      <Services services={serviceItems} />
      <Experience />
      <Projects />
      <TechStack />
      {/*<FAQ items={faqItems} />*/}
      <Contact />
      <Footer menuGroups={bottomMenu} />
    </div>
  );
}

export default App;