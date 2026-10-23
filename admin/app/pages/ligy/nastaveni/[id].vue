<script setup lang="ts">
import { inject, ref, computed, watch } from 'vue';
import { Form } from 'vee-validate';
import { TrophyIcon, Squares2X2Icon, UserGroupIcon } from '@heroicons/vue/24/outline';
import type { LeagueActivityForm, LeagueSectionForm } from '~/composables/useLeague';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const { formRef, validateForm } = useFormValidation();
const { scoringOptions, visibilityOptions } = useLeague();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const isNew = computed(() => route.params.id === 'pridat');

const pageTitle = ref(isNew.value ? 'Nová liga' : 'Nastavení ligy');

const breadcrumbs = ref([
  { name: 'Nastavení lig', link: '/ligy/nastaveni', current: false },
  { name: pageTitle.value, link: '/ligy/nastaveni/pridat', current: true },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  description: '' as string,
  scoring_type: 'points' as string,
  visibility: 'public' as string,
  position: 0 as number,
  sites: [] as number[],
  members: [] as number[],
});

const sections = ref<LeagueSectionForm[]>([]);
const activities = ref<LeagueActivityForm[]>([]);

const users = ref<any[]>([]);
const userSearch = ref('');

const filteredUsers = computed(() => {
  const q = userSearch.value.trim().toLowerCase();
  if (!q) return users.value;
  return users.value.filter((u) =>
    `${u.firstname} ${u.lastname} ${u.email}`.toLowerCase().includes(q),
  );
});

function toActivityForm(a: any): LeagueActivityForm {
  return {
    _key: `a-${a.id}`,
    id: a.id,
    name: a.name,
    value: a.value,
    max_per_day: a.max_per_day,
    counts_to_league: a.counts_to_league,
    entries_count: a.entries_count,
  };
}

function toPayloadActivity(a: LeagueActivityForm) {
  return {
    id: a.id,
    name: a.name,
    value: Number(a.value) || 0,
    max_per_day: a.max_per_day,
    counts_to_league: a.counts_to_league,
  };
}

async function loadUsers() {
  const client = useSanctumClient();
  await client('/api/admin/user', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response: any) => {
      users.value = Array.isArray(response) ? response : response.data || [];
    })
    .catch(() => {
      users.value = [];
    });
}

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/league/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response: any) => {
      item.value = {
        id: response.id,
        name: response.name || '',
        description: response.description || '',
        scoring_type: response.scoring_type || 'points',
        visibility: response.visibility || 'public',
        position: response.position || 0,
        sites: response.sites?.map((s: any) => s.id) || [],
        members: response.members || [],
      };
      sections.value = (response.sections || []).map((s: any) => ({
        _key: `s-${s.id}`,
        id: s.id,
        name: s.name,
        activities: (s.activities || []).map(toActivityForm),
      }));
      activities.value = (response.activities || []).map(toActivityForm);

      breadcrumbs.value.pop();
      pageTitle.value = item.value.name || 'Nastavení ligy';
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/ligy/nastaveni/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst ligu.',
        severity: 'error',
      });
      router.push('/ligy/nastaveni');
    })
    .finally(() => {
      loading.value = false;
    });
}

function structureIsValid(): boolean {
  const allActivities = [...activities.value, ...sections.value.flatMap((s) => s.activities)];
  return sections.value.every((s) => s.name.trim()) && allActivities.every((a) => a.name.trim());
}

