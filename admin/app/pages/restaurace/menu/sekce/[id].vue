<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';

const { $toast } = useNuxtApp();
const route = useRoute();
const router = useRouter();
const { formRef, validateForm } = useFormValidation();
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová sekce' : 'Detail sekce');
const breadcrumbs = ref([
  { name: 'Jídelní lístky', link: '/restaurace/menu', current: false },
  { name: 'Sekce', link: '/restaurace/menu/sekce', current: false },
  { name: pageTitle.value, link: '/restaurace/menu/sekce/pridat', current: true },
]);

const item = ref({ id: null, name: '', description: '', position: 0, sites: [] as number[] });

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;
  await client('/api/admin/food/menu-section/' + route.params.id, {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then((r) => {
      item.value = { ...r, sites: r.sites || [] };
      pageTitle.value = r.name;
      breadcrumbs.value[2] = {
        name: r.name,
        link: '/restaurace/menu/sekce/' + r.id,
        current: true,
      };
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true) {
  if (!(await validateForm())) return;
  const client = useSanctumClient();
  loading.value = true;
  await client(
    route.params.id === 'pridat'
      ? '/api/admin/food/menu-section'
      : '/api/admin/food/menu-section/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    },
  )
    .then((r) => {
      $toast.show({ summary: 'Hotovo', detail: 'Sekce uložena.', severity: 'success' });
      if (!redirect && route.params.id === 'pridat') router.push('/restaurace/menu/sekce/' + r.id);
      else if (redirect) router.push('/restaurace/menu/sekce');
      else loadItem();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit sekci.', severity: 'error' });
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
      slug="menus"
      @save="saveItem"
    />
    <Form ref="formRef" @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 lg:col-span-9">
          <LayoutContainer>
            <LayoutTitle>Sekce jídelního lístku</LayoutTitle>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <BaseFormInput
                v-model="item.name"
                label="Název"
                name="name"
                rules="required"
                placeholder="Např. Předkrmy, Polévky, Dezerty..."
              />
              <BaseFormInput v-model="item.position" label="Pořadí" type="number" name="position" />
              <BaseFormInput
                v-model="item.description"
                label="Popis"
                name="description"
                class="col-span-full"
              />
            </div>
          </LayoutContainer>
        </div>
        <div class="col-span-1 lg:sticky lg:top-24 lg:col-span-3">
          <LayoutActionsDetailBlock v-model:sites="item.sites" image-type="icon" />
        </div>
      </div>
    </Form>
  </div>
</template>
