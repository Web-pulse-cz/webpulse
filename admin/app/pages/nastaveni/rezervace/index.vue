<script setup lang="ts">
import { ref, inject } from 'vue';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Nastavení rezervací');
const loading = ref(false);
const error = ref(false);
const items = ref({ data: [], total: 0, perPage: 25, currentPage: 1, lastPage: 1 });
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const breadcrumbs = ref([{ name: pageTitle.value, link: '/nastaveni/rezervace', current: true }]);

const tableQuery = useListQuery({
  search: null as string | null,
  paginate: 25,
  page: 1,
  orderBy: 'service_name',
  orderWay: 'asc',
});

async function loadItems() {
  error.value = false;
  loading.value = true;
  const client = useSanctumClient();
  await client('/api/admin/service-booking-setting', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      const d = Array.isArray(r) ? r : r?.data || [];
      const mapped = d.map((row: any) => ({
        id: row.service_id,
        service_name: row.service_name,
        work_start: row.booking_setting?.work_start ?? null,
        work_end: row.booking_setting?.work_end ?? null,
        slot_duration_minutes: row.booking_setting?.slot_duration_minutes ?? null,
        active: row.booking_setting ? !!row.booking_setting.active : false,
      }));
      items.value = {
        data: mapped,
        total: mapped.length,
        perPage: mapped.length || 1,
        currentPage: 1,
        lastPage: 1,
      };
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst nastavení rezervací. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

function updateSort(column: string) {
  if (tableQuery.value.orderBy === column) {
    tableQuery.value.orderWay = tableQuery.value.orderWay === 'asc' ? 'desc' : 'asc';
  } else {
    tableQuery.value.orderBy = column;
    tableQuery.value.orderWay = 'asc';
  }
}
function updatePage(page: number) {
  tableQuery.value.page = page;
}
function updatePerPage(perPage: number) {
  tableQuery.value.paginate = perPage;
  tableQuery.value.page = 1;
}

watch(selectedSiteHash, () => loadItems());

useHead({
  title: pageTitle,
});
onMounted(() => {
  loadItems();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div>
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="service_booking_settings" />

    <BaseTable
      :items="items"
      :columns="[
        {
          key: 'service_name',
          name: 'Služba',
          type: 'text',
          width: 200,
          hidden: false,
          sortable: true,
        },
        {
          key: 'work_start',
          name: 'Pracovní doba od',
          type: 'text',
          width: 80,
          hidden: false,
          sortable: false,
        },
        {
          key: 'work_end',
          name: 'Pracovní doba do',
          type: 'text',
          width: 80,
          hidden: false,
          sortable: false,
        },
        {
          key: 'slot_duration_minutes',
          name: 'Délka slotu (min)',
          type: 'text',
          width: 100,
          hidden: false,
          sortable: false,
        },
        {
          key: 'active',
          name: 'Aktivní',
          type: 'status',
          width: 80,
          hidden: false,
          sortable: false,
        },
      ]"
      :actions="[{ type: 'edit' }]"
      :loading="loading"
      :error="error"
      singular="Nastavení"
      plural="Nastavení rezervací"
      :query="tableQuery"
      slug="service_booking_settings"
      @update-sort="updateSort"
      @retry="loadItems"
      @update-page="updatePage"
      @update-per-page="updatePerPage"
    />
  </div>
</template>
