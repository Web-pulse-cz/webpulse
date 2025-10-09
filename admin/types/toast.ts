export type ToastSeverity = 'info' | 'success' | 'warning' | 'error' | 'neutral';

export interface ToastOptions {
  summary: string;
  detail?: string;
  severity?: ToastSeverity;
  /** v ms, default 4000 */
  duration?: number;
  /** vlastní id pokud chceš něco řídit ručně */
  id?: string;
}

export interface Toast extends Required<Omit<ToastOptions, 'id'>> {
  id: string;
  createdAt: number;
  remaining: number;
  /** interní stav */
  _timer?: ReturnType<typeof setTimeout> | null;
  _endAt?: number | null;
  _paused?: boolean;
}
