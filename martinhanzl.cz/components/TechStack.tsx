import React from 'react';

const technologies = [
  { name: 'Vue.js', icon: 'code' },
  { name: 'Nuxt', icon: 'layers' },
  { name: 'Laravel', icon: 'php' },
  { name: 'MySQL', icon: 'database' },
  { name: 'AWS', icon: 'cloud' },
  { name: 'React', icon: 'javascript' },
  { name: 'Pinia', icon: 'store' },
  { name: 'Composer', icon: 'package_2' },
  { name: 'Tailwind', icon: 'css' },
  { name: 'AI', icon: 'smart_toy' },
];

const TechStack: React.FC = () => {
  return (
    <section id="tech-stack" className="py-24 bg-background-light dark:bg-background-dark">
      <div className="max-w-6xl mx-auto px-6">
        <div className="text-center mb-16">
          <h2 className="text-3xl md:text-4xl font-bold mb-4 dark:text-white">Technologies & Tools</h2>
          <p className="text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
            My preferred stack for building robust, scalable applications.
          </p>
        </div>
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
          {technologies.map((tech) => (
            <div
              key={tech.name}
              className="group relative flex flex-col items-center justify-center p-6 bg-white dark:bg-surface-dark border border-gray-200 dark:border-white/5 rounded-xl hover:border-primary/50 transition-all duration-300 hover:-translate-y-1"
            >
              <div className="w-12 h-12 flex items-center justify-center mb-3 text-slate-400 group-hover:text-primary transition-colors">
                <span className="material-symbols-outlined text-[40px]">
                  {tech.icon}
                </span>
              </div>
              <span className="font-display font-bold text-slate-700 dark:text-slate-300 group-hover:text-primary">
                {tech.name}
              </span>
              <div className="absolute inset-0 bg-primary/5 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default TechStack;
