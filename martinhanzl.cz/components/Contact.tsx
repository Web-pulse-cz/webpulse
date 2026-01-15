import React, { useState } from 'react';
import { sendDemand } from '../services/api';

const Contact: React.FC = () => {
  const [formData, setFormData] = useState({
    fullname: '',
    email: '',
    phone: '',
    message: '',
    subject: '' // Kept for UI, but might map to 'text' in payload
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
      // Map form state to API payload requirements
      // The API requires: fullname, phone, email, text
      const payload = {
        fullname: formData.fullname,
        email: formData.email,
        phone: formData.phone || '',
        text: `Subject: ${formData.subject}\n\n${formData.message}`,
      };

      await sendDemand(payload);
      setStatus('success');
      setFormData({ fullname: '', email: '', phone: '', message: '', subject: '' });
    } catch (error: any) {
      setStatus('error');
      setErrorMessage(error.message || "Something went wrong.");
    }
  };

  return (
    <section id="contact" className="py-24 bg-white dark:bg-[#0b0e11] border-t border-gray-200 dark:border-white/5">
      <div className="max-w-6xl mx-auto px-6">
        <div className="grid lg:grid-cols-2 gap-16">
          <div className="flex flex-col justify-center">
            <div className="inline-block px-3 py-1 rounded bg-primary/10 text-primary text-sm font-bold mb-6 w-fit">
              Get In Touch
            </div>
            <h2 className="text-4xl md:text-5xl font-bold mb-6 text-slate-900 dark:text-white">
              Let's build something <br /> <span className="text-primary">extraordinary</span>.
            </h2>
            <p className="text-lg text-slate-600 dark:text-slate-400 mb-10 max-w-md">
              Have a project in mind or want to discuss modern web architecture? I'm always open to new opportunities.
            </p>
            <div className="flex flex-col gap-6">
              <a
                href="mailto:hello@martinhanzl.dev"
                className="flex items-center gap-4 text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary transition-colors group"
              >
                <div className="w-12 h-12 rounded-full bg-gray-100 dark:bg-surface-dark flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors">
                  <span className="material-symbols-outlined">mail</span>
                </div>
                <span className="text-lg font-medium">hello@martinhanzl.dev</span>
              </a>
            </div>
          </div>

          <div className="bg-gray-50 dark:bg-surface-dark p-8 md:p-10 rounded-2xl border border-gray-200 dark:border-white/5 shadow-2xl shadow-black/5 dark:shadow-black/20">
            {status === 'success' ? (
              <div className="h-full flex flex-col items-center justify-center text-center p-8">
                <div className="w-20 h-20 bg-primary/20 text-primary rounded-full flex items-center justify-center mb-6">
                   <span className="material-symbols-outlined text-[40px]">check</span>
                </div>
                <h3 className="text-2xl font-bold text-slate-900 dark:text-white mb-2">Message Sent!</h3>
                <p className="text-slate-600 dark:text-slate-400">I will get back to you as soon as possible.</p>
                <button onClick={() => setStatus('idle')} className="mt-6 text-primary font-bold hover:underline">Send another</button>
              </div>
            ) : (
              <form className="flex flex-col gap-6" onSubmit={handleSubmit}>
                <div className="grid md:grid-cols-2 gap-6">
                  <div className="flex flex-col gap-2">
                    <label className="text-sm font-bold text-slate-700 dark:text-slate-300" htmlFor="fullname">
                      Name
                    </label>
                    <input
                      id="fullname"
                      type="text"
                      required
                      value={formData.fullname}
                      onChange={handleChange}
                      placeholder="John Doe"
                      className="bg-white dark:bg-background-dark border border-gray-300 dark:border-white/10 rounded-lg p-3 text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                    />
                  </div>
                  <div className="flex flex-col gap-2">
                    <label className="text-sm font-bold text-slate-700 dark:text-slate-300" htmlFor="email">
                      Email
                    </label>
                    <input
                      id="email"
                      type="email"
                      required
                      value={formData.email}
                      onChange={handleChange}
                      placeholder="john@example.com"
                      className="bg-white dark:bg-background-dark border border-gray-300 dark:border-white/10 rounded-lg p-3 text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                    />
                  </div>
                </div>
                <div className="flex flex-col gap-2">
                  <label className="text-sm font-bold text-slate-700 dark:text-slate-300" htmlFor="phone">
                    Phone (Optional)
                  </label>
                  <input
                    id="phone"
                    type="tel"
                    value={formData.phone}
                    onChange={handleChange}
                    placeholder="+420 123 456 789"
                    className="bg-white dark:bg-background-dark border border-gray-300 dark:border-white/10 rounded-lg p-3 text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                  />
                </div>
                <div className="flex flex-col gap-2">
                  <label className="text-sm font-bold text-slate-700 dark:text-slate-300" htmlFor="subject">
                    Subject
                  </label>
                  <input
                    id="subject"
                    type="text"
                    required
                    value={formData.subject}
                    onChange={handleChange}
                    placeholder="Project Inquiry"
                    className="bg-white dark:bg-background-dark border border-gray-300 dark:border-white/10 rounded-lg p-3 text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                  />
                </div>
                <div className="flex flex-col gap-2">
                  <label className="text-sm font-bold text-slate-700 dark:text-slate-300" htmlFor="message">
                    Message
                  </label>
                  <textarea
                    id="message"
                    required
                    rows={5}
                    value={formData.message}
                    onChange={handleChange}
                    placeholder="Tell me about your project..."
                    className="bg-white dark:bg-background-dark border border-gray-300 dark:border-white/10 rounded-lg p-3 text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-none"
                  ></textarea>
                </div>
                
                {status === 'error' && (
                  <div className="p-3 bg-red-500/10 border border-red-500/20 text-red-500 text-sm rounded-lg">
                    {errorMessage}
                  </div>
                )}

                <button
                  type="submit"
                  disabled={status === 'loading'}
                  className="mt-2 bg-primary hover:bg-primary/90 disabled:bg-slate-500 text-white font-bold py-4 rounded-lg shadow-glow hover:shadow-glow-hover transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center gap-2"
                >
                  {status === 'loading' ? (
                     <span className="inline-block w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                  ) : (
                    <>
                      Send Message
                      <span className="material-symbols-outlined text-[20px]">send</span>
                    </>
                  )}
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
