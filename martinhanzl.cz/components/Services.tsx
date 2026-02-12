import React from 'react';
import { ServiceItem } from '../types';

interface ServicesProps {
  services: ServiceItem[];
}

const Services: React.FC<ServicesProps> = ({ services }) => {
  const getIcon = (id: number) => {
    switch(id) {
      case 2: return 'rocket_launch';
      case 3: return 'badge'; // fallback if exists
      case 5: return 'article';
      case 4: return 'dns'; // approximate mapping
      default: return 'engineering';
    }
  };

  return (
    <section id="services" className="py-24 bg-background-light dark:bg-background-dark border-t border-gray-200 dark:border-white/5">
      <div className="max-w-7xl mx-auto px-6">
        <div className="text-center mb-16 reveal-on-scroll">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">Co dělám</h2>
          <p className="text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
            Specializované technické služby navržené pro škálovatelnost, výkon a zapojení uživatelů.
          </p>
        </div>

        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
          {services.map((service, index) => (
            <div 
              key={service.id} 
              className="group p-8 bg-white dark:bg-surface-dark rounded-2xl border border-gray-200 dark:border-white/5 hover:border-primary/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-glow/10 reveal-on-scroll"
              style={{ transitionDelay: `${index * 100}ms` }}
            >
              <div className="w-14 h-14 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                <span className="material-symbols-outlined text-[32px]">
                  {getIcon(service.id)}
                </span>
              </div>
              <h3 className="text-xl font-bold mb-3 text-slate-900 dark:text-white">{service.name}</h3>
              <div 
                className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed"
                dangerouslySetInnerHTML={{ __html: service.perex }}
              />
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default Services;