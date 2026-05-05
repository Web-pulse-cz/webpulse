<script setup lang="ts">
import { ref, onMounted, computed, inject, watch, type Ref } from 'vue';

const props = withDefaults(
  defineProps<{
    widgetKey: string;
    title: string;
    icon: unknown;
    endpoint: string;
    link: string;
    color: string;
    emptyLabel?: string;
    columns?: any[];
    enums?: Record<string, Record<string | number, string>>;
    actions?: any[];
    dateField?: string;
    permissionSlug?: string;
    paginate?: boolean;
    dataKey?: string;
  }>(),
  {
    columns: () => [{ key: 'name', name: 'Název', type: 'text' }],
    enums: () => ({}),
    actions: () => [{ type: 'edit' }],
    dateField: 'updated_at',
    permissionSlug: '',
    paginate: true,
    dataKey: '',
  },
);

const selectedSiteHash = inject<Ref<string>>('selectedSiteHash', ref(''));

const total = ref(0);
const loading = ref(false);
const error = ref(false);
const items = ref<{
  data: any[];
  total: number;
  currentPage: number;
  perPage: number;
  lastPage: number;
}>({
  data: [],
  total: 0,
  currentPage: 1,
  perPage: 5,
  lastPage: 1,
});

const orderBy = computed(() => props.dateField || 'updated_at');

const tableActions = computed(() =>
  props.actions.map((a) => ({ ...a, path: a.path ?? props.link })),
);

async function loadItems() {
  if (!selectedSiteHash.value) return;
  loading.value = true;
  error.value = false;
  const client = useSanctumClient();

  const query: Record<string, any> = props.paginate
    ? { paginate: 5, page: 1, orderBy: orderBy.value, orderWay: 'desc' }
    : {};

  try {
    const response: any = await client(props.endpoint, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Site-Hash': selectedSiteHash.value,
      },
      query,
    });

    if (props.dataKey) {
      const raw = response?.[props.dataKey];
      const arr = Array.isArray(raw) ? raw : (raw?.data ?? []);
      total.value = arr.length;
      items.value = {
        data: arr,
        total: arr.length,
        currentPage: 1,
        perPage: arr.length,
        lastPage: 1,
      };
    } else {
      total.value = response?.total ?? 0;
      items.value = {
        data: response?.data ?? [],
        total: response?.total ?? 0,
        currentPage: response?.currentPage ?? 1,
        perPage: response?.perPage ?? 5,
        lastPage: response?.lastPage ?? 1,
      };
    }
  } catch (_) {
    error.value = true;
    total.value = 0;
    items.value = { data: [], total: 0, currentPage: 1, perPage: 5, lastPage: 1 };
  } finally {
    loading.value = false;
  }
}

onMounted(loadItems);
watch(selectedSiteHash, () => loadItems());
</script>

<template>
  <DashboardWidgetBaseCard :title="title" :icon="icon" :color="color" :count="total" :link="link">
    <BaseTable
      compact
      :items="items"
      :columns="columns"
      :enums="enums"
      :actions="tableActions"
      :loading="loading"
      :error="error"
      :singular="title"
      :plural="title"
      :slug="permissionSlug"
    />
  </DashboardWidgetBaseCard>
</template>
