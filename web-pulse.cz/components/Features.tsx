import React from 'react';

export const Features: React.FC = () => {
  return (
    <section className="py-24 bg-slate-900 dark:bg-black text-white relative overflow-hidden">
      <div className="absolute inset-0 opacity-20 bg-[url('https://grainy-gradients.vercel.app/noise.svg')]"></div>
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div className="grid lg:grid-cols-2 gap-16 items-center">
          <div>
            <h2 className="font-display text-4xl lg:text-5xl font-bold mb-8 leading-tight">
              Built for flexibility <br />
              <span className="text-primary">without compromise.</span>
            </h2>
            <p className="text-slate-400 text-lg mb-8 max-w-md">
              We don't just write code; we architect solutions. Our methodology ensures your project is future-proof from day one.
            </p>
            <div className="space-y-8">
              <div className="flex gap-4">
                <div className="mt-1">
                  <div className="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-primary border border-slate-700">
                    <span className="material-icons-round">bolt</span>
                  </div>
                </div>
                <div>
                  <h4 className="text-xl font-bold mb-2">Lightning Fast</h4>
                  <p className="text-slate-400">
                    Optimized code architecture ensures minimal load times and maximum performance scores.
                  </p>
                </div>
              </div>
              <div className="flex gap-4">
                <div className="mt-1">
                  <div className="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-primary border border-slate-700">
                    <span className="material-icons-round">security</span>
                  </div>
                </div>
                <div>
                  <h4 className="text-xl font-bold mb-2">Secure by Default</h4>
                  <p className="text-slate-400">
                    Enterprise-grade security protocols implemented at every layer of the application stack.
                  </p>
                </div>
              </div>
              <div className="flex gap-4">
                <div className="mt-1">
                  <div className="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-primary border border-slate-700">
                    <span className="material-icons-round">all_inclusive</span>
                  </div>
                </div>
                <div>
                  <h4 className="text-xl font-bold mb-2">Infinitely Scalable</h4>
                  <p className="text-slate-400">
                    Cloud-native infrastructure that grows automatically alongside your user base.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div className="relative">
            <img
              alt="Data Analytics Dashboard"
              className="rounded-2xl shadow-2xl border border-slate-700 opacity-90"
              src="https://picsum.photos/600/600"
            />
            <div className="absolute -bottom-8 -left-8 bg-surface-dark p-6 rounded-xl border border-slate-700 shadow-xl max-w-xs">
              <div className="flex items-center gap-3 mb-2">
                <div className="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                <span className="text-sm text-slate-300 font-mono">SYSTEM STATUS</span>
              </div>
              <div className="text-2xl font-bold text-white mb-1">100% Operational</div>
              <div className="w-full bg-slate-700 h-1.5 rounded-full mt-2">
                <div className="bg-primary h-1.5 rounded-full" style={{ width: '100%' }}></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};