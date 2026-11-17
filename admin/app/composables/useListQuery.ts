import type { Ref } from 'vue';
import type { LocationQuery, LocationQueryRaw } from 'vue-router';

interface ListQueryOptions {
  /** Injektovaný searchString z layoutu – při obnovení z URL se do něj propíše hledaný výraz. */
  searchString?: Ref<string | null | undefined>;
}

function parseValue(raw: unknown, defaultValue: unknown): unknown {
  const value = Array.isArray(raw) ? raw[0] : raw;
  if (value === null || value === undefined) return defaultValue;
  const str = String(value);

  if (typeof defaultValue === 'number') {
    const num = Number(str);
    return Number.isFinite(num) ? num : defaultValue;
  }
  if (typeof defaultValue === 'boolean') {
    return str === 'true' || str === '1';
  }
  if (defaultValue !== null && typeof defaultValue === 'object') {
    try {
      const parsed = JSON.parse(str);
      return parsed && typeof parsed === 'object' ? parsed : defaultValue;
    } catch {
      return defaultValue;
    }
  }
  return str;
}

function serializeValue(value: unknown): string | null {
  if (value === null || value === undefined || value === '') return null;
  if (typeof value === 'object') {
    const json = JSON.stringify(value);
    return json === '{}' || json === '[]' ? null : json;
  }
  return String(value);
}

/**
 * Stav seznamu (stránkování, řazení, hledání, filtry) synchronizovaný s `route.query`.
 *
 * Při inicializaci se hodnoty obnoví z URL (čísla/boolean/objekty se parsují podle
 * typu výchozí hodnoty), každá změna se propíše do URL přes `router.replace`.
 * Do URL se zapisují jen hodnoty odlišné od výchozích.
 */
export function useListQuery<T extends Record<string, unknown>>(
  defaults: T,
  options: ListQueryOptions = {},
): Ref<T> {
  const route = useRoute();
  const router = useRouter();

  function fromRoute(query: LocationQuery): T {
    const result = JSON.parse(JSON.stringify(defaults)) as T;
    (Object.keys(defaults) as (keyof T)[]).forEach((key) => {
      if (query[key as string] !== undefined) {
        result[key] = parseValue(query[key as string], defaults[key]) as T[keyof T];
      }
    });
    return result;
  }

  const listQuery = ref(fromRoute(route.query)) as Ref<T>;
  const ownPath = route.path;

  if (options.searchString && listQuery.value.search) {
    options.searchString.value = String(listQuery.value.search);
  }

  watch(
    listQuery,
    (value) => {
      // Stránka už mohla být opuštěna (např. doběhl request po navigaci jinam).
      if (router.currentRoute.value.path !== ownPath) return;

      const ownKeys = Object.keys(defaults);
      const nextQuery: LocationQueryRaw = Object.fromEntries(
        Object.entries(router.currentRoute.value.query).filter(([key]) => !ownKeys.includes(key)),
      );
      (ownKeys as (keyof T & string)[]).forEach((key) => {
        const serialized = serializeValue(value[key]);
        if (serialized !== null && serialized !== serializeValue(defaults[key])) {
          nextQuery[key] = serialized;
        }
      });

      const current = router.currentRoute.value;
      if (JSON.stringify(nextQuery) === JSON.stringify(current.query)) return;

      router.replace({ query: nextQuery, hash: current.hash });
    },
    { deep: true },
  );

  return listQuery;
}
