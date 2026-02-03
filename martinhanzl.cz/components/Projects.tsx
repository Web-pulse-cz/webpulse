import React from 'react';

const Projects: React.FC = () => {
  return (
    <section id="projects" className="py-24 bg-background-light dark:bg-background-dark border-t border-gray-200 dark:border-white/5">
      <div className="max-w-7xl mx-auto px-6">
        <div className="text-center mb-16 reveal-on-scroll">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">Projekty</h2>
          <p className="text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
            Ukázky mých nedávných prací a digitálních řešení.
          </p>
        </div>
        <div className="grid md:grid-cols-3 gap-8">
          
          <a href="https://chpp.cz/cs" target="_blank" className="group bg-white dark:bg-surface-dark rounded-2xl overflow-hidden border border-gray-200 dark:border-white/5 transition-all duration-300 hover:border-primary/30 shadow-sm reveal-on-scroll" style={{transitionDelay: '0ms'}}>
            <div className="aspect-video overflow-hidden">
              <img alt="Projekt 1" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="../public/images/chpp.png"/>
            </div>
            <div className="p-6">
              <h3 className="text-xl font-bold mb-2 text-slate-900 dark:text-white">CHPP s. r. o.</h3>
              <p className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                  Pro klienta CHPP s. r. o. jsem vytvořil nový obsahový web, který staví na dokonalé přehlednosti a moderní vizuální identitě. Hlavním cílem bylo transformovat komplexní informace do uživatelsky přívětivé podoby. Výsledkem je web s hravým, ale přesto vysoce profesionálním designem, který reflektuje inovativní přístup společnosti a zajišťuje intuitivní pohyb návštěvníka na stránce.
              </p>
            </div>
          </a>

          <a href="https://web-pulse.cz" target="_blank"className="group bg-white dark:bg-surface-dark rounded-2xl overflow-hidden border border-gray-200 dark:border-white/5 transition-all duration-300 hover:border-primary/30 shadow-sm reveal-on-scroll" style={{transitionDelay: '150ms'}}>
            <div className="aspect-video overflow-hidden">
              <img alt="Projekt 2" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="../public/images/webpulse.png"/>
            </div>
            <div className="p-6">
              <h3 className="text-xl font-bold mb-2 text-slate-900 dark:text-white">Web-pulse</h3>
              <p className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                  Realizace moderní webové prezentace pro digitální agenturu. Projekt definuje progresivní design, důraz na inovativní technologie a uživatelsky přívětivé rozhraní. Cílem bylo vytvořit přehledný a vizuálně atraktivní prostor, který jasně komunikuje kompetence agentury v digitálním prostředí.
              </p>
            </div>
          </a>

          <a href="https://zahrady-chytre.cz/cs/" target="_blank" className="group bg-white dark:bg-surface-dark rounded-2xl overflow-hidden border border-gray-200 dark:border-white/5 transition-all duration-300 hover:border-primary/30 shadow-sm reveal-on-scroll" style={{transitionDelay: '300ms'}}>
            <div className="aspect-video overflow-hidden">
              <img alt="Projekt 3" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="../public/images/zahrady.png"/>
            </div>
            <div className="p-6">
              <h3 className="text-xl font-bold mb-2 text-slate-900 dark:text-white">Zahrady-chytře</h3>
              <p className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                  Cílem projektu bylo vytvořit čistý a vizuálně poutavý web, který zrcadlí preciznost a cit pro detail dané firmy. Navrhl jsem moderní obsahovou prezentaci, která sází na přehledné uspořádání služeb a velkoformátové fotografie realizací. Výsledkem je intuitivní platforma, která zákazníky inspiruje a usnadňuje jim cestu k poptávce údržby či návrhu zahrady.
              </p>
            </div>
          </a>

        </div>
      </div>
    </section>
  );
};

export default Projects;