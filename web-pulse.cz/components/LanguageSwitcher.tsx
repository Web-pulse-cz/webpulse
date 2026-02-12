import React from 'react';
import { Locale } from '../types';

interface LanguageSwitcherProps {
  currentLocale: Locale;
  onLocaleChange: (locale: Locale) => void;
}

const languages: { code: Locale; label: string; flag: string }[] = [
  { code: 'en', label: 'English', flag: '🇬🇧' },
  { code: 'cs', label: 'Čeština', flag: '🇨🇿' },
  { code: 'pl', label: 'Polski', flag: '🇵🇱' },
  { code: 'de', label: 'Deutsch', flag: '🇩🇪' },
  { code: 'sk', label: 'Slovenčina', flag: '🇸🇰' },
];

export const LanguageSwitcher: React.FC<LanguageSwitcherProps> = ({ currentLocale, onLocaleChange }) => {
  return (
    <div className="relative group">
      <button className="flex items-center gap-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 px-3 py-2 rounded-lg transition-colors text-sm font-medium">
        <span className="text-lg">
          {languages.find((l) => l.code === currentLocale)?.flag}
        </span>
        <span className="uppercase">{currentLocale}</span>
        <span className="material-icons-round text-sm">expand_more</span>
      </button>
      
      <div className="absolute right-0 mt-2 w-40 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 overflow-hidden hidden group-hover:block z-50 animate-in fade-in slide-in-from-top-2 duration-200">
        {languages.map((lang) => (
          <button
            key={lang.code}
            onClick={() => onLocaleChange(lang.code)}
            className={`w-full text-left px-4 py-2.5 text-sm flex items-center gap-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors ${
              currentLocale === lang.code 
                ? 'text-primary font-semibold bg-blue-50 dark:bg-slate-700/50' 
                : 'text-slate-600 dark:text-slate-300'
            }`}
          >
            <span className="text-lg">{lang.flag}</span>
            {lang.label}
          </button>
        ))}
      </div>
    </div>
  );
};