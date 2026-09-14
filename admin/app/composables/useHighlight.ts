function escapeHtml(value: string): string {
  return value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function escapeRegExp(value: string): string {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function markMatches(escapedText: string, query: string): string {
  const trimmed = query?.trim();
  if (!trimmed) return escapedText;

  const pattern = new RegExp(`(${escapeRegExp(escapeHtml(trimmed))})`, 'gi');
  return escapedText.replace(
    pattern,
    '<mark class="rounded bg-amber-200 px-0.5 text-slate-900">$1</mark>',
  );
}

// Matches cross-references to other rule sections, e.g. "802.04.B", "811.F.1", "812", "A.01.B".
const SECTION_REF_RE =
  /\b((?:80[0-9]|81[0-3])(?:\.\d{2})?(?:\.[A-Z])?(?:\.\d+)?|[ABCDFG]\.\d{2}(?:\.[A-Z])?(?:\.\d+)?)\b/g;

function baseSectionId(token: string): string {
  return /^[0-9]/.test(token) ? token.slice(0, 3) : token[0];
}

export function useHighlight() {
  function highlightText(text: string, query: string): string {
    return markMatches(escapeHtml(text ?? ''), query);
  }

  // Same as highlightText, but also turns rule cross-references (e.g. "811.F.1") into
  // clickable links pointing at #pravidlo-<sectionId>, handled by the page's click delegation.
  function renderRuleText(text: string, query: string): string {
    const parts = (text ?? '').split(SECTION_REF_RE);
    return parts
      .map((part, idx) => {
        // Odd indices are the captured section references from SECTION_REF_RE.
        if (idx % 2 === 1) {
          const sectionId = baseSectionId(part);
          return `<a href="#pravidlo-${sectionId}" data-ref-section="${sectionId}" class="font-semibold text-emerald-700 underline decoration-emerald-300 underline-offset-2 hover:text-emerald-800">${part}</a>`;
        }
        return markMatches(escapeHtml(part), query);
      })
      .join('');
  }

  return { highlightText, renderRuleText };
}
