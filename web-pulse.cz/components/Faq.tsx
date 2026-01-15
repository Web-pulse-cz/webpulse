import React, { useState } from 'react';
import { FaqItem } from '../types';

interface FaqProps {
  items: FaqItem[];
  loading: boolean;
}

export const Faq: React.FC<FaqProps> = ({ items, loading }) => {
  const [openId, setOpenId] = useState<number | null>(null);

  const toggle = (id: number) => {
    setOpenId(openId === id ? null : id);
  };

  return (
    <section className="py-24 bg-white dark:bg-background-dark" id="faq">
      <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-16">
          <h3 className="font-display text-3xl font-bold text-slate-900 dark:text-white">
            Common Questions
          </h3>
          <p className="text-slate-500 dark:text-slate-400 mt-2">
            Everything you need to know about working with us.
          </p>
        </div>
        
        <div className="space-y-4">
          {loading ? (
             [1,2,3].map(i => (
                 <div key={i} className="h-20 bg-slate-100 dark:bg-slate-800 rounded-xl animate-pulse"></div>
             ))
          ) : (
              items.map((item) => (
                <div
                  key={item.id}
                  className="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden bg-slate-50 dark:bg-slate-800"
                >
                  <button
                    onClick={() => toggle(item.id)}
                    className="w-full flex justify-between items-center p-6 hover:bg-slate-100 dark:hover:bg-slate-700 transition text-left focus:outline-none"
                  >
                    <span className="font-semibold text-slate-900 dark:text-white">
                      {item.question}
                    </span>
                    <span
                      className={`material-icons-round text-slate-400 transition-transform duration-300 ${
                        openId === item.id ? 'rotate-45' : ''
                      }`}
                    >
                      add
                    </span>
                  </button>
                  <div
                    className={`transition-all duration-300 overflow-hidden ${
                      openId === item.id ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'
                    }`}
                  >
                    <div 
                      className="p-6 pt-0 text-slate-600 dark:text-slate-400 prose dark:prose-invert max-w-none text-sm"
                      dangerouslySetInnerHTML={{__html: item.answer}}
                    />
                  </div>
                </div>
              ))
          )}
        </div>
      </div>
    </section>
  );
};