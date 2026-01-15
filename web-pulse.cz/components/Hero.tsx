import React from 'react';

export const Hero: React.FC = () => {
  return (
    <section className="relative pt-32 pb-20 lg:pt-40 lg:pb-32 overflow-hidden">
      <div className="absolute top-0 right-0 -mr-20 -mt-20 w-[600px] h-[600px] bg-primary/10 rounded-full blur-3xl dark:bg-primary/5 pointer-events-none"></div>
      <div className="absolute bottom-0 left-0 -ml-20 -mb-20 w-[400px] h-[400px] bg-accent/30 rounded-full blur-3xl dark:bg-accent/10 pointer-events-none"></div>
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div className="grid lg:grid-cols-2 gap-12 items-center">
          <div>
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 dark:bg-slate-800 border border-blue-100 dark:border-slate-700 text-primary text-xs font-semibold mb-6">
              <span className="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
              Accepting New Projects for 2024
            </div>
            <h1 className="font-display text-5xl lg:text-7xl font-bold tracking-tight text-slate-900 dark:text-white mb-6">
              Crafting <br />
              <span className="text-gradient">Digital Excellence</span>
            </h1>
            <p className="text-lg text-slate-600 dark:text-slate-400 mb-8 max-w-lg leading-relaxed">
              We transform ambitious ideas into high-performing websites and mobile applications. Partner with Web-pulse to scale your digital presence.
            </p>
            <div className="flex flex-col sm:flex-row gap-4">
              <a
                className="bg-primary hover:bg-secondary text-white px-8 py-3.5 rounded-full font-semibold text-center transition shadow-lg shadow-primary/30 flex items-center justify-center gap-2"
                href="#contact"
              >
                Start Your Project
                <span className="material-icons-round text-sm">arrow_forward</span>
              </a>
            </div>
          </div>
          <div className="relative">
            <div className="relative rounded-2xl bg-gradient-to-br from-blue-50 to-white dark:from-slate-800 dark:to-slate-900 p-8 shadow-2xl border border-slate-100 dark:border-slate-700">
              <div className="absolute -top-6 -right-6 w-24 h-24 bg-accent rounded-full opacity-50 blur-xl"></div>
              <div className="relative z-10 grid grid-cols-2 gap-4">
                <div className="col-span-2 glass-card rounded-xl p-6 shadow-lg">
                  <div className="flex justify-between items-center mb-4">
                    <div className="h-3 w-20 bg-slate-200 dark:bg-slate-600 rounded"></div>
                    <div className="h-8 w-8 bg-primary/20 rounded-full flex items-center justify-center text-primary">
                      <span className="material-icons-round text-sm">bar_chart</span>
                    </div>
                  </div>
                  <div className="space-y-2">
                    <div className="h-2 w-full bg-slate-100 dark:bg-slate-700 rounded"></div>
                    <div className="h-2 w-3/4 bg-slate-100 dark:bg-slate-700 rounded"></div>
                  </div>
                  <div className="mt-6 h-24 bg-gradient-to-r from-primary to-cyan-400 rounded-lg opacity-80"></div>
                </div>
                <div className="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-md border border-slate-100 dark:border-slate-700">
                  <div className="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mb-3">
                    <span className="material-icons-round text-primary">code</span>
                  </div>
                  <div className="h-2 w-16 bg-slate-200 dark:bg-slate-600 rounded mb-2"></div>
                  <div className="h-2 w-10 bg-slate-100 dark:bg-slate-700 rounded"></div>
                </div>
                <div className="bg-slate-900 dark:bg-black rounded-xl p-4 shadow-md text-white">
                  <div className="flex justify-between mb-4">
                    <span className="text-xs text-slate-400">Status</span>
                    <span className="text-xs text-emerald-400">Active</span>
                  </div>
                  <div className="text-2xl font-bold mb-1">98%</div>
                  <div className="text-xs text-slate-400">Uptime Guarantee</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};