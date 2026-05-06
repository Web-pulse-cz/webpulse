import { computed, inject, ref, watch, type Ref } from 'vue';
import _ from 'lodash';

interface PreferenceResponse {
  visible_columns: string[] | null;
  per_page: number;
}

interface UseTablePreferencesOptions {
  defaultPerPage?: number;
  defaultVisibleColumns?: string[];
}

const cache = new Map<string, PreferenceResponse | null>();

function cacheKey(slug: string, siteHash: string): string {
  return `${slug}::${siteHash}`;
}

export function useTablePreferences(slug: string, options: UseTablePreferencesOptions = {}) {
  const defaultPerPage = options.defaultPerPage ?? 25;

  const selectedSiteHash = inject<Ref<string>>('selectedSiteHash', ref(''));

  const visibleColumns = ref<string[] | null>(options.defaultVisibleColumns ?? null);
  const perPage = ref<number>(defaultPerPage);
  const loaded = ref(false);

  async function load(): Promise<void> {
    if (!slug) return;

    const key = cacheKey(slug, selectedSiteHash.value);
    if (cache.has(key)) {
      applyResponse(cache.get(key) ?? null);
      loaded.value = true;
      return;
    }

    const client = useSanctumClient();
    try {
      const response = await client<PreferenceResponse | null>('/api/admin/table-preferences', {
        method: 'GET',
        query: { slug },
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-Site-Hash': selectedSiteHash.value,
        },
      });
      cache.set(key, response ?? null);
      applyResponse(response ?? null);
    } catch {
      // No preference yet or fetch failed — keep defaults.
    } finally {
      loaded.value = true;
    }
  }

  function applyResponse(response: PreferenceResponse | null): void {
    if (!response) {
      visibleColumns.value = options.defaultVisibleColumns ?? null;
      perPage.value = defaultPerPage;
      return;
    }
    visibleColumns.value = response.visible_columns ?? null;
    perPage.value = response.per_page ?? defaultPerPage;
  }

  const persist = _.debounce(async () => {
    if (!slug) return;
    const client = useSanctumClient();
    try {
      const response = await client<PreferenceResponse>('/api/admin/table-preferences', {
        method: 'POST',
        body: JSON.stringify({
          slug,
          visible_columns: visibleColumns.value,
          per_page: perPage.value,
        }),
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-Site-Hash': selectedSiteHash.value,
        },
      });
      cache.set(cacheKey(slug, selectedSiteHash.value), response);
    } catch {
      // Swallow — UI already reflects the change locally.
    }
  }, 400);

  async function reset(): Promise<void> {
    visibleColumns.value = options.defaultVisibleColumns ?? null;
    perPage.value = defaultPerPage;
    persist.cancel();

    const client = useSanctumClient();
    try {
      await client('/api/admin/table-preferences', {
        method: 'DELETE',
        query: { slug },
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-Site-Hash': selectedSiteHash.value,
        },
      });
      cache.delete(cacheKey(slug, selectedSiteHash.value));
    } catch {
      // Ignore network errors.
    }
  }

  function isColumnVisible(key: string): boolean {
    if (!visibleColumns.value) return true;
    return visibleColumns.value.includes(key);
  }

  function setVisibleColumns(keys: string[]): void {
    visibleColumns.value = keys;
    persist();
  }

  function setPerPage(value: number): void {
    perPage.value = value;
    persist();
  }

  watch(selectedSiteHash, () => {
    loaded.value = false;
    load();
  });

  return {
    visibleColumns,
    perPage,
    loaded,
    load,
    reset,
    isColumnVisible,
    setVisibleColumns,
    setPerPage,
    isCustomized: computed(() => visibleColumns.value !== null || perPage.value !== defaultPerPage),
  };
}
