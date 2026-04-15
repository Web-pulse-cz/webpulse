import type { FormContext } from 'vee-validate';

export function useFormValidation() {
  const { $toast } = useNuxtApp();
  const formRef = ref<FormContext | null>(null);

  async function validateForm(): Promise<boolean> {
    if (!formRef.value) return true;

    const { valid, errors } = await formRef.value.validate();

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
