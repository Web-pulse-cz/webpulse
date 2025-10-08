<script setup lang="ts">
import { ref } from 'vue';

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const pageTitle = ref('Detail e-mailu');

const breadcrumbs = ref([
  {
    name: 'Nastaveni',
    link: '#',
    current: false,
  },
  {
    name: 'Logy',
    link: '#',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/nastaveni/logy/emaily',
    current: true,
  },
]);

const tabs = ref([
  { name: 'Základní údaje', link: '#info', current: false },
  { name: 'Tělo e-mailu', link: '#sablona', current: false },
]);

const item = ref({
  id: null as number | null,
  to: '' as string,
  subject: '' as string,
  cc: '' as string,
  bcc: '' as string,
  html: '' as string,
  attachments: [] as Array<{ name: string; url: string }>,
  priority: 1 as number,
  status: '' as string,
  attempts: 0 as number,
  sent_at: null as string | null,
  locale: 'cs' as string,
  template: '' as string,
  sent: false as boolean,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    to: string;
    subject: string;
    cc: string;
    bcc: string;
    html: string;
    attachments: Array<{ name: string; url: string }>;
    priority: number;
    status: string;
    attempts: number;
    sent_at: string | null;
    locale: string;
    template: string;
    sent: boolean;
  }>('/api/admin/log/email/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      breadcrumbs.value.pop();
      pageTitle.value = 'E-mail #' + item.value.id;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/nastaveni/logy/emaily/' + route.params.id,
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
    })
    .finally(() => {
      loading.value = false;
    });
}

watchEffect(() => {
  const routeTabHash = route.hash;
  if (routeTabHash && routeTabHash !== '') {
    tabs.value.forEach((tab) => {
      tab.current = tab.link === routeTabHash;
    });
  } else {
    tabs.value[0].current = true;
    router.push(route.path + '#info');
  }
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
  <div>
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="emails" />
    <div>
      <div class="mt-5 block">
        <nav class="isolate flex divide-x divide-gray-200 shadow-sm" aria-label="Tabs">
          <NuxtLink
            v-for="(tab, index) in tabs"
            :key="index"
            :to="tab.link"
            class="group relative min-w-0 flex-1 overflow-hidden bg-white px-2 py-2.5 text-center text-xs font-medium text-grayCustom hover:bg-gray-50 hover:text-grayDark focus:z-10 lg:px-4 lg:py-4 lg:text-sm"
          >
            <span>{{ tab.name }}</span>
            <span
              aria-hidden="true"
              :class="
                tab.current
                  ? 'absolute inset-x-0 bottom-0 h-0.5 bg-primaryCustom'
                  : 'absolute inset-x-0 bottom-0 h-0.5 bg-transparent'
              "
            />
          </NuxtLink>
        </nav>
      </div>
    </div>
    <div class="grid grid-cols-1 items-baseline gap-x-4 gap-y-8 lg:grid-cols-7">
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
        <LayoutContainer class="col-span-full grid w-full grid-cols-12 gap-4">
          <div class="col-span-full grid grid-cols-12 gap-x-8 gap-y-4">
            <p class="col-span-3 font-semibold text-grayDark">Příjemce:</p>
            <p class="col-span-9 text-grayCustom">{{ item.to }}</p>
          </div>
          <div class="col-span-full grid grid-cols-12 gap-x-8 gap-y-4">
            <p class="col-span-3 font-semibold text-grayDark">Kopie:</p>
            <p class="col-span-9 text-grayCustom">{{ item.cc }}</p>
          </div>
          <div class="col-span-full grid grid-cols-12 gap-x-8 gap-y-4">
            <p class="col-span-3 font-semibold text-grayDark">Skrytá kopie:</p>
            <p class="col-span-9 text-grayCustom">{{ item.bcc }}</p>
          </div>
          <div class="col-span-full grid grid-cols-12 gap-x-8 gap-y-4">
            <p class="col-span-3 font-semibold text-grayDark">Priorita:</p>
            <p class="col-span-9 text-grayCustom">{{ item.priority }}</p>
          </div>
          <div class="col-span-full grid grid-cols-12 gap-x-8 gap-y-4">
            <p class="col-span-3 font-semibold text-grayDark">Stav:</p>
            <div class="col-span-9 text-grayCustom">
              <PropsBadge v-if="item.status === 'sent'" color="green">Odesláno</PropsBadge>
              <PropsBadge v-if="item.status === 'pending'" color="yellow"
                >Čeká na odeslání</PropsBadge
              >
              <PropsBadge v-if="item.status === 'failed'" color="red">Chyba</PropsBadge>
            </div>
          </div>
          <div class="col-span-full grid grid-cols-12 gap-x-8 gap-y-4">
            <p class="col-span-3 font-semibold text-grayDark">Počet pokusů:</p>
            <p class="col-span-9 text-grayCustom">{{ item.attempts }}</p>
          </div>
        </LayoutContainer>
      </template>
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#sablona')">
        <LayoutContainer class="col-span-full grid w-full grid-cols-12 gap-4">
          <div class="col-span-full" v-html="item.html" />
        </LayoutContainer>
      </template>
    </div>
  </div>
</template>
