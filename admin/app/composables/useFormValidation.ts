import type { Ref } from 'vue';
import type { FormContext } from 'vee-validate';

interface ValidationTab {
  link: string;
  current?: boolean;
  errors?: number;
}

/**
 * Najde záložku (panel s `data-tab-panel="#hash"`), ve které je pole daného jména.
 */
function findTabOfField(fieldName: string): string | null {
  if (typeof document === 'undefined') return null;
  const escaped = typeof CSS !== 'undefined' && CSS.escape ? CSS.escape(fieldName) : fieldName;
  const el = document.querySelector(
    `[data-tab-panel] [name="${escaped}"], [data-tab-panel] #${escaped}`,
  );
  const panel = el?.closest('[data-tab-panel]') as HTMLElement | null;
  return panel?.dataset.tabPanel ?? null;
}

export function useFormValidation() {
  const { $toast } = useNuxtApp();
  const router = useRouter();
  const formRef = ref<FormContext | null>(null);

  /**
   * Nastaví `tab.errors` podle chyb ve formuláři a při chybě přepne
   * na první záložku s chybou (pokud aktuální záložka žádnou chybu nemá).
   */
  function applyTabErrors(tabs: Ref<ValidationTab[]>, errors: Record<string, string | undefined>) {
    const counts: Record<string, number> = {};
    Object.entries(errors).forEach(([field, message]) => {
      if (!message) return;
      let link: string | null = null;
      try {
        link = findTabOfField(field);
      } catch {
        link = null;
      }
      if (link) counts[link] = (counts[link] ?? 0) + 1;
    });

    tabs.value.forEach((tab) => {
      tab.errors = counts[tab.link] ?? 0;
    });

    const current = tabs.value.find((tab) => tab.current);
    if (current && counts[current.link]) return;

    const firstWithError = tabs.value.find((tab) => counts[tab.link]);
    if (firstWithError) {
      router.replace({ hash: firstWithError.link });
    }
  }

  async function validateForm(tabs?: Ref<ValidationTab[]>): Promise<boolean> {
    if (!formRef.value) return true;

    const { valid, errors } = await formRef.value.validate();

    if (tabs?.value?.length) {
      applyTabErrors(tabs, valid ? {} : errors);
    }

    if (!valid) {
      const errorFields = Object.entries(errors)
        .map(([_, message]) => message)
        .filter(Boolean);

      $toast.show({
        summary: 'Formulář obsahuje chyby',
        detail: errorFields.length
          ? errorFields.join(', ')
          : 'Zkontrolujte prosím vyplnění všech povinných polí.',
        severity: 'error',
      });
    }

    return valid;
  }

  return {
    formRef,
    validateForm,
  };
}
