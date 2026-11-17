import DOMPurify from 'isomorphic-dompurify';

/**
 * Sanitizes an HTML string against XSS. Inline `style` attributes are kept,
 * but `<style>` tags are removed so rendered content cannot restyle the admin.
 */
export function sanitizeHtml(html: string | null | undefined): string {
  if (!html) return '';
  return DOMPurify.sanitize(String(html), { FORBID_TAGS: ['style'] });
}
