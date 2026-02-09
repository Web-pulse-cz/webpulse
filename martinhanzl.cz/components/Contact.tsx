import React, { useState } from 'react';
import { sendDemand } from '../services/api';

const Contact: React.FC = () => {
  const [formData, setFormData] = useState({
    fullname: '',
    email: '',
    subject: '',
    message: '',
  });
  const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');
  const [errorMessage, setErrorMessage] = useState('');

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData({ ...formData, [e.target.id]: e.target.value });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setStatus('loading');
    
    try {
      const payload = {
        fullname: formData.fullname,
        email: formData.email,
        phone: '',
        message: `Subject: ${formData.subject}\n\n${formData.message}`,
      };

      await sendDemand(payload);
      setStatus('success');
      setFormData({ fullname: '', email: '', subject: '', message: '' });
    } catch (error: any) {
      setStatus('error');
      setErrorMessage(error.message || "Connection failed.");
    }
  };

  return (
    <section id="contact" className="py-24 bg-background-light dark:bg-background-dark border-t border-gray-200 dark:border-white/5">
      <div className="max-w-7xl mx-auto px-6">
        <div className="grid lg:grid-cols-2 gap-16">
          <div className="flex flex-col justify-center reveal-on-scroll">
            <div className="inline-block px-3 py-1 rounded bg-primary/10 text-primary text-sm font-bold mb-6 w-fit">
               Pojďme vytvořit něco výjimečného
            </div>
            <h2 className="text-4xl md:text-5xl font-bold mb-6 text-slate-900 dark:text-white">Kontaktujte <span className="text-primary">mě</span></h2>
            <p className="text-lg text-slate-600 dark:text-slate-400 mb-10 max-w-md">
              Máte v hlavě projekt nebo chcete prodiskutovat moderní webovou architekturu? Jsem otevřen novým příležitostem.
            </p>
            <div className="flex flex-col gap-6">
              <a href="mailto:martas.hanzl@email.cz" className="flex items-center gap-4 text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary transition-colors group">
                <div className="w-12 h-12 rounded-full bg-white dark:bg-surface-dark border border-gray-200 dark:border-white/5 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors shadow-sm">
                  <span className="material-symbols-outlined">mail</span>
                </div>
                <span className="text-lg font-medium">martas.hanzl@email.cz</span>
              </a>
              <a href="https://www.linkedin.com/in/martin-hanzl-618784173/" className="flex items-center gap-4 text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary transition-colors group" target="_blank" rel="noopener noreferrer">
                <div className="w-12 h-12 rounded-full bg-white dark:bg-surface-dark border border-gray-200 dark:border-white/5 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors shadow-sm">
                  <span className="material-symbols-outlined">link</span>
                </div>
                <span className="text-lg font-medium">linkedin.com/in/martinhanzl</span>
              </a>
            </div>
          </div>

          <div className="bg-white dark:bg-surface-dark p-8 md:p-10 rounded-2xl border border-gray-200 dark:border-white/5 shadow-2xl shadow-black/5 dark:shadow-black/20 reveal-on-scroll delay-100">
            {status === 'success' ? (
               <div className="flex flex-col items-center justify-center h-full py-12 text-center">
                 <div className="w-16 h-16 bg-green-100 dark:bg-green-900/30 text-green-600 rounded-full flex items-center justify-center mb-4">
                   <span className="material-symbols-outlined text-3xl">check</span>
                 </div>
                 <h3 className="text-2xl font-bold text-slate-900 dark:text-white mb-2">Zpráva odeslána!</h3>
                 <p className="text-slate-600 dark:text-slate-400 mb-6">Děkuji za zprávu, ozvu se co nejdříve.</p>
                 <button onClick={() => setStatus('idle')} className="text-primary hover:underline font-bold">Odeslat další</button>
               </div>
            ) : (
              <form className="flex flex-col gap-6" onSubmit={handleSubmit}>
                <div className="grid md:grid-cols-2 gap-6">
                  <div className="flex flex-col gap-2">
                    <label htmlFor="fullname" className="text-sm font-bold text-slate-700 dark:text-slate-300">Jméno</label>
                    <input id="fullname" value={formData.fullname} onChange={handleChange} required type="text" placeholder="Jan Novák" className="bg-gray-50 dark:bg-background-dark border border-gray-200 dark:border-white/10 rounded-lg p-3 text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"/>
                  </div>
                  <div className="flex flex-col gap-2">
                    <label htmlFor="email" className="text-sm font-bold text-slate-700 dark:text-slate-300">E-mail</label>
                    <input id="email" value={formData.email} onChange={handleChange} required type="email" placeholder="jan@priklad.cz" className="bg-gray-50 dark:bg-background-dark border border-gray-200 dark:border-white/10 rounded-lg p-3 text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"/>
                  </div>
                </div>
                <div className="flex flex-col gap-2">
                  <label htmlFor="subject" className="text-sm font-bold text-slate-700 dark:text-slate-300">Předmět</label>
                  <input id="subject" value={formData.subject} onChange={handleChange} type="text" placeholder="Dotaz na projekt" className="bg-gray-50 dark:bg-background-dark border border-gray-200 dark:border-white/10 rounded-lg p-3 text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"/>
                </div>
                <div className="flex flex-col gap-2">
                  <label htmlFor="message" className="text-sm font-bold text-slate-700 dark:text-slate-300">Zpráva</label>
                  <textarea id="message" value={formData.message} onChange={handleChange} required rows={5} placeholder="Řekněte mi o svém projektu..." className="bg-gray-50 dark:bg-background-dark border border-gray-200 dark:border-white/10 rounded-lg p-3 text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-none"></textarea>
                </div>
                
                {status === 'error' && (
                  <div className="p-3 bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg text-sm">
                    Chyba: {errorMessage}
                  </div>
                )}

                <button 
                  type="submit" 
                  disabled={status === 'loading'}
                  className="mt-2 bg-primary hover:bg-primary/90 text-white font-bold py-4 rounded-lg shadow-glow hover:shadow-glow-hover transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {status === 'loading' ? 'Odesílám...' : 'Odeslat zprávu'}
                  {!status.includes('loading') && <span className="material-symbols-outlined text-[20px]">send</span>}
                </button>
              </form>
            )}
          </div>
        </div>
      </div>
    </section>
  );
};

export default Contact;