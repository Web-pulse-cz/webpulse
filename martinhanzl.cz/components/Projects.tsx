import React from 'react';

const Projects: React.FC = () => {
  return (
    <section id="projects" className="py-24 bg-background-light dark:bg-background-dark border-t border-gray-200 dark:border-white/5">
      <div className="max-w-6xl mx-auto px-6">
        <div className="text-center mb-16 reveal-on-scroll">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">Projekty</h2>
          <p className="text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
            Ukázky mých nedávných prací a digitálních řešení.
          </p>
        </div>
        <div className="grid md:grid-cols-3 gap-8">
          
          <div className="group bg-white dark:bg-surface-dark rounded-2xl overflow-hidden border border-gray-200 dark:border-white/5 transition-all duration-300 hover:border-primary/30 shadow-sm reveal-on-scroll" style={{transitionDelay: '0ms'}}>
            <div className="aspect-video overflow-hidden">
              <img alt="Projekt 1" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCFzA4O__zt02UzDg_nRA1RKsGx9tD686uq1YO66YZD48GJrSLI5W2qXeyURARdhC3Puyp3IGrxo5LWnzyJa93BmOrno7W0ZGk7Elty9PyfcsO9pGHTyf9-dg0lVCeopCkM1mPPKwr9q3VTaQxcNkCWCyTrqgjc58LGX3vYn8isUWqf2Bo6Sgx425g0fl2pyHtt5GsXFwd5_XYXJDbyXHnk5vCTV9FS4AhwmsbCQdF1iRAxMGoJ8-ICGQP86mr17vdVR4AY0CYJTZIM"/>
            </div>
            <div className="p-6">
              <h3 className="text-xl font-bold mb-2 text-slate-900 dark:text-white">Finanční Dashboard</h3>
              <p className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Komplexní analytický nástroj pro sledování investic v reálném čase s integrací více API.
              </p>
            </div>
          </div>

          <div className="group bg-white dark:bg-surface-dark rounded-2xl overflow-hidden border border-gray-200 dark:border-white/5 transition-all duration-300 hover:border-primary/30 shadow-sm reveal-on-scroll" style={{transitionDelay: '150ms'}}>
            <div className="aspect-video overflow-hidden">
              <img alt="Projekt 2" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCUQYzxMGkaohRLeg0KdbVOpcJ_2RwmnM9cJJPjRkvuXcVzk9-0bw3VTHJAk-P-19SYi852uYW0jsnZc-ZbSkTx-YoDhoQmX74l09AocsS0TVgCINyTbSPeLLhd2nNoKyUyh0Lt-ZZU7TL_xB6hYKD572PeZd5JOTfRHCP5L38OPp3hNTTremBk84J-TOWTVyoEKr3v-YJRmBgkWehxI46iHH77_Xwt_1GyylD6RU1Vlj7duZcPWV9DrUDDb_WVqSNqFTFixb-SeQPo"/>
            </div>
            <div className="p-6">
              <h3 className="text-xl font-bold mb-2 text-slate-900 dark:text-white">E-commerce Platforma</h3>
              <p className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Moderní online obchod s bleskovým vyhledáváním a plně responzivním rozhraním.
              </p>
            </div>
          </div>

          <div className="group bg-white dark:bg-surface-dark rounded-2xl overflow-hidden border border-gray-200 dark:border-white/5 transition-all duration-300 hover:border-primary/30 shadow-sm reveal-on-scroll" style={{transitionDelay: '300ms'}}>
            <div className="aspect-video overflow-hidden">
              <img alt="Projekt 3" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBL4NXwFJwmVHdiEdA_Dn3s8xfRSqWv1ZAa9TthoiQBzgWqFOMdUwpJzaQjoqFUKznLDUd6fAuFlCqGHocXKhVvyAsqtjOPgyKVIw9lekeZyp2t-Wga3_PPVK7pQCdE-6qzoyD1E-70AkqKcjgeSDEDFjPuHb-q5RTob6tRkgIhkIJR6lYaw0DJSFSQrsnkRmRd7-TthcatfCpk5HuIz6FTNy0NtSBYXCDlfyt1-zpl-qE_ftWwTo0nQNa6KS0g2t9pU7EyDma2uTYW"/>
            </div>
            <div className="p-6">
              <h3 className="text-xl font-bold mb-2 text-slate-900 dark:text-white">SaaS Architektura</h3>
              <p className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Návrh a implementace cloudové infrastruktury pro škálovatelnou B2B aplikaci.
              </p>
            </div>
          </div>

        </div>
      </div>
    </section>
  );
};

export default Projects;