import { defineStore } from 'pinia';
import type { Toast, ToastOptions, ToastSeverity } from '~/../types/toast';

const DEFAULT_DURATION = 5000;
const DEFAULT_severity: ToastSeverity = 'info';

export const useToastStore = defineStore('toast', {
  state: () => ({
    items: [] as Toast[],
    maxVisible: 5,
    position: 'top-right' as 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left',
  }),
  actions: {
    _color(severity: ToastSeverity) {
      // mapuje na barvy z tvého tailwind.config.ts
      switch (severity) {
        case 'success':
          return { base: 'bg-success', ring: 'ring-successLight', text: 'text-white' };
        case 'warning':
          // warning je spíš světlý, ať je kontrast OK
          return { base: 'bg-warningLight', ring: 'ring-warning', text: 'text-gray-900' };
        case 'error':
          return { base: 'bg-danger', ring: 'ring-dangerLight', text: 'text-white' };
        case 'neutral':
          return { base: 'bg-grayDark', ring: 'ring-grayLight', text: 'text-white' };
        case 'info':
        default:
          return { base: 'bg-primaryCustom', ring: 'ring-primaryLight', text: 'text-white' };
      }
    },

    show(opts: ToastOptions) {
      const id = opts.id ?? crypto.randomUUID();
      const severity = opts.severity ?? DEFAULT_severity;
      const duration = Math.max(1000, opts.duration ?? DEFAULT_DURATION);

      // omezení staku
      if (this.items.length && this.items.length >= this.maxVisible) {
        // odstraníme nejstarší
        this.remove(this.items[0].id);
      }

      const t: Toast = {
        id,
        summary: opts.summary,
        detail: opts.detail ?? '',
        severity,
        duration,
        createdAt: Date.now(),
        remaining: duration,
        _timer: null,
        _endAt: null,
        _paused: false,
      };

      this.items.push(t);
      this._startTimer(id);

      return id;
    },

    success(summary: string, detail = '', duration?: number) {
      return this.show({ summary, detail, duration, severity: 'success' });
    },
    info(summary: string, detail = '', duration?: number) {
      return this.show({ summary, detail, duration, severity: 'info' });
    },
    warning(summary: string, detail = '', duration?: number) {
      return this.show({ summary, detail, duration, severity: 'warning' });
    },
    error(summary: string, detail = '', duration?: number) {
      return this.show({ summary, detail, duration, severity: 'error' });
    },

    remove(id: string) {
      const i = this.items.findIndex((t) => t.id === id);
      if (i !== -1) {
        const t = this.items[i];
        if (t._timer) clearTimeout(t._timer);
        this.items.splice(i, 1);
      }
    },

    clear() {
      this.items.forEach((t) => t._timer && clearTimeout(t._timer));
      this.items = [];
    },

    pause(id: string) {
      const t = this.items.find((x) => x.id === id);
      if (!t || t._paused) return;
      t._paused = true;
      if (t._timer) clearTimeout(t._timer);
      if (t._endAt) t.remaining = Math.max(0, t._endAt - Date.now());
      t._endAt = null;
      t._timer = null;
    },

    resume(id: string) {
      const t = this.items.find((x) => x.id === id);
      if (!t || !t._paused) return;
      t._paused = false;
      this._startTimer(id);
    },

    _startTimer(id: string) {
      const t = this.items.find((x) => x.id === id);
      if (!t) return;
      if (t._timer) clearTimeout(t._timer);
      t._endAt = Date.now() + t.remaining;
      t._timer = setTimeout(() => this.remove(id), t.remaining);
    },

    colorClasses(variant: ToastVariant) {
      const c = this._color(variant);
      return { base: c.base, ring: c.ring, text: c.text };
    },
  },
});
