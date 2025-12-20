<script setup lang="ts">
import { ref } from 'vue';
import { VPdfViewer } from '@vue-pdf-viewer/viewer';
import { Form } from 'vee-validate';

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
  <div>
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="biographies"
      @save="saveItem"
    />
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
    <Form @submit="saveItem">
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
        <div class="grid grid-cols-1 gap-x-10">
          <LayoutContainer class="col-span-full w-full">
            <div class="grid grid-cols-4 gap-x-8 gap-y-4">
              <BaseFormInput
                v-model="item.name"
                label="Název"
                type="text"
                name="name"
                rules="required|min:3"
                class="col-span-1"
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
                class="col-span-1"
              />
              <div class="col-span-2"></div>
              <BaseFormInput
                v-model="item.job_title"
                label="Název pracovní pozice"
                type="text"
                name="job_title"
                rules="min:3"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.address"
                label="Adresa"
                type="text"
                name="address"
                rules="min:3"
                class="col-span-2"
              />
              <div class="col-span-1"></div>
              <div class="col-span-1 flex items-end">
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
                  size="xl"
                  @click="takeEmailFromProfile"
                  >Převzít z profilu</BaseButton
                >
              </div>
              <div class="col-span-1 flex items-end">
                <BaseFormInput
                  v-model="item.phone_prefix"
                  label="Předčíslí"
                  type="text"
                  name="phone_prefix"
                  rules="min:3"
                  class="flex-1"
                />
                <BaseButton
                  v-if="user.phone_prefix"
                  type="button"
                  variant="secondary"
                  size="xl"
                  @click="takePhonePrefixFromProfile"
                  >Převzít z profilu</BaseButton
                >
              </div>
              <div class="col-span-1 flex items-end">
                <BaseFormInput
                  v-model="item.phone"
                  label="Telefonní číslo"
                  type="text"
                  name="phone"
                  rules="required|min:3"
                  class="flex-1"
                />
                <BaseButton
                  v-if="user.phone"
                  type="button"
                  variant="secondary"
                  size="xl"
                  @click="takePhoneFromProfile"
                  >Převzít z profilu</BaseButton
                >
              </div>
              <div class="col-span-1"></div>
              <BaseFormInput
                v-model="item.linkedin"
                label="Odkaz na LinkedIn"
                type="text"
                name="linkedin"
                rules="min:3"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.github"
                label="Odkaz na GitHub"
                type="text"
                name="github"
                rules="min:3"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.website"
                label="Odkaz na webovou stránku"
                type="text"
                name="website"
                rules="min:3"
                class="col-span-1"
              />
              <BaseFormTextarea
                v-model="item.about_me"
                label="O mně"
                name="about_me"
                rules="min:3"
                class="col-span-2"
                :max="1000"
              />
              <BaseFormTextarea
                v-model="item.summary"
                label="Shrnutí"
                name="summary"
                rules="min:3"
                class="col-span-2"
                :max="1000"
              />
            </div>
          </LayoutContainer>
          <LayoutContainer class="col-span-full w-full">
            <LayoutTitle>Dovednosti</LayoutTitle>
            <div class="grid grid-cols-2 gap-x-8 gap-y-4">
              <div
                v-for="(group, index) in item.skills"
                :key="index"
                class="col-span-1 grid grid-cols-3 items-end gap-4 rounded-lg bg-gray-100 p-4"
              >
                <BaseFormInput
                  v-model="group.name"
                  :label="`Název skupiny dovedností #${index + 1}`"
                  type="text"
                  :name="`skills[${index}][name]`"
                  rules="required|min:2"
                  class="col-span-2"
                />
                <BaseButton
                  type="button"
                  variant="danger"
                  size="xl"
                  class="col-span-1"
                  @click="removeSkillGroup(index)"
                  >Smazat skupinu</BaseButton
                >
                <LayoutDivider />
                <div
                  v-for="(skill, key) in group.skills"
                  :key="key"
                  class="col-span-3 grid grid-cols-6 gap-4"
                >
                  <BaseFormInput
                    v-model="skill.name"
                    :label="`Dovednost #${key + 1}`"
                    type="text"
                    :name="`skills[${index}][skills][${key}][name]`"
                    rules="required|min:2"
                    class="col-span-3"
                  />
                  <BaseFormInput
                    v-model="skill.level"
                    :label="`Úroveň dovednosti #${key + 1} (0-100)`"
                    type="number"
                    :name="`skills[${index}][skills][${key}][level]`"
                    rules="required|min:2"
                    class="col-span-2"
                  />
                  <BaseButton
                    type="button"
                    variant="danger"
                    size="xl"
                    class="col-span-1 mt-6"
                    @click="removeSkill(index, key)"
                    >Smazat</BaseButton
                  >
                </div>
                <div class="col-span-full text-center">
                  <BaseButton type="button" variant="primary" size="xl" @click="addSkill(index)"
                    >Přidat dovednost</BaseButton
                  >
                </div>
              </div>
            </div>
            <BaseButton
              type="button"
              variant="primary"
              size="xl"
              class="mt-4"
              @click="addSkillGroup"
              >Přidat skupinu dovedností</BaseButton
            >
          </LayoutContainer>
        </div>
      </template>
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#pracovni-zkusenosti')">
        <div class="grid grid-cols-1 gap-x-10">
          <LayoutContainer class="col-span-full w-full">
            <div
              v-for="(experience, index) in item.job_experiences"
              :key="index"
              class="my-4 mb-6 grid grid-cols-4 gap-x-8 rounded-lg bg-gray-100 p-4"
            >
              <div class="col-span-1">
                <BaseFormInput
                  v-model="experience.company"
                  :label="`Společnost #${index + 1}`"
                  type="text"
                  :name="`job_experiences[${index}][company]`"
                  rules="required|min:3"
                />
                <BaseFormInput
                  v-model="experience.position"
                  :label="`Pozice #${index + 1}`"
                  type="text"
                  :name="`job_experiences[${index}][position]`"
                  rules="required|min:3"
                />
              </div>
              <div class="col-span-1">
                <BaseFormInput
                  v-model="experience.start_date"
                  :label="`Datum začátku #${index + 1}`"
                  type="date"
                  :name="`job_experiences[${index}][start_date]`"
                  rules="required|date"
                />
                <BaseFormInput
                  v-model="experience.end_date"
                  :label="`Datum konce #${index + 1}`"
                  type="date"
                  :name="`job_experiences[${index}][end_date]`"
                  rules="date"
                />
              </div>
              <BaseFormTextarea
                v-model="experience.description"
                :label="`Popis práce #${index + 1}`"
                :name="`job_experiences[${index}][description]`"
                rules="min:3"
                class="col-span-2"
                :max="1000"
              />
              <LayoutDivider />
              <div class="col-span-4 grid grid-cols-8 gap-4">
                <div v-for="(skill, key) in experience.skills" :key="key" class="col-span-1">
                  <BaseFormInput
                    v-model="skill.name"
                    :label="`Dovednost #${key + 1}`"
                    type="text"
                    :name="`job_experiences[${index}][skills][${key}][name]`"
                    rules="required|min:2"
                  />
                </div>
                <div class="col-span-1">
                  <BaseButton
                    type="button"
                    variant="primary"
                    size="xl"
                    @click="addJobExperienceSkill(index)"
                    >Přidat dovednost</BaseButton
                  >
                </div>
              </div>
              <div class="col-span-full mt-4 text-center">
                <BaseButton type="button" variant="danger" size="xl" @click="removeEducation(index)"
                  >Odstranit pracovní zkušenost</BaseButton
                >
              </div>
            </div>
            <div class="text-center">
              <BaseButton size="xl" type="button" @click="addJobExperience"
                >Přidat pracovní zkušenost</BaseButton
              >
            </div>
          </LayoutContainer>
        </div>
      </template>
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#vzdelani')">
        <div class="grid grid-cols-1 gap-x-10">
          <LayoutContainer class="col-span-full w-full">
            <div
              v-for="(education, index) in item.education"
              :key="index"
              class="my-4 mb-6 grid grid-cols-4 gap-x-8 rounded-lg bg-gray-100 p-4"
            >
              <div class="col-span-1">
                <BaseFormInput
                  v-model="education.institution"
                  :label="`Instituce #${index + 1}`"
                  type="text"
                  :name="`education[${index}][institution]`"
                  rules="required|min:3"
                />
                <BaseFormInput
                  v-model="education.degree"
                  :label="`Nejvyšší dosažené vzdělání #${index + 1}`"
                  type="text"
                  :name="`education[${index}][degree]`"
                  rules="required|min:3"
                />
              </div>
              <div class="col-span-1">
                <BaseFormInput
                  v-model="education.start_date"
                  :label="`Datum začátku #${index + 1}`"
                  type="date"
                  :name="`education[${index}][start_date]`"
                  rules="required|date"
                />
                <BaseFormInput
                  v-model="education.end_date"
                  :label="`Datum konce #${index + 1}`"
                  type="date"
                  :name="`education[${index}][end_date]`"
                  rules="date"
                />
              </div>
              <BaseFormTextarea
                v-model="education.description"
                :label="`Popis průběhu vzdělání #${index + 1}`"
                :name="`education[${index}][description]`"
                rules="min:3"
                class="col-span-2"
                :max="1000"
              />
              <div class="col-span-full">
                <BaseButton type="button" variant="danger" size="xl" @click="removeEducation(index)"
                  >Odstranit vzdělání</BaseButton
                >
              </div>
            </div>
            <div class="text-center">
              <BaseButton size="xl" type="button" @click="addEducation">Přidat vzdělání</BaseButton>
            </div>
          </LayoutContainer>
        </div>
      </template>
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#nahled')">
        <LayoutContainer>
          <VPdfViewer :src="'/content/biographies/' + item.filename" />
        </LayoutContainer>
      </template>
    </Form>
  </div>
</template>

<style scoped>
.vpv-sidebar-wrapper {
  display: none !important;
}
</style>
