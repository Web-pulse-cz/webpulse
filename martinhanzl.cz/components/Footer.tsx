import React from 'react';
import { MenuGroup } from '../types';

interface FooterProps {
  menuGroups: MenuGroup[];
}

const Footer: React.FC<FooterProps> = ({ menuGroups }) => {
  return (
    <footer className="py-12 bg-background-light dark:bg-background-dark border-t border-gray-200 dark:border-white/5">
      <div className="max-w-7xl mx-auto px-6">
        <div className="flex flex-col md:flex-row justify-between items-start gap-8 mb-8">
           <div className="flex flex-col gap-4">
             <div className="flex items-center gap-2">
                <div className="h-8 w-8 bg-primary rounded-lg flex items-center justify-center text-white font-bold text-sm">
                    MH
                </div>
                <span className="font-bold text-lg dark:text-white">Martin Hanzl</span>
             </div>
             <p className="text-sm text-slate-500 max-w-xs">
               Building digital experiences with modern technologies and focus on performance.
             </p>
           </div>
           
           <div className="flex gap-12 flex-wrap">
             {menuGroups.map((group, idx) => (
               <div key={idx} className="flex flex-col gap-3">
                 <h4 className="font-bold text-slate-900 dark:text-white">{group.name}</h4>
                 <div className="flex flex-col gap-2">
                    {group.submenu.map((item, subIdx) => (
                      <a 
                        key={subIdx} 
                        href={item.link ? (item.link === 'faq' ? '#faq' : '#') : '#'}
                        className="text-sm text-slate-500 hover:text-primary transition-colors"
                      >
                        {item.name}
                      </a>
                    ))}
                 </div>
               </div>
             ))}
           </div>
        </div>
        
        <div className="pt-8 border-t border-gray-200 dark:border-white/5 flex flex-col md:flex-row justify-between items-center gap-4">
          <p className="text-slate-500 text-sm">
            © {new Date().getFullYear()} Martin Hanzl. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
