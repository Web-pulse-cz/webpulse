<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';

const { $toast } = useNuxtApp();
const route = useRoute();
const router = useRouter();
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová šablona směny' : 'Detail šablony');
const breadcrumbs = ref([
  { name: 'Směny', link: '/smeny', current: false },
  { name: 'Šablony', link: '/smeny/sablony', current: false },
  { name: pageTitle.value, link: '/smeny/sablony/pridat', current: true },
]);

const item = ref({
  id: null,
  name: '',
  color: '#6366f1',
  start_time: '08:00',
  end_time: '16:00',
  break_minutes: 30,
  note: '',
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;
  await client('/api/admin/shift/template/' + route.params.id, {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then((r) => {
      item.value = r;
      pageTitle.value = r.name;
      breadcrumbs.value[2] = { name: r.name, link: '/smeny/sablony/' + r.id, current: true };
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true) {
  const client = useSanctumClient();
  loading.value = true;
  await client(
    route.params.id === 'pridat'
      ? '/api/admin/shift/template'
      : '/api/admin/shift/template/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    },
  )
    .then((r) => {
      $toast.show({ summary: 'Hotovo', detail: 'Šablona uložena.', severity: 'success' });
      if (!redirect && route.params.id === 'pridat') router.push('/smeny/sablony/' + r.id);
      else if (redirect) router.push('/smeny/sablony');
      else loadItem();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit šablonu.', severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

useHead({ title: pageTitle.value });
onMounted(() => {
  if (route.params.id !== 'pridat') loadItem();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-20">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="shifts"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <LayoutContainer>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
          <BaseFormInput v-model="item.name" label="Název" name="name" rules="required" />
          <BaseFormColorPicker v-model="item.color" label="Barva" name="color" />
          <BaseFormInput
            v-model="item.start_time"
            label="Začátek"
            type="time"
            name="start_time"
            rules="required"
          />
          <BaseFormInput
            v-model="item.end_time"
            label="Konec"
            type="time"
            name="end_time"
            rules="required"
          />
          <BaseFormInput
            v-model="item.break_minutes"
            label="Přestávka (min)"
            type="number"
            name="break_minutes"
          />
          <BaseFormTextarea
            v-model="item.note"
            label="Poznámka"
            name="note"
            rows="2"
            class="col-span-full"
          />
        </div>
      </LayoutContainer>
    </Form>
  </div>
</template>