async function saveItem(redirect = true as boolean) {
  if (!(await validateForm())) return;
  if (!structureIsValid()) {
    $toast.show({
      summary: 'Chyba',
      detail: 'Vyplňte názvy všech sekcí a aktivit.',
      severity: 'error',
    });
    return;
  }

  const client = useSanctumClient();
  loading.value = true;

  const body = {
    ...item.value,
    members: item.value.visibility === 'invite' ? item.value.members : [],
    sections: sections.value.map((s) => ({
      id: s.id,
      name: s.name,
      activities: s.activities.map(toPayloadActivity),
    })),
    activities: activities.value.map(toPayloadActivity),
  };

  await client(isNew.value ? '/api/admin/league' : '/api/admin/league/' + route.params.id, {
    method: 'POST',
    body: JSON.stringify(body),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response: any) => {
      $toast.show({
        summary: 'Hotovo',
        detail: isNew.value ? 'Liga byla vytvořena.' : 'Liga byla upravena.',
        severity: 'success',
      });
      if (!redirect && isNew.value) router.push('/ligy/nastaveni/' + response.id);
      else if (redirect) router.push('/ligy/nastaveni');
      else loadItem();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se uložit ligu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

useHead({ title: pageTitle.value });
watch(selectedSiteHash, () => {
  loadUsers();
  if (!isNew.value) loadItem();
});

onMounted(() => {
  loadUsers();
  if (!isNew.value) loadItem();
});

definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-24">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="leagues"
      @save="saveItem"
    />

    <Form ref="formRef" @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <TrophyIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Základní údaje</LayoutTitle>
            </div>

            <div class="grid max-w-3xl grid-cols-1 gap-6 md:grid-cols-2">
              <div class="md:col-span-2">
                <BaseFormInput
                  v-model="item.name"
                  label="Název ligy"
                  type="text"
                  name="name"
                  rules="required|min:2"
                  placeholder="Např. Říjnová výzva"
                />
              </div>
              <BaseFormSelect
                v-model="item.scoring_type"
                label="Systém skórování"
                name="scoring_type"
                rules="required"
                :options="scoringOptions"
              />
              <BaseFormSelect
                v-model="item.visibility"
                label="Viditelnost"
                name="visibility"
                rules="required"
                :options="visibilityOptions"
              />
              <div class="md:col-span-2">
                <BaseFormTextarea
                  v-model="item.description"
                  label="Popis"
                  name="description"
                  rows="3"
                />
              </div>
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <Squares2X2Icon class="size-5" />
              </div>
              <div>
                <LayoutTitle class="!mb-0">Sekce a aktivity</LayoutTitle>
                <p class="text-xs text-slate-500">
                  Pořadí měníte přetažením. Prázdné „Max. za den“ = neomezený počet záznamů za den.
                </p>
              </div>
            </div>

            <LeagueStructureEditor
              v-model:sections="sections"
              v-model:activities="activities"
              :scoring-type="item.scoring_type"
            />
          </LayoutContainer>

          <LayoutContainer v-if="item.visibility === 'invite'">
            <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <UserGroupIcon class="size-5" />
              </div>
              <div>
                <LayoutTitle class="!mb-0">Pozvaní uživatelé</LayoutTitle>
                <p class="text-xs text-slate-500">
                  Ligu uvidí jen pozvaní uživatelé a její tvůrce. Vybráno: {{ item.members.length }}
                </p>
              </div>
            </div>

            <input
              v-model="userSearch"
              type="text"
              placeholder="Hledat uživatele…"
              class="mb-4 block w-full max-w-sm rounded-xl border-0 px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500"
            />

            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3">
              <label
                v-for="user in filteredUsers"
                :key="user.id"
                class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 ring-1 transition"
                :class="
                  item.members.includes(user.id)
                    ? 'bg-indigo-50 ring-indigo-300'
                    : 'bg-white ring-slate-200 hover:bg-slate-50'
                "
              >
                <input
                  v-model="item.members"
                  type="checkbox"
                  :value="user.id"
                  class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                />
                <span class="min-w-0">
                  <span class="block truncate text-sm font-medium text-slate-800">
                    {{ user.firstname }} {{ user.lastname }}
                  </span>
                  <span class="block truncate text-xs text-slate-500">{{ user.email }}</span>
                </span>
              </label>
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:position="item.position"
            v-model:sites="item.sites"
            :allow-translations="false"
            :allow-image="false"
            :allow-is-active="false"
            :allow-position="true"
            class="shadow-sm"
          />
        </aside>
      </div>
    </Form>
  </div>
</template>
