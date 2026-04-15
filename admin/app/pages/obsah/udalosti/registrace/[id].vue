<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';
import { MapPinIcon, PencilSquareIcon, UserIcon } from '@heroicons/vue/24/outline';
import { useCountryStore } from '~~/stores/countryStore';

const { $toast } = useNuxtApp();

const { formRef, validateForm } = useFormValidation();

const route = useRoute();
const router = useRouter();

const countryStore = useCountryStore();
const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová registrace' : 'Detail registrace');

const breadcrumbs = ref([
  {
    name: 'Události a akce',
    link: '/obsah/udalosti',
    current: false,
  },
  {
    name: 'Registrace',
    link: '/obsah/udalosti/registrace',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/udalosti/registrace/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  event_id: null as number | null,
  firstname: '' as string,
  lastname: '' as string,
  email: '' as string,
  phone: '' as string,
  note: '' as string,
  ico: '' as string,
  dic: '' as string,
  company: '' as string,
  street: '' as string,
  city: '' as string,
  zip: '' as string,
  country_id: 1 as number,
  is_paid: false as boolean,
  event: {
    id: null as number | null,
    name: '' as string,
  },
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    event_id: number | null;
    firstname: string;
    lastname: string;
    email: string;
    phone: string;
    note: string;
    ico: string;
    dic: string;
    company: string;
    street: string;
    city: string;
    zip: string;
    country_id: number;
    is_paid: boolean;
    event: {
      id: number | null;
      name: string;
    };
  }>('/api/admin/event/registration/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      breadcrumbs.value.pop();
      pageTitle.value = `Událost ${item.value.event.name} ─ ${item.value.firstname} ${item.value.lastname}`;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/obsah/udalosti/registrace/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst registraci. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true as boolean) {
  if (!(await validateForm())) return;

  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    event_id: number | null;
    firstname: string;
    lastname: string;
    email: string;
    phone: string;
    note: string;
    ico: string;
    dic: string;
    company: string;
    street: string;
    city: string;
    zip: string;
    country_id: number;
    is_paid: boolean;
    event: {
      id: number | null;
      name: string;
    };
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/event/registration'
      : '/api/admin/event/registration/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    },
  )
    .then((response) => {
      $toast.show({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Registrace byla úspěšně vytvořena.'
            : 'Registrace byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/udalosti/registrace/' + response.id);
      } else if (redirect) {
        router.push('/obsah/udalosti/registrace');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit registraci. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
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
  <div class="space-y-6 pb-12">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="events"
      @save="saveItem"
    />

    <Form ref="formRef" @submit="saveItem">
      <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-8">
          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <UserIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Informace o účastníkovi</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
              <BaseFormInput
                v-model="item.firstname"
                name="firstname"
                label="Jméno"
                type="text"
                rules="required|min:3"
                placeholder="Např. Jan"
              />
              <BaseFormInput
                v-model="item.lastname"
                name="lastname"
                label="Příjmení"
                type="text"
                rules="required|min:3"
                placeholder="Např. Novák"
              />
              <BaseFormInput
                v-model="item.email"
                name="email"
                label="E-mail"
                type="text"
                rules="required|email"
                placeholder="jan.novak@email.cz"
              />
              <BaseFormInput
                v-model="item.phone"
                name="phone"
                label="Telefon"
                type="text"
                placeholder="+420 123 456 789"
              />

              <div class="col-span-full pt-4">
                <LayoutDivider>Firemní údaje (volitelné)</LayoutDivider>
              </div>

              <BaseFormInput
                v-model="item.company"
                name="company"
                label="Název společnosti"
                type="text"
                class="col-span-full"
              />
              <BaseFormInput v-model="item.ico" name="ico" label="IČO" type="text" />
              <BaseFormInput v-model="item.dic" name="dic" label="DIČ" type="text" />
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600"
              >
                <MapPinIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Adresa bydliště / sídla</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-6">
              <BaseFormInput
                v-model="item.street"
                name="street"
                label="Ulice a číslo popisné"
                type="text"
                class="col-span-full sm:col-span-4"
              />
              <div class="hidden sm:col-span-2 sm:block"></div>
              <BaseFormInput
                v-model="item.city"
                name="city"
                label="Město"
                type="text"
                class="col-span-full sm:col-span-3"
              />
              <BaseFormInput
                v-model="item.zip"
                name="zip"
                label="PSČ"
                type="text"
                class="col-span-full sm:col-span-1"
              />

              <BaseFormSelect
                v-model="item.country_id"
                name="country_id"
                label="Země"
                :options="countryStore.countriesOptions"
                class="col-span-full sm:col-span-2"
              />
            </div>
          </LayoutContainer>
        </div>

        <div class="col-span-1 space-y-6 lg:sticky lg:top-8 lg:col-span-4">
          <LayoutContainer class="!py-6">
            <LayoutTitle class="text-xs uppercase tracking-widest text-slate-400"
              >Status registrace</LayoutTitle
            >
            <div
              class="mt-6 rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200 transition-all hover:bg-white hover:shadow-md"
            >
              <BaseFormCheckbox
                v-model="item.is_paid"
                name="is_paid"
                label="Registrace je uhrazena"
                class="flex-row-reverse justify-between font-bold text-slate-900"
              />
            </div>
            <div class="mt-4 flex items-center gap-2 px-1">
              <div
                :class="[item.is_paid ? 'bg-emerald-500' : 'bg-amber-500', 'size-2 rounded-full']"
              />
              <p class="text-xs font-medium text-slate-500">
                {{ item.is_paid ? 'Platba byla potvrzena' : 'Čeká se na úhradu' }}
              </p>
            </div>
          </LayoutContainer>

          <LayoutContainer class="!py-6">
            <div class="mb-4 flex items-center gap-2">
              <PencilSquareIcon class="size-4 text-slate-400" />
              <LayoutTitle class="!mb-0 text-xs uppercase tracking-widest text-slate-400"
                >Interní poznámka</LayoutTitle
              >
            </div>
            <BaseFormTextarea
              v-model="item.note"
              name="note"
              label=""
              placeholder="Zde můžete dopsat doplňující informace k účastníkovi..."
              rows="6"
              class="!bg-slate-50 focus:!bg-white"
            />
          </LayoutContainer>

          <div class="rounded-3xl bg-indigo-600 p-6 text-white shadow-xl shadow-indigo-200">
            <h4 class="text-sm font-bold">Potřebujete fakturu?</h4>
            <p class="mt-2 text-xs leading-relaxed opacity-80">
              Pokud jsou vyplněny firemní údaje (IČO/DIČ), systém automaticky vygeneruje daňový
              doklad po označení registrace jako uhrazené.
            </p>
          </div>
        </div>
      </div>
    </Form>
  </div>
</template>
