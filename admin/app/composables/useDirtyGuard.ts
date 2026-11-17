import type { Ref } from 'vue';

type DirtySource = Ref<unknown> | (() => unknown);

interface DirtyGuardOptions {
  message?: string;
}

/**
 * Hlídá neuložené změny ve formuláři.
 *
 * - `markClean()` uloží snapshot aktuálního stavu (volat po načtení a po úspěšném uložení).
 * - Dokud nebyl zavolán `markClean()`, formulář není nikdy „dirty“.
 * - Při odchodu z route s neuloženými změnami se zeptá na potvrzení,
 *   při zavření/obnovení okna zobrazí nativní dialog prohlížeče.
 */
export function useDirtyGuard(source: DirtySource, opts: DirtyGuardOptions = {}) {
  const message = opts.message ?? 'Máte neuložené změny. Opravdu odejít?';
  const getter = typeof source === 'function' ? source : () => source.value;

  const snapshot = ref<string | null>(null);

  function serialize(): string {
    try {
      return JSON.stringify(getter()) ?? '';
    } catch {
      return '';
    }
  }

  const isDirty = computed(() => snapshot.value !== null && serialize() !== snapshot.value);

  /**
   * Uloží snapshot okamžitě (aby následný router.push neprošel přes potvrzení)
   * a znovu po nextTick, aby se zahrnuly normalizace provedené child komponentami.
   */
  function markClean() {
    snapshot.value = serialize();
    nextTick(() => {
      snapshot.value = serialize();
    });
  }

  function reset() {
    snapshot.value = null;
  }

  onBeforeRouteLeave(() => {
    if (isDirty.value && !window.confirm(message)) {
      return false;
    }
  });

  function onBeforeUnload(event: BeforeUnloadEvent) {
    if (!isDirty.value) return;
    event.preventDefault();
    event.returnValue = '';
  }

  onMounted(() => {
    window.addEventListener('beforeunload', onBeforeUnload);
  });

  onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', onBeforeUnload);
  });

  return {
    isDirty,
    markClean,
    reset,
  };
}
