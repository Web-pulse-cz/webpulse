import React from 'react';

const TechStack: React.FC = () => {
  return (
    <section id="tech-stack" className="py-24 bg-white dark:bg-[#0b0e11] border-t border-gray-200 dark:border-white/5">
      <div className="max-w-6xl mx-auto px-6">
        <div className="text-center mb-16 reveal-on-scroll">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">Technologie a nástroje</h2>
          <p className="text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
            Můj preferovaný stack pro vytváření robustních a škálovatelných aplikací.
          </p>
        </div>
        
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
          {[
            { name: 'Vue.js', icon: 'code' },
            { name: 'Nuxt', icon: 'layers' },
            { name: 'Laravel', icon: 'php' },
            { name: 'MySQL', icon: 'database' },
            { name: 'AWS', icon: 'cloud' }
          ].map((tech, i) => (
             <div key={i} className="group relative flex flex-col items-center justify-center p-6 bg-gray-50 dark:bg-surface-dark border border-gray-200 dark:border-white/5 rounded-xl hover:border-primary/50 transition-all duration-300 hover:-translate-y-1 reveal-on-scroll" style={{transitionDelay: `${i * 100}ms`}}>
              <div className="w-12 h-12 flex items-center justify-center mb-3 text-slate-400 group-hover:text-primary transition-colors">
                <span className="material-symbols-outlined text-[40px]">{tech.icon}</span>
              </div>
              <span className="font-display font-bold text-slate-700 dark:text-slate-300 group-hover:text-primary">{tech.name}</span>
              <div className="absolute inset-0 bg-primary/5 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default TechStack;