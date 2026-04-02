<script setup lang="ts">
import { inject, ref } from 'vue';
import { CurrencyDollarIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová poptávka' : 'Detail poptávky');
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

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
      'X-Site-Hash': selectedSiteHash.value,
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
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst poptávku. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/uzivatele/poptavky');
    })
    .finally(() => {
      loading.value = false;
    });
}

watch(selectedSiteHash, () => {
  loadItem();
});

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
  <div class="space-y-6">
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="demands" />

    <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-3">
      <div class="space-y-6 lg:col-span-1">
        <LayoutContainer>
          <LayoutTitle>Kontakt</LayoutTitle>
          <div class="mt-4 space-y-6">
            <div class="flex flex-col gap-1">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400"
                >Celé jméno</span
              >
              <p class="text-base font-semibold text-slate-900">{{ item.fullname }}</p>
            </div>

            <div class="flex flex-col gap-1">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400"
                >E-mail</span
              >
              <a
                :href="`mailto:${item.email}`"
                class="group flex items-center gap-2 text-indigo-600 transition-colors hover:text-indigo-500"
              >
                <EnvelopeIcon class="size-4" />
                <span class="font-medium">{{ item.email }}</span>
              </a>
            </div>

            <div class="flex flex-col gap-1">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400"
                >Telefon</span
              >
              <a
                :href="`tel:${item.phone}`"
                class="group flex items-center gap-2 text-slate-700 transition-colors hover:text-indigo-600"
              >
                <PhoneIcon class="size-4 text-slate-400 group-hover:text-indigo-600" />
                <span class="font-medium">{{ item.phone }}</span>
              </a>
            </div>

            <div v-if="item.url" class="flex flex-col gap-1 border-t border-slate-100 pt-4">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400"
                >URL projektu</span
              >
              <a
                :href="item.url"
                target="_blank"
                class="group flex items-center gap-2 text-slate-600 transition-colors hover:text-indigo-600"
              >
                <LinkIcon class="size-4 text-slate-400 group-hover:text-indigo-600" />
                <span class="truncate font-medium">{{ item.url }}</span>
              </a>
            </div>
          </div>
        </LayoutContainer>

        <div
          v-if="item.offer_price"
          class="rounded-3xl bg-indigo-600 p-6 text-white shadow-lg shadow-indigo-200"
        >
          <div class="flex items-center gap-3 opacity-80">
            <BanknotesIcon class="size-5" />
            <span class="text-xs font-bold uppercase tracking-widest">Navrhovaná cena</span>
          </div>
          <p class="mt-2 text-3xl font-black">{{ item.offer_price }}</p>
        </div>
      </div>

      <div class="space-y-6 lg:col-span-2">
        <LayoutContainer>
          <div class="mb-4 flex items-center justify-between">
            <LayoutTitle class="!mb-0">Zpráva</LayoutTitle>
            <ChatBubbleLeftRightIcon class="size-5 text-slate-300" />
          </div>
          <div class="rounded-2xl bg-slate-50 p-6 ring-1 ring-inset ring-slate-100">
            <p class="whitespace-pre-wrap text-base leading-relaxed text-slate-700">
              {{ item.text }}
            </p>
          </div>
        </LayoutContainer>

        <LayoutContainer v-if="item.service">
          <LayoutTitle>Poptávaná služba</LayoutTitle>
          <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div
              class="flex items-start gap-4 rounded-2xl border border-slate-100 p-4 transition-colors hover:bg-slate-50"
            >
              <div
                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-lg font-bold text-indigo-600"
              >
                {{ item.service.name.charAt(0) }}
              </div>
              <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                  Název služby
                </p>
                <p class="font-bold text-slate-900">{{ item.service.name }}</p>
                <p class="mt-0.5 text-xs text-slate-500">
                  {{ item.service.type === 'product' ? 'Produkt' : 'Služba' }}
                </p>
              </div>
            </div>

            <div
              class="flex items-start gap-4 rounded-2xl border border-slate-100 p-4 transition-colors hover:bg-slate-50"
            >
              <div
                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 font-bold text-emerald-600"
              >
                <CurrencyDollarIcon class="size-6" />
              </div>
              <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                  Naše ceníková cena
                </p>
                <p class="font-bold text-slate-900">{{ item.service.price }}</p>
                <p class="mt-0.5 text-xs text-slate-500">
                  Cena za
                  {{ item.service.price_type === 'total' ? 'jednotku/službu' : 'hodinu práce' }}
                </p>
              </div>
            </div>
          </div>
        </LayoutContainer>
      </div>
    </div>
  </div>
</template>
