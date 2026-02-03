import React from 'react';
import { MenuGroup } from '../types';

interface FooterProps {
  menuGroups: MenuGroup[];
}

const Footer: React.FC<FooterProps> = ({ menuGroups }) => {
  return (
    <footer className="py-8 bg-background-light dark:bg-background-dark border-t border-gray-200 dark:border-white/5">
      <div className="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <p className="text-slate-500 dark:text-slate-500 text-sm">
          © {new Date().getFullYear()} Martin Hanzl. Všechna práva vyhrazena.
        </p>
        <div className="flex items-center gap-6">
          <a href="#" className="text-slate-500 hover:text-primary transition-colors text-sm font-medium">Ochrana údajů</a>
          <a href="#" className="text-slate-500 hover:text-primary transition-colors text-sm font-medium">Obchodní podmínky</a>
        </div>
      </div>
    </footer>
  );
};

export default Footer;