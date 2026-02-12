import React, { useState } from 'react';
import { MenuGroup } from '../types';

interface NavbarProps {
  menuGroups: MenuGroup[];
}

const Navbar: React.FC<NavbarProps> = ({ menuGroups }) => {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const handleNavigation = (link: string | null) => {
    if (!link) return;
    
    let href = "#";
    if (link === 'faq') href = "#faq";
    else if (link === 'blog') href = "#"; 
    else if (link.startsWith('#')) href = link;
    
    const element = document.querySelector(href);
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' });
    }
    setMobileMenuOpen(false);
  };

  return (
    <nav className="fixed top-0 left-0 right-0 z-50 backdrop-blur-md bg-white/80 dark:bg-background-dark/80 border-b border-gray-200 dark:border-white/5 transition-all duration-300">
      <div className="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        <a href="#" className="flex items-center gap-2 group">
          <div className="h-8 w-8 bg-primary rounded-lg flex items-center justify-center text-white font-bold text-lg group-hover:shadow-glow transition-all duration-300">
            MH
          </div>
          <span className="font-bold text-xl tracking-tight hidden sm:block group-hover:text-primary transition-colors">Martin Hanzl</span>
        </a>

        <div className="hidden md:flex items-center gap-8 text-sm font-medium">
          <a href="#services" className="hover:text-primary transition-colors relative after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full">Služby</a>
          <a href="#experience" className="hover:text-primary transition-colors relative after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full">Zkušenosti</a>
          <a href="#projects" className="hover:text-primary transition-colors relative after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full">Projekty</a>
          <a href="#tech-stack" className="hover:text-primary transition-colors relative after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full">Technologie</a>
          
          {menuGroups.map((group, idx) => (
             group.link && group.link !== 'Home' ? (
                <button 
                  key={idx}
                  onClick={() => handleNavigation(group.link)}
                  className="hover:text-primary transition-colors relative after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full"
                >
                  {group.name}
                </button>
             ) : null
          ))}

          <a href="#contact" className="hover:text-primary transition-colors relative after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full">Kontakt</a>
        </div>

        <a href="#contact" className="hidden md:flex items-center justify-center h-10 px-6 rounded-lg bg-primary hover:bg-primary/90 text-white text-sm font-bold shadow-glow hover:shadow-glow-hover transition-all duration-300 transform hover:-translate-y-0.5">
          Kontaktujte mě
        </a>

        <button 
          className="md:hidden p-2 text-slate-600 dark:text-slate-300"
          onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
        >
          <span className="material-symbols-outlined">{mobileMenuOpen ? 'close' : 'menu'}</span>
        </button>
      </div>

      {/* Mobile Menu */}
      {mobileMenuOpen && (
        <div className="md:hidden absolute top-full left-0 right-0 bg-white dark:bg-background-dark border-b border-gray-200 dark:border-white/5 p-6 flex flex-col gap-4 shadow-xl">
          <a href="#services" onClick={() => setMobileMenuOpen(false)} className="text-lg font-medium hover:text-primary">Služby</a>
          <a href="#experience" onClick={() => setMobileMenuOpen(false)} className="text-lg font-medium hover:text-primary">Zkušenosti</a>
          <a href="#projects" onClick={() => setMobileMenuOpen(false)} className="text-lg font-medium hover:text-primary">Projekty</a>
          <a href="#tech-stack" onClick={() => setMobileMenuOpen(false)} className="text-lg font-medium hover:text-primary">Technologie</a>
          <a href="#contact" onClick={() => setMobileMenuOpen(false)} className="text-lg font-bold text-primary">Kontakt</a>
        </div>
      )}
    </nav>
  );
};

export default Navbar;