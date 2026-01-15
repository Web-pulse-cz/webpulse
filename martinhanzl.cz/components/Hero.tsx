import React from 'react';

const Hero: React.FC = () => {
  return (
    <section className="relative min-h-screen flex items-center justify-center pt-20 overflow-hidden">
      {/* Background Effects */}
      <div className="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div className="absolute top-1/4 -left-20 w-96 h-96 bg-primary/10 rounded-full blur-[100px]"></div>
        <div className="absolute bottom-1/4 -right-20 w-[500px] h-[500px] bg-primary/5 rounded-full blur-[120px]"></div>
      </div>

      <div className="relative z-10 max-w-5xl mx-auto px-6 text-center flex flex-col items-center gap-8">
        <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-primary/30 bg-primary/10 text-primary text-xs font-medium uppercase tracking-wider mb-4 animate-pulse">
          <span className="w-2 h-2 rounded-full bg-primary"></span>
          Available for new projects
        </div>

        <h1 className="font-display text-5xl md:text-7xl lg:text-8xl font-bold tracking-tight leading-tight dark:text-white">
          Martin{" "}
          <span className="text-transparent bg-clip-text bg-gradient-to-r from-slate-400 via-slate-600 to-slate-900 dark:from-white dark:via-slate-300 dark:to-slate-500">
            Hanzl
          </span>
        </h1>

        <h2 className="font-body text-xl md:text-2xl text-slate-600 dark:text-slate-400 max-w-2xl font-light">
          Fullstack Web Developer crafting scalable{" "}
          <span className="text-primary font-medium">digital architectures</span>{" "}
          and minimalist interfaces.
        </h2>

        <div className="flex flex-wrap gap-4 mt-8 justify-center">
          <a
            href="#experience"
            className="h-12 px-8 rounded-lg border border-slate-300 dark:border-white/20 hover:border-primary dark:hover:border-primary text-slate-800 dark:text-white font-bold flex items-center gap-2 transition-all duration-300 hover:bg-slate-100 dark:hover:bg-white/5"
          >
            View Experience
            <span className="material-symbols-outlined text-[20px]">
              arrow_downward
            </span>
          </a>
          <a
            href="#contact"
            className="h-12 px-8 rounded-lg bg-primary text-white font-bold flex items-center gap-2 shadow-glow hover:shadow-glow-hover transition-all duration-300 hover:bg-primary/90 transform hover:-translate-y-0.5"
          >
            Get in Touch
            <span className="material-symbols-outlined text-[20px]">
              arrow_forward
            </span>
          </a>
        </div>

        <div className="mt-20 w-full max-w-3xl opacity-40 grayscale hover:grayscale-0 hover:opacity-100 transition-all duration-500">
          <div className="flex justify-between items-center gap-4 md:gap-8 flex-wrap md:flex-nowrap overflow-hidden">
            <span className="font-display font-bold text-lg text-slate-500">VUE</span>
            <span className="font-display font-bold text-lg text-slate-500">LARAVEL</span>
            <span className="font-display font-bold text-lg text-slate-500">REACT</span>
            <span className="font-display font-bold text-lg text-slate-500">AWS</span>
            <span className="font-display font-bold text-lg text-slate-500">TAILWIND</span>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Hero;
