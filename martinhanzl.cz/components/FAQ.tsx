import React, { useState } from 'react';
import { FaqItem } from '../types';

interface FAQProps {
  items: FaqItem[];
}

const FAQ: React.FC<FAQProps> = ({ items }) => {
  const [openIndex, setOpenIndex] = useState<number | null>(null);

  const toggle = (index: number) => {
    setOpenIndex(openIndex === index ? null : index);
  };

  return (
    <section id="faq" className="py-24 bg-white dark:bg-[#0b0e11] border-t border-gray-200 dark:border-white/5">
      <div className="max-w-4xl mx-auto px-6">
        <div className="text-center mb-16">
          <h2 className="text-3xl md:text-4xl font-bold mb-4 dark:text-white">Frequently Asked Questions</h2>
          <p className="text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
            Answers to common questions about my services and process.
          </p>
        </div>

        <div className="flex flex-col gap-4">
          {items.map((item, index) => (
            <div
              key={item.id}
              className={`rounded-xl border transition-all duration-300 overflow-hidden ${
                openIndex === index
                  ? 'bg-gray-50 dark:bg-surface-dark border-primary/50 shadow-glow/10'
                  : 'bg-white dark:bg-transparent border-gray-200 dark:border-white/10 hover:border-primary/30'
              }`}
            >
              <button
                onClick={() => toggle(index)}
                className="w-full p-6 text-left flex justify-between items-center gap-4 focus:outline-none"
              >
                <span className={`font-bold text-lg ${openIndex === index ? 'text-primary' : 'text-slate-800 dark:text-white'}`}>
                  {item.question}
                </span>
                <span
                  className={`material-symbols-outlined transition-transform duration-300 ${
                    openIndex === index ? 'rotate-180 text-primary' : 'text-slate-400'
                  }`}
                >
                  keyboard_arrow_down
                </span>
              </button>
              
              <div
                className={`transition-all duration-300 ease-in-out ${
                  openIndex === index ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'
                }`}
              >
                <div 
                  className="px-6 pb-6 text-slate-600 dark:text-slate-400 leading-relaxed"
                  dangerouslySetInnerHTML={{ __html: item.answer }}
                />
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default FAQ;
