import React, { useState } from 'react';
import { Locale, MenuItem } from '../types';
import { apiService } from '../services/apiService';

interface FooterProps {
  menuItems: MenuItem[];
  currentLocale: Locale;
}

export const Footer: React.FC<FooterProps> = ({ menuItems, currentLocale }) => {
  const [email, setEmail] = useState('');
  const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');

  const handleSubscribe = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email) return;
    setStatus('loading');
    try {
      await apiService.postNewsletter(currentLocale, email);
      setStatus('success');
      setEmail('');
      setTimeout(() => setStatus('idle'), 3000);
    } catch (error) {
      console.error(error);
      setStatus('error');
    }
  };

  return (
    <footer className="bg-slate-900 text-slate-300 py-16 border-t border-slate-800">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
          {/* Brand Column */}
          <div>
            <div className="flex items-center gap-2 mb-6">
              <span className="material-icons-round text-primary text-2xl">bolt</span>
              <span className="font-display font-bold text-xl text-white">Web-pulse</span>
            </div>
            <p className="text-sm text-slate-400 mb-6">
              Crafting superior digital experiences through innovation, design, and robust engineering.
            </p>
            <div className="flex gap-4">
              {['FB', 'TW', 'IN'].map((social) => (
                <a
                  key={social}
                  className="w-8 h-8 flex items-center justify-center rounded-full bg-slate-800 hover:bg-primary hover:text-white transition cursor-pointer"
                  href="#"
                >
                  <span className="text-xs">{social}</span>
                </a>
              ))}
            </div>
          </div>

          {/* Dynamic Menu Columns */}
          {menuItems.map((group, index) => (
             <div key={index}>
                 <h4 className="text-white font-bold mb-6">{group.name}</h4>
                 <ul className="space-y-3 text-sm">
                     {group.submenu?.map((item, idx) => (
                         <li key={idx}>
                             <a href={item.link ? `#${item.link}` : '#'} className="hover:text-primary transition">
                                 {item.name}
                             </a>
                         </li>
                     ))}
                 </ul>
             </div>
          ))}

          {/* Newsletter Column */}
          <div>
            <h4 className="text-white font-bold mb-6">Newsletter</h4>
            <p className="text-sm text-slate-400 mb-4">
              Get the latest insights on tech and design.
            </p>
            <form onSubmit={handleSubscribe} className="flex flex-col gap-2">
              <div className="flex gap-2">
                <input
                    className="bg-slate-800 border-none rounded-lg px-4 py-2 text-sm w-full focus:ring-2 focus:ring-primary text-white placeholder-slate-500"
                    placeholder="Your email"
                    type="email"
                    required
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    disabled={status === 'loading' || status === 'success'}
                />
                <button
                    type="submit"
                    disabled={status === 'loading' || status === 'success'}
                    className="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg text-sm font-semibold transition disabled:opacity-50"
                >
                    {status === 'loading' ? '...' : 'Subscribe'}
                </button>
              </div>
              {status === 'success' && <p className="text-xs text-green-400">Subscribed successfully!</p>}
              {status === 'error' && <p className="text-xs text-red-400">Failed to subscribe.</p>}
            </form>
          </div>
        </div>

        <div className="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-500">
          <div>© 2024 Web-pulse Agency. All rights reserved.</div>
          <div className="flex gap-6">
            <a className="hover:text-white" href="#">
              Privacy Policy
            </a>
            <a className="hover:text-white" href="#">
              Terms of Service
            </a>
          </div>
        </div>
      </div>
    </footer>
  );
};