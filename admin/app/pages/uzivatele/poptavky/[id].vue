<script setup lang="ts">
import { ref } from 'vue';

const toast = useToast();

const route = useRoute();

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová poptávka' : 'Detail poptávky');

const breadcrumbs = ref([
  {
    name: 'Poptávky',
    link: '/uzivatele/poptavky',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/uzivatele/poptavky/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  fullname: '' as string,
  email: '' as string,
  phone: '' as string,
  url: '' as string,
  text: '' as string,
  offer_price: 0 as number,
  locale: '' as string,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    fullname: string;
    email: string;
    phone: string;
    url: string;
    text: string;
    offer_price: number;
    locale: string;
  }>('/api/admin/demand/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      breadcrumbs.value.pop();
      pageTitle.value = 'Poptávka #' + item.value.id;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/uzivatele/poptavky/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst poptávku. Zkuste to prosím později.',
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
  loadItem();
});
definePageMeta({
  middleware: 'sanctum:auth',
});
</script>

<template>
  <div>
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="demands" />
    <div class="grid grid-cols-1 items-baseline gap-x-4 gap-y-8 lg:grid-cols-7">
      <LayoutContainer class="col-span-full grid w-full grid-cols-12 gap-4">
        <div class="col-span-full grid grid-cols-12 gap-x-8 gap-y-4">
          <p class="col-span-3 font-semibold text-grayDark">Celé jmeno:</p>
          <p class="col-span-9 text-grayCustom">{{ item.fullname }}</p>
        </div>
        <div class="col-span-full grid grid-cols-12 gap-x-8 gap-y-4">
          <p class="col-span-3 font-semibold text-grayDark">E-mail:</p>
          <p class="col-span-9 text-grayCustom">{{ item.email }}</p>
        </div>
        <div class="col-span-full grid grid-cols-12 gap-x-8 gap-y-4">
          <p class="col-span-3 font-semibold text-grayDark">Telefon:</p>
          <p class="col-span-9 text-grayCustom">{{ item.phone }}</p>
        </div>
        <div class="col-span-full grid grid-cols-12 gap-x-8 gap-y-4">
          <p class="col-span-3 font-semibold text-grayDark">Zpráva:</p>
          <p class="col-span-9 text-grayCustom">{{ item.text }}</p>
        </div>
        <div v-if="item.url" class="col-span-full grid grid-cols-12 gap-x-8 gap-y-4">
          <p class="col-span-3 font-semibold text-grayDark">URL projektu:</p>
          <p class="col-span-9 text-grayCustom">{{ item.url }}</p>
        </div>
        <div v-if="item.offer_price" class="col-span-full grid grid-cols-12 gap-x-8 gap-y-4">
          <p class="col-span-3 font-semibold text-grayDark">Navrhovaná cena:</p>
          <p class="col-span-9 text-grayCustom">{{ item.offer_price }}</p>
        </div>
        <div v-if="item.service" class="col-span-full grid grid-cols-12 gap-x-8 gap-y-4">
          <p class="col-span-3 font-semibold text-grayDark">Název služby:</p>
          <p class="col-span-9 text-grayCustom">{{ item.service.name }}</p>
        </div>
        <div v-if="item.service" class="col-span-full grid grid-cols-12 gap-x-8 gap-y-4">
          <p class="col-span-3 font-semibold text-grayDark">Typ služby:</p>
          <p class="col-span-9 text-grayCustom">
            {{ item.service.type === 'product' ? 'Produkt' : 'Služba' }}
          </p>
        </div>
        <div v-if="item.service" class="col-span-full grid grid-cols-12 gap-x-8 gap-y-4">
          <p class="col-span-3 font-semibold text-grayDark">Naše cena:</p>
          <p class="col-span-9 text-grayCustom">
            {{ item.service.price }} (cena za
            {{ item.service.price_type === 'total' ? 'službu' : 'hodinu' }})
          </p>
        </div>
      </LayoutContainer>
    </div>
  </div>
</template>
