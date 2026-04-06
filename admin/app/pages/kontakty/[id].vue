<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';
import { useActivityStore } from '~~/stores/activityStore';

const activityStore = useActivityStore();

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const phases = ref([]);
const sources = ref([]);
const tasks = ref([]);
const lists = ref([]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový kontakt' : 'Detail kontaktu');

const tabs = ref([
  { name: 'Základní údaje', link: '#info', current: false },
  { name: 'Proces', link: '#proces', current: false },
  { name: 'Historie', link: '#historie', current: false },
  { name: 'Lidé', link: '#lide', current: false },
]);

const historyDialog = ref({
  open: false,
  item: null,
});

const breadcrumbs = ref([
  {
    name: 'Kontakty',
    link: '/kontakty',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/kontakty/pridat',
    current: true,
  },
]);

const intrests = ref([
  { key: 'business', name: 'Vlastní byznys, tvoření PP', value: 0 },
  { key: 'extra', name: 'Přivýdělek/extra příjem k tomu, co děláš teď', value: 0 },
  { key: 'growth', name: 'Osobní a podnikatelský růst', value: 0 },
  { key: 'comunity', name: 'Komunitu lidí, co na sobě pracují a vzájemně se podporují', value: 0 },
  { key: 'health', name: 'Prevenci zdraví, healthspan', value: 0 },
]);

const item = ref({
  id: null as number | null,
  firstname: '' as string,
  lastname: '' as string,
  phone_prefix: '+420' as string,
  phone: '' as string,
  email: '' as string,
  company: '' as string,
  street: '' as string,
  city: '' as string,
  zip: '' as string,
  occupation: '' as string,
  goal: '' as string,
  note: '' as string,
  contact_source_id: null as number | string | null,
  contact_phase_id: null as number | string | null,
  next_meeting: '' as string,
  formatted_next_meeting: '' as string,
  next_contact: '' as string,
  formatted_next_contact: '' as string,
  last_contacted_at: '' as string,
  formatted_last_contacted_at: '' as string,
  contact_id: null as number | null,
  history: [] as [],
  contacts: [] as [],
  interests: [] as [],
  parent_contact: {
    id: 0 as number | null,
    firstname: '' as string,
    lastname: '' as string,
  },
  source: {
    id: null as number | null,
    name: '' as string,
    color: '' as string,
  },
  phase: {
    id: null as number | null,
    name: '' as string,
    color: '' as string,
  },
  tasks: [] as [],
  lists: [] as [],
});

const parentContactOptions = ref({
  firstname: 'Osobní' as string,
  lastname: 'kontakt' as string,
  id: null as number | null,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    firstname: string;
    lastname: string;
    phone_prefix: string;
    phone: string;
    email: string;
    company: string;
    street: string;
    city: string;
    zip: string;
    occupation: string;
    goal: string;
    note: string;
    contact_source_id: number | null;
    contact_phase_id: number | null;
    next_meeting: string;
    formatted_next_meeting: string;
    next_contact: string;
    formatted_next_contact: string;
    last_contacted_at: string;
    formatted_last_contacted_at: string;
    contact_id: number | null;
    parent_contact: {
      id: number | null;
      firstname: string;
      lastname: string;
    };
    source: {
      id: number | null;
      name: string;
      color: string;
    };
    phase: {
      id: number | null;
      name: string;
      color: string;
    };
    tasks: {
      id: number | null;
      name: string;
      phase_id: string;
    }[];
    lists: {
      id: number | null;
      name: string;
    };
    interests: number[];
  }>('/api/admin/contact/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      item.value.tasks = response.tasks.map((task) => task.id);
      item.value.lists = response.lists.map((list) => list.id);
      if (!item.value.interests || !item.value.interests.length) {
        item.value.interests = intrests.value;
      }
      breadcrumbs.value.pop();
      pageTitle.value = item.value.firstname + ' ' + item.value.lastname;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/administratori/' + route.params.id,
        current: true,
      });
      if (
        item.value.contacts &&
        item.value.contacts.data &&
        !item.value.contacts.data.length &&
        tabs.value.find((tab) => tab.link === '#lide')
      ) {
        tabs.value.pop();
      }
      if (item.value.parent_contact) {
        parentContactOptions.value = {
          firstname: item.value.parent_contact.firstname,
          lastname: item.value.parent_contact.lastname,
          id: item.value.parent_contact.id,
        };
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst detail kontaktu. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadPhases() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{ id: number }>('/api/admin/contact/phase', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      response.forEach((phase: { id: number; name: string }) => {
        phases.value.push({
          value: phase.id,
          name: phase.name,
        });
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst fáze. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadSources() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{ id: number }>('/api/admin/contact/source', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      response.forEach((source: { id: number; name: string }) => {
        sources.value.push({
          value: source.id,
          name: source.name,
        });
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst zdroje. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadTasks() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{ id: number }>('/api/admin/contact/task', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      tasks.value = response;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst zdroje. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadLists() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{ id: number }>('/api/admin/contact/list', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      lists.value = response;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst seznamy kontaktů. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true as boolean) {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    firstname: string;
    lastname: string;
    phone_prefix: string;
    phone: string;
    email: string;
    company: string;
    street: string;
    city: string;
    zip: string;
    occupation: string;
    goal: string;
    note: string;
    contact_source_id: number | null;
    contact_phase_id: number | null;
    next_meeting: string;
    formatted_next_meeting: string;
    next_contact: string;
    formatted_next_contact: string;
    last_contacted_at: string;
    formatted_last_contacted_at: string;
    contact_id: number | null;
    parent_contact: {
      id: number | null;
      firstname: string;
      lastname: string;
    };
    source: {
      id: number | null;
      name: string;
      color: string;
    };
    phase: {
      id: number | null;
      name: string;
      color: string;
    };
    tasks: {
      id: number | null;
      name: string;
      phase_id: string;
    }[];
    lists: {
      id: number | null;
      name: string;
    };
    interests: number[];
  }>(
    route.params.id === 'pridat' ? '/api/admin/contact' : '/api/admin/contact/' + route.params.id,
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
            ? 'Kontakt byl úspěšně vytvořen.'
            : 'Kontakt byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push(`/kontakty/${response.id}`);
      } else if (redirect) {
        router.push('/kontakty');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit kontakt. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveHistoryItem(item: { id: number }) {
  await saveItem(false);
  const client = useSanctumClient();
  loading.value = true;

  await client(
    !item.id
      ? '/api/admin/contact/history/' + route.params.id
      : '/api/admin/contact/history/' + route.params.id + '/' + item.id,
    {
      method: 'POST',
      body: JSON.stringify(item),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    },
  )
    .then(() => {
      $toast.show({
        summary: 'Hotovo',
        detail: `Záznám historie byl úspěšně ${!item.id ? 'vytvořen' : 'uložen'}.`,
        severity: 'success',
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit záznam historie. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      historyDialog.value.open = false;
      historyDialog.value.item = {};
      loading.value = false;
      loadItem();
    });
}

async function deleteHistoryItem(item: { id: number }) {
  const client = useSanctumClient();
  loading.value = true;

  await client<{}>('/api/admin/contact/history/destroy/' + item.id, {
    method: 'DELETE',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then(() => {
      $toast.show({
        summary: 'Hotovo',
        detail: `Záznám historie byl úspěšně smazán.`,
        severity: 'success',
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se smazat záznam historie.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
      loadItem();
    });
}

function addRemoveItemTask(taskId) {
  if (item.value.tasks.includes(taskId)) {
    item.value.tasks = item.value.tasks.filter((task) => task !== taskId);
    return;
  } else {
    item.value.tasks.push(taskId);
  }
}

function addRemoveItemList(listId) {
  if (item.value.lists.includes(listId)) {
    item.value.lists = item.value.lists.filter((list) => list !== listId);
    return;
  } else {
    item.value.lists.push(listId);
  }
}

function editHistoryItem(history) {
  historyDialog.value.item = history;
  historyDialog.value.open = true;
}

useHead({
  title: pageTitle.value,
});

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

onMounted(() => {
  if (route.params.id !== 'pridat') {
    loadItem();
  } else {
    tabs.value.pop();
    tabs.value.pop();
    item.value.interests = intrests.value;
  }
  activityStore.fetchActivities(true);
  loadPhases();
  loadSources();
  loadTasks();
  loadLists();
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
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="contacts"
      :modify-bottom="false"
      @save="saveItem"
    />

    <LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

    <Form @submit="saveItem">
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
          <div class="col-span-1 space-y-8 lg:col-span-5">
            <LayoutContainer>
              <div class="mb-8 flex items-center gap-3">
                <div
                  class="flex size-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-100"
                >
                  <UserIcon class="size-6" />
                </div>
                <LayoutTitle class="!mb-0">Osobní profil</LayoutTitle>
              </div>

              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <BaseFormInput
                  v-model="item.firstname"
                  label="Jméno"
                  type="text"
                  name="firstname"
                  rules="required|min:3"
                />
                <BaseFormInput
                  v-model="item.lastname"
                  label="Příjmení"
                  type="text"
                  name="lastname"
                  rules="required|min:3"
                />
                <BaseFormInput
                  v-model="item.email"
                  label="E-mail"
                  type="text"
                  name="email"
                  rules="email"
                  class="col-span-full"
                  placeholder="klient@email.cz"
                />
                <BaseFormInput
                  v-model="item.phone"
                  label="Telefonní číslo"
                  type="text"
                  name="phone"
                  class="col-span-full"
                  placeholder="+420 000 000 000"
                />

                <div class="col-span-full pt-4">
                  <LayoutDivider>Fakturační adresa / Sídlo</LayoutDivider>
                </div>

                <BaseFormInput
                  v-model="item.company"
                  label="Název firmy (volitelné)"
                  type="text"
                  name="company"
                  class="col-span-full"
                />
                <BaseFormInput
                  v-model="item.street"
                  label="Ulice a číslo popisné"
                  type="text"
                  name="street"
                  class="col-span-full"
                />
                <BaseFormInput
                  v-model="item.zip"
                  label="PSČ"
                  type="text"
                  name="zip"
                  class="col-span-1"
                />
                <BaseFormInput
                  v-model="item.city"
                  type="text"
                  label="Město"
                  name="city"
                  class="col-span-1"
                />
              </div>
            </LayoutContainer>
          </div>

          <div class="col-span-1 space-y-8 lg:col-span-7">
            <LayoutContainer>
              <div class="mb-8 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
                >
                  <IdentificationIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Kontext a sny</LayoutTitle>
              </div>

              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <BaseFormInput
                  v-model="item.occupation"
                  type="text"
                  label="Práce / Obor / Studium"
                  name="occupation"
                  class="col-span-full"
                />
                <BaseFormInput
                  v-model="item.goal"
                  type="text"
                  label="Sen nebo životní cíl"
                  name="goal"
                  class="col-span-full"
                />
                <BaseFormTextarea
                  v-model="item.note"
                  label="Interní poznámka k osobě"
                  name="note"
                  rows="4"
                  class="col-span-full bg-slate-50 focus:bg-white"
                />

                <div class="col-span-full pt-4">
                  <LayoutDivider>Původ kontaktu</LayoutDivider>
                </div>

                <BaseFormSelect
                  v-model="item.contact_source_id"
                  :options="sources"
                  label="Jak nás našel?"
                  name="contact_source_id"
                />
                <ContactAutocomplete
                  v-model="item.contact_id"
                  :contact-options="parentContactOptions"
                  label="Doporučil (Ambasador)"
                />

                <div v-if="lists && lists.length" class="col-span-full mt-6">
                  <span
                    class="mb-4 block text-xs font-bold uppercase tracking-widest text-slate-400"
                    >Segmentace kontaktu</span
                  >
                  <div class="flex flex-wrap items-center gap-3">
                    <BaseFormCheckbox
                      v-for="(list, key) in lists"
                      :key="key"
                      :label="list.name"
                      :name="list.id"
                      :value="item.lists.includes(list.id)"
                      :checked="item.lists.includes(list.id)"
                      type="badge"
                      :color="list.color"
                      class="transition-transform hover:scale-105"
                      @change="addRemoveItemList(list.id)"
                    />
                  </div>
                </div>
              </div>
            </LayoutContainer>
          </div>
        </div>
      </template>

      <template v-if="tabs.find((tab) => tab.current && tab.link === '#proces')">
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
          <div class="col-span-1 space-y-6 lg:col-span-4">
            <LayoutContainer class="!border-l-4 !border-l-indigo-500">
              <LayoutTitle class="text-indigo-600">Obchodní status</LayoutTitle>
              <div class="space-y-6">
                <BaseFormSelect
                  v-model="item.contact_phase_id"
                  :options="phases"
                  label="Aktuální fáze procesu"
                  name="contact_phase_id"
                  class="col-span-full"
                />
                <div class="space-y-4 rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                  <BaseFormInput
                    v-model="item.formatted_next_meeting"
                    type="datetime-local"
                    label="Příští meeting (osobně)"
                    name="next_meeting"
                  />
                  <BaseFormInput
                    v-model="item.formatted_next_contact"
                    type="datetime-local"
                    label="Příští kontakt (Follow-up)"
                    name="next_contact"
                  />
                  <BaseFormInput
                      v-model="item.formatted_last_contacted_at"
                      type="datetime-local"
                      label="Naposledy kontaktováno"
                      name="last_contacted_at"
                      class="opacity-60"
                  />
                </div>
              </div>
            </LayoutContainer>
          </div>

          <div class="col-span-1 lg:col-span-4">
            <LayoutContainer>
              <div class="mb-6 flex items-center gap-2">
                <HeartIcon class="size-5 text-rose-500" />
                <LayoutTitle class="!mb-0">Úroveň zájmu</LayoutTitle>
              </div>
              <div class="space-y-5">
                <div
                  v-for="(interest, index) in item.interests"
                  :key="index"
                  class="flex items-center gap-4 rounded-xl border border-slate-100 bg-slate-50 p-3"
                >
                  <BaseFormInput
                    v-model="interest.value"
                    type="number"
                    :name="interest.key"
                    :label="interest.name"
                    class="!mb-0 flex-1"
                    min="0"
                    max="10"
                  />
                </div>
              </div>
            </LayoutContainer>
          </div>

          <div class="col-span-1 lg:col-span-4">
            <LayoutContainer class="max-h-[600px] overflow-y-auto">
              <div class="mb-6 flex items-center gap-2">
                <CheckCircleIcon class="size-5 text-emerald-500" />
                <LayoutTitle class="!mb-0">Checklist úkolů</LayoutTitle>
              </div>
              <div class="grid grid-cols-1 gap-3">
                <BaseFormCheckbox
                  v-for="(task, key) in tasks"
                  :key="key"
                  :label="task.name"
                  :name="task.id"
                  :value="item.tasks.includes(task.id)"
                  :checked="item.tasks.includes(task.id)"
                  class="!w-full rounded-xl border p-3 transition-colors hover:bg-slate-50"
                  type="badge"
                  :color="task.phase_color"
                  @change="addRemoveItemTask(task.id)"
                />
              </div>
            </LayoutContainer>
          </div>
        </div>
      </template>

      <template v-if="tabs.find((tab) => tab.current && tab.link === '#historie')">
        <LayoutContainer>
          <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-6">
            <div>
              <LayoutTitle class="!mb-1">Časová osa kontaktu</LayoutTitle>
              <p class="text-sm text-slate-500">
                Chronologický přehled všech interakcí a změn fází.
              </p>
            </div>
            <BaseButton
              variant="primary"
              size="xl"
              type="button"
              class="shadow-lg shadow-indigo-100"
              @click="
                historyDialog.item = {};
                historyDialog.open = true;
              "
            >
              <PlusIcon class="mr-2 size-5" />
              Nový záznam
            </BaseButton>
          </div>

          <div class="relative px-4">
            <ol class="relative ml-4 space-y-10 border-s-2 border-slate-200">
              <ContactHistoryCard
                v-for="(history, index) in item.history"
                :key="index"
                :history="history"
                @edit-history="editHistoryItem(history)"
                @delete-item="deleteHistoryItem(history)"
              />
            </ol>
          </div>
        </LayoutContainer>

        <ContactHistoryDialog
          v-model:show="historyDialog.open"
          v-model:item="historyDialog.item"
          :phases="phases"
          @save-item="saveHistoryItem"
        />
      </template>

      <template v-if="tabs.find((tab) => tab.current && tab.link === '#lide')">
        <LayoutContainer v-if="item.contacts?.data?.length">
          <div class="mb-8">
            <LayoutTitle>Přiřazené kontakty</LayoutTitle>
            <p class="text-sm text-slate-500">
              Osoby, které jsou s tímto kontaktem v přímém vztahu.
            </p>
          </div>

          <BaseTable
            :items="item.contacts"
            :columns="[
              { key: 'id', name: 'ID', type: 'text', width: 60 },
              { key: 'firstname', name: 'Jméno', type: 'text' },
              { key: 'lastname', name: 'Příjmení', type: 'text' },
              { key: 'phone', name: 'Telefon', type: 'text' },
              { key: 'email', name: 'E-mail', type: 'text' },
              { key: 'phase', name: 'Fáze', type: 'badge', colorKey: 'phase_color' },
              { key: 'source', name: 'Zdroj', type: 'badge', colorKey: 'source_color' },
            ]"
            :actions="[{ type: 'edit', path: '/kontakty', hash: '#proces' }]"
            :loading="loading"
            :error="error"
            singular="Kontakt"
            plural="Kontakty"
            slug="contacts"
          />
        </LayoutContainer>
      </template>
    </Form>
  </div>
</template>
