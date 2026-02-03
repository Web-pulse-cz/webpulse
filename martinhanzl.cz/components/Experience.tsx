import React from 'react';

const Experience: React.FC = () => {
  return (
    <section id="experience" className="py-24 md:py-32 bg-white dark:bg-[#0b0e11] relative border-t border-gray-200 dark:border-white/5">
      <div className="max-w-7xl mx-auto px-6">
        <div className="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-4 reveal-on-scroll">
          <div>
            <h2 className="text-3xl md:text-4xl font-bold mb-2">Pracovní zkušenosti</h2>
            <p className="text-slate-600 dark:text-slate-400">Časová osa mé profesní kariéry a růstu.</p>
          </div>
          <div className="h-1 w-20 bg-primary rounded-full"></div>
        </div>

        <div className="relative">
          <div className="absolute left-4 md:left-1/2 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-white/10 -translate-x-1/2 hidden md:block"></div>
          <div className="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-white/10 md:hidden"></div>
          
          <div className="flex flex-col gap-12">
            
            {/* Item 1 */}
            <div className="relative flex flex-col md:flex-row items-start group reveal-on-scroll">
              <div className="hidden md:flex w-1/2 justify-end pr-12 pt-1">
                <span className="font-display text-slate-500 font-bold text-lg text-right">12/2025 - 01/2026</span>
              </div>
              <div className="absolute left-4 md:left-1/2 -translate-x-1/2 w-4 h-4 rounded-full bg-background-light dark:bg-background-dark border-2 border-primary z-10 shadow-[0_0_0_4px_rgba(0,163,136,0.2)]"></div>
              <div className="md:w-1/2 pl-12 md:pl-12 ml-4 md:ml-0">
                <span className="md:hidden font-display text-primary font-bold text-sm mb-1 block">12/2025 - 01/2026</span>
                <div className="p-6 rounded-xl bg-gray-50 dark:bg-surface-dark border border-gray-200 dark:border-white/5 hover:border-primary/50 transition-all duration-300 group-hover:shadow-glow/20">
                  <div className="flex justify-between items-start mb-2">
                    <h3 className="text-xl font-bold text-slate-900 dark:text-white">Fiosoft</h3>
                    <span className="px-2 py-1 rounded text-xs font-bold bg-slate-200 dark:bg-white/10 text-slate-600 dark:text-slate-300">Backend</span>
                  </div>
                  <p className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                      Ve společnosti Fiosoft a. s. jsem působil jako klíčová podpora pro seniorní backend vývojáře s cílem zvýšit vývojovou kapacitu týmu na strategických projektech. Zodpovídal jsem za implementaci pokročilých funkcionalit do nového interního systému, kde jsem samostatně řešil komplexní moduly od návrhu až po nasazení.
                  </p>
                </div>
              </div>
            </div>

            {/* Item 2 */}
            <div className="relative flex flex-col md:flex-row-reverse items-start group reveal-on-scroll">
              <div className="hidden md:flex w-1/2 justify-start pl-12 pt-1">
                <span className="font-display text-slate-500 dark:text-slate-400 font-bold text-lg">09/2025 - 12/2025</span>
              </div>
              <div className="absolute left-4 md:left-1/2 -translate-x-1/2 w-3 h-3 rounded-full bg-background-light dark:bg-background-dark border-2 border-slate-400 dark:border-slate-600 z-10 group-hover:border-primary transition-colors"></div>
              <div className="md:w-1/2 pl-12 md:pr-12 md:pl-0 ml-4 md:ml-0 text-left md:text-right">
                <span className="md:hidden font-display text-slate-500 dark:text-slate-400 font-bold text-sm mb-1 block">09/2025 - 12/2025</span>
                <div className="p-6 rounded-xl bg-gray-50 dark:bg-surface-dark border border-gray-200 dark:border-white/5 hover:border-primary/30 transition-all duration-300">
                  <div className="flex justify-between md:justify-end items-start md:items-center mb-2 gap-2 flex-col md:flex-row-reverse">
                    <h3 className="text-xl font-bold text-slate-900 dark:text-white">Cortex a. s.</h3>
                    <span className="px-2 py-1 rounded text-xs font-bold bg-slate-200 dark:bg-white/10 text-slate-600 dark:text-slate-300">Backend</span>
                  </div>
                  <p className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                      Ve společnosti Cortex a. s. jsem zodpovídal za údržbu rozsáhlého interního projektu a vývoj nových funkcionalit v čistém PHP. Pracoval jsem se stávajícím kódem, navrhoval nové části aplikace a podílel se na zefektivňování procesů v rámci backendového vývoje.
                  </p>
                </div>
              </div>
            </div>

            {/* Item 3 */}
            <div className="relative flex flex-col md:flex-row items-start group reveal-on-scroll">
              <div className="hidden md:flex w-1/2 justify-end pr-12 pt-1">
                <span className="font-display text-slate-500 dark:text-slate-400 font-bold text-lg text-right">06/2023 - 07/2025</span>
              </div>
              <div className="absolute left-4 md:left-1/2 -translate-x-1/2 w-3 h-3 rounded-full bg-background-light dark:bg-background-dark border-2 border-slate-400 dark:border-slate-600 z-10 group-hover:border-primary transition-colors"></div>
              <div className="md:w-1/2 pl-12 md:pl-12 ml-4 md:ml-0">
                <span className="md:hidden font-display text-slate-500 dark:text-slate-400 font-bold text-sm mb-1 block">06/2023 - 07/2025</span>
                <div className="p-6 rounded-xl bg-gray-50 dark:bg-surface-dark border border-gray-200 dark:border-white/5 hover:border-primary/30 transition-all duration-300">
                  <div className="flex justify-between items-start mb-2">
                    <h3 className="text-xl font-bold text-slate-900 dark:text-white">Monster Media s. r. o.</h3>
                    <span className="px-2 py-1 rounded text-xs font-bold bg-slate-200 dark:bg-white/10 text-slate-600 dark:text-slate-300">Fullstack</span>
                  </div>
                  <p className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                      Vývoj e-commerce řešení na stacku Laravel a Nuxt.js (2/3). Participoval jsem na návrhu DB schémat, backendové logiky a REST API. Spravoval jsem rozsáhlé multisite platformy lokalizované do 25+ jazyků, s důrazem na optimalizaci výkonu databáze při objemu přes 500 000 produktů.
                  </p>
                </div>
              </div>
            </div>

            {/* Item 4 */}
            <div className="relative flex flex-col md:flex-row-reverse items-start group reveal-on-scroll">
              <div className="hidden md:flex w-1/2 justify-start pl-12 pt-1">
                <span className="font-display text-slate-500 dark:text-slate-400 font-bold text-lg">08/2022 - 07/2023</span>
              </div>
              <div className="absolute left-4 md:left-1/2 -translate-x-1/2 w-3 h-3 rounded-full bg-background-light dark:bg-background-dark border-2 border-slate-400 dark:border-slate-600 z-10 group-hover:border-primary transition-colors"></div>
              <div className="md:w-1/2 pl-12 md:pr-12 md:pl-0 ml-4 md:ml-0 text-left md:text-right">
                <span className="md:hidden font-display text-slate-500 dark:text-slate-400 font-bold text-sm mb-1 block">08/2022 - 07/2023</span>
                <div className="p-6 rounded-xl bg-gray-50 dark:bg-surface-dark border border-gray-200 dark:border-white/5 hover:border-primary/30 transition-all duration-300">
                  <div className="flex justify-between md:justify-end items-start md:items-center mb-2 gap-2 flex-col md:flex-row-reverse">
                    <h3 className="text-xl font-bold text-slate-900 dark:text-white">Wonder Makers</h3>
                    <span className="px-2 py-1 rounded text-xs font-bold bg-slate-200 dark:bg-white/10 text-slate-600 dark:text-slate-300">Fullstack</span>
                  </div>
                  <p className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                      Jako full-stack developer ve Wonder Makers jsem realizoval zakázkové projekty pro segment SME. Vyvinul jsem backend v Laravelu pro online bazarovou platformu, včetně napojení na frontend ve Vue.js 2 skrze REST API.
                  </p>
                </div>
              </div>
            </div>

             {/* Item 5 */}
             <div className="relative flex flex-col md:flex-row items-start group reveal-on-scroll">
              <div className="hidden md:flex w-1/2 justify-end pr-12 pt-1">
                <span className="font-display text-slate-500 dark:text-slate-400 font-bold text-lg text-right">06/2021 - 07/2022</span>
              </div>
              <div className="absolute left-4 md:left-1/2 -translate-x-1/2 w-3 h-3 rounded-full bg-background-light dark:bg-background-dark border-2 border-slate-400 dark:border-slate-600 z-10 group-hover:border-primary transition-colors"></div>
              <div className="md:w-1/2 pl-12 md:pl-12 ml-4 md:ml-0">
                <span className="md:hidden font-display text-slate-500 dark:text-slate-400 font-bold text-sm mb-1 block">06/2021 - 07/2022</span>
                <div className="p-6 rounded-xl bg-gray-50 dark:bg-surface-dark border border-gray-200 dark:border-white/5 hover:border-primary/30 transition-all duration-300">
                  <div className="flex justify-between items-start mb-2">
                    <h3 className="text-xl font-bold text-slate-900 dark:text-white">4Work Solutions s. r. o.</h3>
                    <span className="px-2 py-1 rounded text-xs font-bold bg-slate-200 dark:bg-white/10 text-slate-600 dark:text-slate-300">Fullstack</span>
                  </div>
                  <p className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                      Ve společnosti 4Works Solutions s. r. o. jsem působil primárně jako backend vývojář. Zodpovídal jsem za vývoj a údržbu zakázkových e-commerce řešení na platformě PrestaShop. Vyvíjel jsem vlastní moduly pro administraci e-shopů, realizoval frontendové úpravy a spravoval funkce backendového rozhraní.
                  </p>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </section>
  );
};

export default Experience;