<script setup lang="ts">
import { inject, ref } from 'vue';
import { TrophyIcon, LockClosedIcon, GlobeAltIcon } from '@heroicons/vue/24/outline';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const { formatScore, scoringLabel } = useLeague();

const pageTitle = ref('Ligy');
const breadcrumbs = ref([{ name: pageTitle.value, link: '/ligy', current: true }]);
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const { canEdit } = usePermissions();

const loading = ref(false);

function stripHtml(html: string): string {
  return html
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
}
const leagues = ref<any[]>([]);

async function loadLeagues() {
  loading.value = true;
  const client = useSanctumClient();

  await client('/api/admin/league/board', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response: any) => {
      leagues.value = response;
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst ligy.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

watch(selectedSiteHash, () => loadLeagues());

useHead({ title: pageTitle.value });
onMounted(() => loadLeagues());
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-24">
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="leagues" />

    <div v-if="loading && !leagues.length" class="py-12 text-center text-sm text-slate-500">
      Načítám ligy…
    </div>

    <LayoutContainer v-else-if="!leagues.length">
      <div class="py-8 text-center">
        <TrophyIcon class="mx-auto mb-3 size-10 text-slate-300" />
        <p class="text-sm text-slate-500">Zatím nejste v žádné lize.</p>
        <NuxtLink
          v-if="canEdit('leagues')"
          to="/ligy/nastaveni/pridat"
          class="mt-4 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500"
        >
          Vytvořit ligu
        </NuxtLink>
      </div>
    </LayoutContainer>

    <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
      <NuxtLink
        v-for="league in leagues"
        :key="league.id"
        :to="`/ligy/${league.id}`"
        class="group flex flex-col rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md hover:ring-indigo-300"
      >
        <div class="mb-4 flex items-start justify-between gap-3">
          <div class="flex items-center gap-3">
            <div
              class="flex size-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
            >
              <TrophyIcon class="size-6" />
            </div>
            <div>
              <div class="font-semibold text-slate-900 group-hover:text-indigo-600">
                {{ league.name }}
              </div>
              <div class="flex items-center gap-1 text-xs text-slate-500">
                <component
                  :is="league.visibility === 'invite' ? LockClosedIcon : GlobeAltIcon"
                  class="size-3.5"
                />
                {{ league.visibility === 'invite' ? 'Na pozvání' : 'Pro všechny' }}
                · {{ scoringLabel(league.scoring_type) }}
              </div>
            </div>
          </div>
        </div>

        <p v-if="league.description" class="mb-4 line-clamp-2 text-sm text-slate-600">
          {{ stripHtml(league.description) }}
        </p>

        <div class="mt-auto grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
          <div>
            <div class="text-xs text-slate-500">Vede</div>
            <div class="truncate font-semibold text-slate-800">
              {{ league.leader && league.leader.total > 0 ? league.leader.name : '—' }}
            </div>
            <div v-if="league.leader && league.leader.total > 0" class="text-xs text-slate-500">
              {{ formatScore(league.leader.total, league.scoring_type) }}
            </div>
          </div>
          <div>
            <div class="text-xs text-slate-500">Já</div>
            <div class="font-semibold text-slate-800">
              {{ formatScore(league.my_total, league.scoring_type) }}
            </div>
            <div v-if="league.my_rank" class="text-xs text-slate-500">
              {{ league.my_rank }}. místo z {{ league.participants_count }}
            </div>
          </div>
        </div>
      </NuxtLink>
    </div>
  </div>
</template>
