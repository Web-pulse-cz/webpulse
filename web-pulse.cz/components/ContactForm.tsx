import React, { useState } from 'react';
import { Locale, DemandPayload } from '../types';
import { apiService } from '../services/apiService';

interface ContactFormProps {
  currentLocale: Locale;
}

export const ContactForm: React.FC<ContactFormProps> = ({ currentLocale }) => {
  const [formData, setFormData] = useState<DemandPayload>({
    fullname: '',
    phone: '',
    email: '',
    text: '',
  });
  const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setStatus('loading');
    try {
      await apiService.postDemand(currentLocale, formData);
      setStatus('success');
      setFormData({ fullname: '', phone: '', email: '', text: '' });
      setTimeout(() => setStatus('idle'), 5000);
    } catch (error) {
      console.error(error);
      setStatus('error');
    }
  };

  return (
    <section className="py-24 px-4 sm:px-6 lg:px-8" id="contact">
      <div className="max-w-7xl mx-auto rounded-3xl bg-slate-900 dark:bg-primary overflow-hidden relative">
        <div className="absolute top-0 right-0 -mt-20 -mr-20 w-96 h-96 bg-primary dark:bg-white rounded-full opacity-20 blur-3xl"></div>
        
        <div className="relative z-10 py-20 px-8 md:px-20 grid lg:grid-cols-2 gap-12 items-center">
          <div className="max-w-2xl">
            <h2 className="font-display text-3xl md:text-5xl font-bold text-white mb-6">
              Ready to take control of your <span className="text-primary dark:text-slate-900">digital future?</span>
            </h2>
            <p className="text-slate-400 dark:text-blue-100 text-lg mb-8">
              Join the forward-thinking companies scaling with Web-pulse technology. Send us a message and we'll get back to you shortly.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 mb-8">
              <div className="flex items-center gap-2 text-sm text-slate-400 dark:text-blue-100">
                <span className="material-icons-round text-primary dark:text-white">check</span> 
                Free Consultation
              </div>
              <div className="flex items-center gap-2 text-sm text-slate-400 dark:text-blue-100">
                <span className="material-icons-round text-primary dark:text-white">check</span> 
                Technical Audit
              </div>
            </div>
          </div>

          <div className="bg-white/5 backdrop-blur-sm p-8 rounded-2xl border border-white/10">
            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label htmlFor="fullname" className="block text-sm font-medium text-slate-300 dark:text-white mb-1">Full Name</label>
                <input
                  type="text"
                  id="fullname"
                  name="fullname"
                  required
                  value={formData.fullname}
                  onChange={handleChange}
                  className="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"
                  placeholder="John Doe"
                />
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label htmlFor="email" className="block text-sm font-medium text-slate-300 dark:text-white mb-1">Email</label>
                  <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    value={formData.email}
                    onChange={handleChange}
                    className="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"
                    placeholder="john@example.com"
                  />
                </div>
                <div>
                   <label htmlFor="phone" className="block text-sm font-medium text-slate-300 dark:text-white mb-1">Phone</label>
                  <input
                    type="tel"
                    id="phone"
                    name="phone"
                    required
                    value={formData.phone}
                    onChange={handleChange}
                    className="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"
                    placeholder="+420 123 456 789"
                  />
                </div>
              </div>
              <div>
                <label htmlFor="text" className="block text-sm font-medium text-slate-300 dark:text-white mb-1">Message</label>
                <textarea
                  id="text"
                  name="text"
                  required
                  rows={4}
                  value={formData.text}
                  onChange={handleChange}
                  className="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"
                  placeholder="Tell us about your project..."
                ></textarea>
              </div>

              <button
                type="submit"
                disabled={status === 'loading'}
                className="w-full bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-bold px-8 py-4 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition text-center shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              >
                {status === 'loading' ? 'Sending...' : 'Send Message'}
                {status !== 'loading' && <span className="material-icons-round text-sm">arrow_forward</span>}
              </button>

              {status === 'success' && (
                <div className="p-4 bg-green-500/20 border border-green-500/50 rounded-lg text-green-200 text-center">
                  Message sent successfully! We will contact you soon.
                </div>
              )}
              {status === 'error' && (
                 <div className="p-4 bg-red-500/20 border border-red-500/50 rounded-lg text-red-200 text-center">
                   Something went wrong. Please try again later.
                 </div>
              )}
            </form>
          </div>
        </div>
      </div>
    </section>
  );
};