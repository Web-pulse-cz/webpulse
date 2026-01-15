import React from 'react';
import { ServiceItem } from '../types';

interface ServicesProps {
  services: ServiceItem[];
  loading: boolean;
}

const getIconForService = (slug: string) => {
  if (slug.includes('web') || slug.includes('corporate')) return 'language';
  if (slug.includes('mobile') || slug.includes('app')) return 'smartphone';
  if (slug.includes('design') || slug.includes('ux')) return 'palette';
  if (slug.includes('landing')) return 'flight_takeoff';
  return 'layers';
};

const getColorClass = (index: number) => {
  const colors = [
    'text-primary bg-blue-100 dark:bg-blue-900/30',
    'text-purple-600 dark:text-purple-400 bg-purple-100 dark:bg-purple-900/30',
    'text-cyan-600 dark:text-cyan-400 bg-cyan-100 dark:bg-cyan-900/30',
  ];
  return colors[index % colors.length];
};

export const Services: React.FC<ServicesProps> = ({ services, loading }) => {
  return (
    <section className="py-24 bg-white dark:bg-slate-900" id="services">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-16">
          <h2 className="text-primary font-semibold tracking-wide uppercase text-sm mb-2">
            What We Do
          </h2>
          <h3 className="font-display text-3xl md:text-4xl font-bold text-slate-900 dark:text-white">
            Full-Cycle Development
          </h3>
        </div>
        
        {loading ? (
           <div className="grid md:grid-cols-3 gap-8">
             {[1, 2, 3].map((i) => (
               <div key={i} className="h-64 rounded-2xl bg-slate-50 dark:bg-slate-800 animate-pulse"></div>
             ))}
           </div>
        ) : (
          <div className="grid md:grid-cols-3 gap-8">
            {services.map((service, index) => (
              <div
                key={service.id}
                className="group relative p-8 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-white dark:hover:bg-slate-700 border border-slate-100 dark:border-slate-700 transition-all duration-300 hover:shadow-xl hover:-translate-y-1"
              >
                <div
                  className={`w-14 h-14 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform ${getColorClass(index)}`}
                >
                  <span className="material-icons-round text-3xl">
                    {getIconForService(service.slug)}
                  </span>
                </div>
                <h4 className="text-xl font-bold text-slate-900 dark:text-white mb-3">
                  {service.name}
                </h4>
                <div
                  className="text-slate-600 dark:text-slate-400 mb-6 prose dark:prose-invert line-clamp-3 leading-relaxed text-sm"
                  dangerouslySetInnerHTML={{ __html: service.perex }}
                />
                <a
                  className="inline-flex items-center text-primary font-semibold hover:gap-2 transition-all cursor-pointer"
                  href="#"
                >
                  Learn more{' '}
                  <span className="material-icons-round text-sm ml-1">arrow_forward</span>
                </a>
              </div>
            ))}
          </div>
        )}
      </div>
    </section>
  );
};