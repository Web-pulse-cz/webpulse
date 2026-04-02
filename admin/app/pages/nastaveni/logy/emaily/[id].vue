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
  <div class="space-y-6 pb-20">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      slug="emails"
      :modify-bottom="false"
    />

    <LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

    <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
      <div class="col-span-1 space-y-8 lg:col-span-9">
        <template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <EnvelopeIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Detail doručení</LayoutTitle>
            </div>

            <div class="space-y-6">
              <div class="flex flex-col gap-1 border-b border-slate-50 pb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400"
                  >Hlavní příjemce (To)</span
                >
                <p class="text-base font-bold text-indigo-600">{{ item.to }}</p>
              </div>

              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="flex flex-col gap-1">
                  <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400"
                    >Kopie (CC)</span
                  >
                  <p class="text-sm text-slate-600">{{ item.cc || '---' }}</p>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400"
                    >Skrytá kopie (BCC)</span
                  >
                  <p class="text-sm text-slate-600">{{ item.bcc || '---' }}</p>
                </div>
              </div>
            </div>
          </LayoutContainer>
        </template>

        <template v-if="tabs.find((tab) => tab.current && tab.link === '#sablona')">
          <LayoutContainer class="overflow-hidden !p-0 shadow-inner ring-1 ring-slate-200">
            <div
              class="flex items-center justify-between border-b border-slate-200 bg-slate-100 px-4 py-2"
            >
              <div class="flex gap-1.5">
                <div class="size-2.5 rounded-full bg-slate-300" />
                <div class="size-2.5 rounded-full bg-slate-300" />
                <div class="size-2.5 rounded-full bg-slate-300" />
              </div>
              <span class="text-[10px] font-bold uppercase italic tracking-widest text-slate-400"
                >Náhled odeslaného HTML</span
              >
              <div class="w-12" />
            </div>

            <div class="flex min-h-[600px] justify-center bg-slate-50 p-4 md:p-8">
              <div class="w-full max-w-3xl rounded-sm bg-white p-2 shadow-2xl ring-1 ring-black/5">
                <div class="prose prose-sm max-w-none" v-html="item.html" />
              </div>
            </div>
          </LayoutContainer>
        </template>
      </div>

      <aside class="col-span-1 space-y-6 lg:sticky lg:top-24 lg:col-span-3">
        <LayoutContainer class="!py-6">
          <LayoutTitle class="text-xs uppercase tracking-widest text-slate-400"
            >Status zprávy</LayoutTitle
          >

          <div class="mt-6 space-y-6">
            <div
              class="flex items-center justify-between rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200"
            >
              <span class="text-xs font-semibold text-slate-500">Aktuální stav</span>
              <PropsBadge v-if="item.status === 'sent'" color="green">Odesláno</PropsBadge>
              <PropsBadge v-if="item.status === 'pending'" color="yellow">Čeká</PropsBadge>
              <PropsBadge v-if="item.status === 'failed'" color="red">Chyba</PropsBadge>
            </div>

            <div class="grid grid-cols-2 gap-4 text-center">
              <div class="rounded-xl border border-slate-100 p-3">
                <span class="block text-[10px] font-bold uppercase text-slate-400">Priorita</span>
                <span class="text-sm font-black text-slate-700">{{ item.priority }}</span>
              </div>
              <div class="rounded-xl border border-slate-100 p-3">
                <span class="block text-[10px] font-bold uppercase text-slate-400">Pokusy</span>
                <span class="text-sm font-black text-slate-700">{{ item.attempts }}x</span>
              </div>
            </div>
          </div>
        </LayoutContainer>

        <div
          v-if="item.status === 'failed'"
          class="rounded-3xl bg-rose-50 p-6 ring-1 ring-inset ring-rose-100"
        >
          <div class="mb-3 flex items-center gap-2">
            <ExclamationCircleIcon class="size-5 text-rose-600" />
            <h4 class="text-sm font-bold text-rose-900">Chyba při odesílání</h4>
          </div>
          <p class="text-xs leading-relaxed text-rose-700/80">
            E-mail se nepodařilo doručit. Systém se pokusí odeslání zopakovat celkem 3x, než zprávu
            trvale označí za chybnou.
          </p>
        </div>
      </aside>
    </div>
  </div>
</template>
