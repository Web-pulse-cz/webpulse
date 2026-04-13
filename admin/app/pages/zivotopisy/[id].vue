<script setup lang="ts">
import { ref } from 'vue';
import { VPdfViewer } from '@vue-pdf-viewer/viewer';
import { Form } from 'vee-validate';
import { ArrowDownOnSquareIcon, TrashIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const user = useSanctumUser();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový životopis' : 'Detail životopisu');

const tabs = ref([
  { name: 'Základní údaje', link: '#info', current: false },
  { name: 'Pracovní zkušenosti', link: '#pracovni-zkusenosti', current: false },
  { name: 'Vzdělání', link: '#vzdelani', current: false },
  { name: 'Náhled', link: '#nahled', current: false },
]);

const breadcrumbs = ref([
  {
    name: 'Životopisy',
    link: '/zivotopisy',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/zivotopisy/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  template: '' as string,
  phone_prefix: '' as string,
  phone: '' as string,
  email: '' as string,
  linkedin: '' as string,
  github: '' as string,
  website: '' as string,
  address: '' as string,
  about_me: '' as string,
  job_experiences: [] as any[], // JSON
  education: [] as any[], // JSON
  skills: [] as any[], // JSON
  hard_skills: [] as any[], // JSON
  soft_skills: [] as any[], // JSON
  filename: '' as string,
  user_id: null as number | null,
  job_title: '' as string,
  summary: '' as string,
  template: 'default' as const,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    template: 'default';
    phone_prefix: '+420';
    phone: string;
    email: string;
    linkedin: string;
    github: string;
    website: string;
    address: string;
    about_me: string;
    job_experiences: any[];
    education: any[];
    skills: any[];
    hard_skills: any[];
    soft_skills: any[];
    filename: string;
    user_id: number | null;
    job_title: string;
    summary: string;
    template: string;
  }>('/api/admin/biography/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      breadcrumbs.value.pop();
      pageTitle.value = item.value.name;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/zivotopisy/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst životopis. Zkuste to prosím později.',
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
    name: string;
    template: string;
    phone_prefix: string;
    phone: string;
    email: string;
    linkedin: string;
    github: string;
    website: string;
    address: string;
    about_me: string;
    job_experiences: any[];
    education: any[];
    skills: any[];
    hard_skills: any[];
    soft_skills: any[];
    filename: string;
    user_id: number | null;
    job_title: string;
    summary: string;
    template: string;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/biography'
      : '/api/admin/biography/' + route.params.id,
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
            ? 'Životopis byl úspěšně vytvořen.'
            : 'Životopis byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/zivotopisy/' + response.id);
      } else if (redirect) {
        router.push('/zivotopisy');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit životopis. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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

function takeEmailFromProfile() {
  if (user.value && user.value.email) {
    item.value.email = user.value.email;
  }
}

function takePhonePrefixFromProfile() {
  if (user.value && user.value.phone_prefix) {
    item.value.phone_prefix = user.value.phone_prefix;
  }
}

function takePhoneFromProfile() {
  if (user.value && user.value.phone) {
    item.value.phone = user.value.phone;
  }
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

function addJobExperience() {
  item.value.job_experiences.unshift({
    position: '',
    company: '',
    start_date: '',
    end_date: '',
    description: '',
    skills: [],
  });
}

function addJobExperienceSkill(index) {
  item.value.job_experiences[index].skills.push({ name: '' });
}

function removeJobExperience(index) {
  item.value.job_experiences.splice(index, 1);
}

function addEducation() {
  item.value.education.unshift({
    institution: '',
    degree: '',
    start_date: '',
    end_date: '',
    description: '',
  });
}

function removeEducation(index) {
  item.value.education.splice(index, 1);
}

function addSkillGroup() {
  item.value.skills.push({ name: '', skills: [] });
}

function addSkill(groupIndex: number) {
  item.value.skills[groupIndex].skills.push({ name: '', level: 0 });
}

function removeSkillGroup(index: number) {
  item.value.skills.splice(index, 1);
}

function removeSkill(groupIndex: number, skillIndex: number) {
  item.value.skills[groupIndex].skills.splice(skillIndex, 1);
}
</script>

<template>
  <div class="space-y-6 pb-20">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      :modify-bottom="false"
      slug="biographies"
      @save="saveItem"
    />

    <LayoutTabs :tabs="tabs" />

    <Form @submit="saveItem">
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
        <div class="flex flex-col gap-8">
          <LayoutContainer>
            <LayoutTitle>Základní informace</LayoutTitle>
            <div class="grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-2 lg:grid-cols-4">
              <BaseFormInput
                v-model="item.name"
                label="Název životopisu (interní)"
                type="text"
                name="name"
                rules="required|min:3"
                class="col-span-1 lg:col-span-2"
              />
              <BaseFormSelect
                v-model="item.template"
                :options="[
                  { name: 'Výchozí šablona', value: 'default' },
                  { name: 'Výchozí šablona - anglická verze', value: 'default_en' },
                ]"
                label="Šablona"
                name="template"
                rules="required"
                class="col-span-1 lg:col-span-2"
              />

              <BaseFormInput
                v-model="item.job_title"
                label="Pracovní pozice"
                type="text"
                name="job_title"
                rules="min:3"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.address"
                label="Adresa / Lokalita"
                type="text"
                name="address"
                rules="min:3"
                class="col-span-1 lg:col-span-2"
              />

              <div class="hidden lg:col-span-1 lg:block"></div>

              <div class="col-span-1 space-y-2">
                <div class="flex items-end gap-2">
                  <BaseFormInput
                    v-model="item.email"
                    label="E-mail"
                    type="text"
                    name="email"
                    rules="required|min:3"
                    class="flex-1"
                  />
                  <BaseButton
                    type="button"
                    variant="secondary"
                    size="md"
                    class="mb-0.5 shadow-none ring-slate-200"
                    @click="takeEmailFromProfile"
                  >
                    <ArrowDownOnSquareIcon class="size-4 sm:hidden" />
                    <span class="hidden text-xs sm:inline">Z profilu</span>
                  </BaseButton>
                </div>
              </div>

              <div class="col-span-1 space-y-2">
                <div class="flex items-end gap-2">
                  <BaseFormInput
                    v-model="item.phone_prefix"
                    label="Předčíslí"
                    type="text"
                    name="phone_prefix"
                    rules="min:3"
                    class="w-24"
                  />
                  <BaseFormInput
                    v-model="item.phone"
                    label="Telefon"
                    type="text"
                    name="phone"
                    rules="required|min:3"
                    class="flex-1"
                  />
                  <BaseButton
                    v-if="user.phone"
                    type="button"
                    variant="secondary"
                    size="md"
                    class="mb-0.5 shadow-none ring-slate-200"
                    @click="takePhoneFromProfile"
                  >
                    <span class="hidden text-xs sm:inline">Z profilu</span>
                  </BaseButton>
                </div>
              </div>

              <div
                class="col-span-full grid grid-cols-1 gap-6 border-t border-slate-100 pt-4 md:grid-cols-3"
              >
                <BaseFormInput v-model="item.linkedin" label="LinkedIn" name="linkedin" />
                <BaseFormInput v-model="item.github" label="GitHub" name="github" />
                <BaseFormInput v-model="item.website" label="Web" name="website" />
              </div>

              <BaseFormTextarea
                v-model="item.about_me"
                label="O mně"
                name="about_me"
                class="col-span-full lg:col-span-2"
                :max="1000"
                rows="4"
              />
              <BaseFormTextarea
                v-model="item.summary"
                label="Shrnutí / Cíle"
                name="summary"
                class="col-span-full lg:col-span-2"
                :max="1000"
                rows="4"
              />
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-8 flex items-center justify-between">
              <LayoutTitle class="!mb-0">Dovednosti</LayoutTitle>
              <BaseButton type="button" variant="primary" @click="addSkillGroup">
                Přidat skupinu
              </BaseButton>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
              <div
                v-for="(group, index) in item.skills"
                :key="index"
                class="relative rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200 transition-all hover:shadow-lg hover:shadow-slate-200/50"
              >
                <div class="mb-6 flex items-center gap-4">
                  <BaseFormInput
                    v-model="group.name"
                    :label="`Skupina #${index + 1}`"
                    type="text"
                    :name="`skills[${index}][name]`"
                    rules="required"
                    class="flex-1"
                  />
                  <BaseButton
                    type="button"
                    variant="danger"
                    size="md"
                    class="mt-6"
                    @click="removeSkillGroup(index)"
                  >
                    Smazat
                  </BaseButton>
                </div>

                <div class="space-y-4">
                  <div
                    v-for="(skill, key) in group.skills"
                    :key="key"
                    class="grid grid-cols-12 items-end gap-3 rounded-2xl bg-white p-3 ring-1 ring-slate-100"
                  >
                    <BaseFormInput
                      v-model="skill.name"
                      label="Dovednost"
                      type="text"
                      :name="`skills[${index}][skills][${key}][name]`"
                      rules="required"
                      class="col-span-6"
                    />
                    <BaseFormInput
                      v-model="skill.level"
                      label="Úroveň %"
                      type="number"
                      :name="`skills[${index}][skills][${key}][level]`"
                      class="col-span-4"
                    />
                    <BaseButton
                      type="button"
                      variant="danger"
                      size="md"
                      class="col-span-2 mb-0.5 flex h-[42px] justify-center"
                      @click="removeSkill(index, key)"
                    >
                      <TrashIcon class="size-4" />
                    </BaseButton>
                  </div>
                </div>

                <div class="mt-6 text-center">
                  <BaseButton
                    type="button"
                    variant="secondary"
                    size="md"
                    class="w-full border-dashed bg-transparent ring-slate-300"
                    @click="addSkill(index)"
                  >
                    + Přidat dovednost
                  </BaseButton>
                </div>
              </div>
            </div>
          </LayoutContainer>
        </div>
      </template>

      <template v-if="tabs.find((tab) => tab.current && tab.link === '#pracovni-zkusenosti')">
        <LayoutContainer>
          <div class="mb-8 flex items-center justify-between">
            <LayoutTitle class="!mb-0">Pracovní zkušenosti</LayoutTitle>
            <BaseButton size="lg" type="button" @click="addJobExperience">
              + Přidat zkušenost
            </BaseButton>
          </div>

          <div class="space-y-8">
            <div
              v-for="(experience, index) in item.job_experiences"
              :key="index"
              class="group relative rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200 lg:p-8"
            >
              <div class="grid grid-cols-1 gap-8 lg:grid-cols-4">
                <div class="space-y-5 lg:col-span-1">
                  <BaseFormInput
                    v-model="experience.company"
                    label="Společnost"
                    :name="`job_experiences[${index}][company]`"
                    rules="required"
                  />
                  <BaseFormInput
                    v-model="experience.position"
                    label="Pozice"
                    :name="`job_experiences[${index}][position]`"
                    rules="required"
                  />
                  <div class="grid grid-cols-2 gap-4">
                    <BaseFormInput
                      v-model="experience.start_date"
                      label="Od"
                      type="date"
                      name="..."
                    />
                    <BaseFormInput
                      v-model="experience.end_date"
                      label="Do"
                      type="date"
                      name="..."
                    />
                  </div>
                </div>

                <div class="space-y-6 lg:col-span-3">
                  <BaseFormTextarea
                    v-model="experience.description"
                    label="Náplň práce a úspěchy"
                    rows="6"
                    :name="`job_experiences[${index}][description]`"
                  />

                  <div class="border-t border-slate-200 pt-4">
                    <span
                      class="mb-4 block text-xs font-bold uppercase tracking-widest text-slate-400"
                      >Použité technologie / Dovednosti</span
                    >
                    <div class="flex flex-wrap gap-3">
                      <div
                        v-for="(skill, key) in experience.skills"
                        :key="key"
                        class="flex items-center gap-2 rounded-xl bg-white p-1.5 ring-1 ring-slate-200"
                      >
                        <input
                          v-model="skill.name"
                          class="w-24 border-none p-0 text-xs font-medium focus:ring-0"
                          placeholder="Dovednost..."
                        />
                        <button
                          class="text-slate-400 hover:text-red-500"
                          @click="removeJobExperienceSkill(index, key)"
                        >
                          <XMarkIcon class="size-3" />
                        </button>
                      </div>
                      <BaseButton
                        type="button"
                        variant="secondary"
                        size="sm"
                        class="rounded-xl border-dashed"
                        @click="addJobExperienceSkill(index)"
                      >
                        +
                      </BaseButton>
                    </div>
                  </div>
                </div>
              </div>

              <div
                class="absolute right-4 top-4 opacity-0 transition-opacity group-hover:opacity-100"
              >
                <BaseButton variant="danger" size="sm" @click="removeJobExperience(index)">
                  Odstranit
                </BaseButton>
              </div>
            </div>
          </div>
        </LayoutContainer>
      </template>

      <template v-if="tabs.find((tab) => tab.current && tab.link === '#vzdelani')">
        <LayoutContainer>
          <div class="mb-8 flex items-center justify-between">
            <LayoutTitle class="!mb-0">Dosažené vzdělání</LayoutTitle>
            <BaseButton size="lg" type="button" @click="addEducation">
              + Přidat vzdělání
            </BaseButton>
          </div>

          <div class="space-y-6">
            <div
              v-for="(education, index) in item.education"
              :key="index"
              class="group relative rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200"
            >
              <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                <div class="space-y-4 lg:col-span-1">
                  <BaseFormInput
                    v-model="education.institution"
                    label="Instituce"
                    name="..."
                    rules="required"
                  />
                  <BaseFormInput
                    v-model="education.degree"
                    label="Titul / Obor"
                    name="..."
                    rules="required"
                  />
                  <div class="grid grid-cols-2 gap-4">
                    <BaseFormInput
                      v-model="education.start_date"
                      label="Od"
                      type="date"
                      name="..."
                    />
                    <BaseFormInput v-model="education.end_date" label="Do" type="date" name="..." />
                  </div>
                </div>
                <div class="lg:col-span-3">
                  <BaseFormTextarea
                    v-model="education.description"
                    label="Popis studia"
                    rows="4"
                    name="..."
                  />
                </div>
              </div>
              <div class="mt-4 text-right">
                <BaseButton variant="danger" size="sm" @click="removeEducation(index)">
                  Odstranit toto vzdělání
                </BaseButton>
              </div>
            </div>
          </div>
        </LayoutContainer>
      </template>

      <template v-if="tabs.find((tab) => tab.current && tab.link === '#nahled')">
        <LayoutContainer class="overflow-hidden bg-slate-800 p-0 ring-slate-800">
          <div class="flex min-h-[800px] items-center justify-center bg-slate-700/50 p-8">
            <VPdfViewer
              :src="'/content/biographies/' + item.filename"
              class="rounded-sm shadow-2xl ring-1 ring-black/20"
            />
          </div>
        </LayoutContainer>
      </template>
    </Form>
  </div>
</template>
