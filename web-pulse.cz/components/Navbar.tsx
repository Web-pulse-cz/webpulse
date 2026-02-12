import React, { useState } from 'react';
import { Locale, MenuItem } from '../types';
import { LanguageSwitcher } from './LanguageSwitcher';

interface NavbarProps {
  menuItems: MenuItem[];
  currentLocale: Locale;
  onLocaleChange: (locale: Locale) => void;
}

export const Navbar: React.FC<NavbarProps> = ({ menuItems, currentLocale, onLocaleChange }) => {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  return (
    <nav className="fixed w-full z-50 bg-background-light/90 dark:bg-background-dark/90 backdrop-blur-md border-b border-gray-100 dark:border-gray-800">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-20">
          <div className="flex items-center gap-2">
            <span className="material-icons-round text-primary text-3xl">bolt</span>
            <span className="font-display font-bold text-xl text-slate-900 dark:text-white">
              Web-pulse
            </span>
          </div>

          {/* Desktop Menu */}
          <div className="hidden md:flex items-center space-x-8">
            <div className="flex space-x-6 font-medium">
              {menuItems.map((item, index) => (
                <a
                  key={index}
                  href={item.link ? `#${item.link}` : '#'}
                  className="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary transition"
                >
                  {item.name}
                </a>
              ))}
            </div>
            
            <LanguageSwitcher currentLocale={currentLocale} onLocaleChange={onLocaleChange} />

            <a
              className="bg-primary hover:bg-secondary text-white px-5 py-2.5 rounded-full font-semibold transition shadow-lg shadow-primary/25"
              href="#contact"
            >
              Get Started -&gt;
            </a>
          </div>

          {/* Mobile Menu Button */}
          <div className="md:hidden flex items-center gap-4">
            <LanguageSwitcher currentLocale={currentLocale} onLocaleChange={onLocaleChange} />
            <button 
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
              className="text-slate-600 dark:text-slate-300 p-2"
            >
              <span className="material-icons-round text-2xl">
                {isMobileMenuOpen ? 'close' : 'menu'}
              </span>
            </button>
          </div>
        </div>
      </div>

      {/* Mobile Menu Dropdown */}
      {isMobileMenuOpen && (
        <div className="md:hidden bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 absolute w-full left-0 top-20 shadow-xl">
            <div className="px-4 py-6 space-y-4">
              {menuItems.map((item, index) => (
                <a
                  key={index}
                  href={item.link ? `#${item.link}` : '#'}
                  onClick={() => setIsMobileMenuOpen(false)}
                  className="block text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary text-lg font-medium"
                >
                  {item.name}
                </a>
              ))}
               <a
                className="block w-full text-center bg-primary hover:bg-secondary text-white px-5 py-3 rounded-xl font-semibold transition mt-6"
                href="#contact"
                onClick={() => setIsMobileMenuOpen(false)}
              >
                Get Started
              </a>
            </div>
        </div>
      )}
    </nav>
  );
};