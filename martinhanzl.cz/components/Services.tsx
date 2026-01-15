import React from 'react';
import { ServiceItem } from '../types';

interface ServicesProps {
  services: ServiceItem[];
}

const Services: React.FC<ServicesProps> = ({ services }) => {
  return (
    <section id="services" className="py-24 bg-background-light dark:bg-background-dark border-t border-gray-200 dark:border-white/5">
      <div className="max-w-6xl mx-auto px-6">
        <div className="text-center mb-16">
          <h2 className="text-3xl md:text-4xl font-bold mb-4 dark:text-white">What I Do</h2>
          <p className="text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
            Specialized technical services designed for scalability, performance, and user engagement.
          </p>
        </div>

        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          {services.map((service) => (
            <div
              key={service.id}
              className="group p-8 bg-white dark:bg-surface-dark rounded-2xl border border-gray-200 dark:border-white/5 hover:border-primary/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-glow/10 flex flex-col"
            >
              <div className="w-14 h-14 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                <span className="material-symbols-outlined text-[32px]">
                  {service.slug === 'landing-pages' ? 'rocket_launch' : 
                   service.slug === 'corporate-websites' ? 'business' : 
                   service.slug === 'content-websites' ? 'article' : 'dns'}
                </span>
              </div>
              <h3 className="text-xl font-bold mb-3 text-slate-900 dark:text-white">
                {service.name}
              </h3>
              
              {/* Dangerous inner HTML is required as API returns HTML content */}
              <div 
                className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed mb-4 flex-grow"
                dangerouslySetInnerHTML={{ __html: service.perex }}
              />
              
              <div className="pt-4 border-t border-gray-100 dark:border-white/5 mt-auto flex justify-between items-center">
                 <span className="text-xs font-bold text-slate-400 uppercase tracking-wide">{service.price_type}</span>
                 <span className="text-primary font-bold">{parseInt(service.price).toLocaleString()} {service.currency.code}</span>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default Services;
