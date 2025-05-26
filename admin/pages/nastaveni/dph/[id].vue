<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';

const toast = useToast();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const phases = ref([]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová sazba' : 'Detail sazby');

const breadcrumbs = ref([
  {
    name: 'Sazby DPH',
    link: '/nastaveni/dph',
    current: false,
  },
  {
    name: 'Nová sazba',
    link: '/nastaveni/dph',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  rate: 0 as number,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    rate: number;
  }>('/api/admin/tax-rate/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      breadcrumbs.value.pop();
      breadcrumbs.value.push({
        name: item.value.name,
        link: '/nastaveni/dph/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst sazbu DPH. Zkuste to prosím později.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    rate: number;
  }>(
    route.params.id === 'pridat' ? '/api/admin/tax-rate' : '/api/admin/tax-rate/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    },
  )
    .then(() => {
      toast.add({
        title: 'Hotovo',
        description:
          route.params.id === 'pridat'
            ? 'Sazba DPH byla úspěšně vytvořena.'
            : 'Sazba DPH byla úspěšně upravena.',
        color: 'green',
      });
      router.push('/nastaveni/dph');
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description:
          'Nepodařilo se uložit sazbu DPH. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

useHead({
  title: pageTitle.value,
});

onMounted(() => {
  if (route.params.id !== 'pridat') {
    loadItem();
  }
});
definePageMeta({
  middleware: 'sanctum:auth',
});
</script>

<template>
  <div>
    <LayoutHeader
      :title="route.params.id === 'pridat' ? 'Nová sazba' : item.name"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }]"
      slug="tax_rates"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <div class="grid grid-cols-1 gap-x-10">
        <LayoutContainer class="col-span-full w-full">
          <div class="grid grid-cols-2 gap-x-8 gap-y-4">
            <BaseFormInput
              v-model="item.name"
              label="Název"
              type="text"
              name="name"
              rules="required|min:3"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="item.rate"
              label="Sazba"
              type="number"
              name="rate"
              min="0"
              rules="required"
              class="col-span-1"
            />
          </div>
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
