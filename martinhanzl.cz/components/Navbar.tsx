import React, { useState, useEffect } from 'react';
import { MenuGroup } from '../types';

interface NavbarProps {
  menuGroups: MenuGroup[];
}

const Navbar: React.FC<NavbarProps> = ({ menuGroups }) => {
  const [scrolled, setScrolled] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 50);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  // Helper to handle scrolling to anchor tags or regular links
  const handleNavigation = (link: string | null) => {
    if (!link) return;
    
    // Check if it's an anchor link (starts with # or matches a section ID logic)
    // Based on the data provided, links are like "blog", "faq". 
    // For this Single Page App, we will assume:
    // "faq" -> #faq
    // "blog" -> external or placeholder
    // "Home" -> #top
    
    let href = "#";
    if (link === 'faq') href = "#faq";
    else if (link === 'blog') href = "#"; // Assuming placeholder for external
    else if (link.startsWith('#')) href = link;
    
    const element = document.querySelector(href);
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' });
    }
    
    setMobileMenuOpen(false);
  };

  return (
    <nav
      className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
        scrolled
          ? 'backdrop-blur-md bg-white/80 dark:bg-background-dark/80 border-b border-gray-200 dark:border-white/5 py-2'
          : 'bg-transparent py-4'
      }`}
    >
      <div className="max-w-7xl mx-auto px-6 flex items-center justify-between">
        <a href="#" className="flex items-center gap-2 group">
          <div className="h-10 w-10 bg-primary rounded-lg flex items-center justify-center text-white font-bold text-lg group-hover:shadow-glow transition-all duration-300">
            MH
          </div>
          <span className="font-bold text-xl tracking-tight hidden sm:block group-hover:text-primary transition-colors dark:text-white">
            Martin Hanzl
          </span>
        </a>

        {/* Desktop Menu */}
        <div className="hidden md:flex items-center gap-8 text-sm font-medium text-slate-700 dark:text-slate-300">
           {/* Static links for the SPA structure */}
           <a href="#services" className="hover:text-primary transition-colors relative after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full">Services</a>
           <a href="#experience" className="hover:text-primary transition-colors relative after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full">Experience</a>
           <a href="#tech-stack" className="hover:text-primary transition-colors relative after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full">Tech Stack</a>
          
          {/* Dynamic Links from API */}
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

          <a
            href="#contact"
            className="flex items-center justify-center h-10 px-6 rounded-lg bg-primary hover:bg-primary/90 text-white text-sm font-bold shadow-glow hover:shadow-glow-hover transition-all duration-300 transform hover:-translate-y-0.5"
          >
            Let's Talk
          </a>
        </div>

        {/* Mobile Toggle */}
        <button
          className="md:hidden p-2 text-slate-600 dark:text-slate-300"
          onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
        >
          <span className="material-symbols-outlined">
            {mobileMenuOpen ? 'close' : 'menu'}
          </span>
        </button>
      </div>

      {/* Mobile Menu */}
      {mobileMenuOpen && (
        <div className="md:hidden absolute top-full left-0 right-0 bg-white dark:bg-background-dark border-b border-gray-200 dark:border-white/5 p-6 shadow-xl flex flex-col gap-4">
          <a href="#services" onClick={() => setMobileMenuOpen(false)} className="text-lg font-medium dark:text-white">Services</a>
          <a href="#experience" onClick={() => setMobileMenuOpen(false)} className="text-lg font-medium dark:text-white">Experience</a>
          <a href="#tech-stack" onClick={() => setMobileMenuOpen(false)} className="text-lg font-medium dark:text-white">Tech Stack</a>
          {menuGroups.map((group, idx) => (
             group.link && group.link !== 'Home' ? (
                <button 
                  key={idx}
                  onClick={() => handleNavigation(group.link)}
                  className="text-left text-lg font-medium dark:text-white"
                >
                  {group.name}
                </button>
             ) : null
          ))}
          <a href="#contact" onClick={() => setMobileMenuOpen(false)} className="text-primary font-bold text-lg">Let's Talk</a>
        </div>
      )}
    </nav>
  );
};

export default Navbar;
