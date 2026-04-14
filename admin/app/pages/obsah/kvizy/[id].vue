<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { TrashIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const user = useSanctumUser();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const tabs = ref([
  { name: 'Základní údaje', link: '#info', current: false },
  { name: 'Otázky', link: '#otazky', current: false },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový kvíz' : 'Detail kvízu');

const breadcrumbs = ref([
  {
    name: 'Kvízy',
    link: '/obsah/kvizy',
    current: false,
  },
  {
    name: 'Nový kvíz',
    link: '/obsah/kvizy/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  slug: '' as string,
  description: '' as string,
  tags: '' as string,
  status: 'draft' as string,
  questions: [] as [],
  sites: [] as number[],
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    slug: string;
    description: string;
    tags: string;
    status: string;
    questions: [];
  }>('/api/admin/quiz/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response) => {
      item.value = response;
      item.value.sites = response.sites.map((site) => site.id);
      breadcrumbs.value.pop();
      breadcrumbs.value.push({
        name: item.value.name,
        link: '/obsah/kvizy/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst kvíz. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/obsah/kvizy');
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
    slug: string;
    description: string;
    tags: string;
    status: string;
    questions: [];
  }>(route.params.id === 'pridat' ? '/api/admin/quiz' : '/api/admin/quiz/' + route.params.id, {
    method: 'POST',
    body: JSON.stringify(item.value),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      $toast.show({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat' ? 'Kvíz byl úspěšně vytvořen.' : 'Kvíz byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/kvizy/' + response.id);
      } else if (redirect) {
        router.push('/obsah/kvizy');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit kvíz. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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

function addQuestion() {
  item.value.questions.push({
    id: null,
    name: '',
    answers: [
      { id: null, name: '', is_correct: false },
      { id: null, name: '', is_correct: false },
      { id: null, name: '', is_correct: false },
      { id: null, name: '', is_correct: false },
    ],
  });
}

function copyQuizUrl() {
  const quizUrl = `https://hry.martinhanzl.cz/kvizy/${item.value.id}/${item.value.slug}`;
  navigator.clipboard
    .writeText(quizUrl)
    .then(() => {
      $toast.show({
        title: 'Odkaz zkopírován',
        description: 'Odkaz na kvíz byl úspěšně zkopírován do schránky.',
        color: 'green',
      });
    })
    .catch(() => {
      $toast.show({
        title: 'Chyba',
        description: 'Nepodařilo se zkopírovat odkaz do schránky.',
        color: 'red',
      });
    });
}

function removeItemQuestion(index: number) {
  item.value.questions.splice(index, 1);
}

function updateQuestionImage(files, index) {
  item.value.questions[index].image = files[0];
}

function removeQuestionImage(index) {
  item.value.questions[index].image = null;
}

watch(selectedSiteHash, () => {
  loadItem();
});

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
  <div class="space-y-6 pb-24">
    <LayoutHeader
      :title="route.params.id === 'pridat' ? 'Nový kvíz' : item.name"
      :breadcrumbs="breadcrumbs"
      :actions="
        route.params.id === 'pridat'
          ? [{ type: 'save' }]
          : [{ type: 'copy', text: 'Kopírovat odkaz' }, { type: 'save' }, { type: 'save-and-stay' }]
      "
      :modify-bottom="false"
      slug="quizzes"
      @copy="copyQuizUrl"
      @save="saveItem"
    />

    <LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

    <Form @submit="saveItem">
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
          <div class="col-span-1 space-y-8 lg:col-span-9">
            <LayoutContainer>
              <div class="mb-8 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                >
                  <InformationCircleIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Základní nastavení kvízu</LayoutTitle>
              </div>

              <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <BaseFormInput
                  v-model="item.name"
                  label="Název kvízu"
                  name="name"
                  required
                  class="col-span-full"
                  placeholder="Např. Poznáte české barbershopy?"
                />
                <div class="col-span-full">
                  <BaseFormInput
                    v-model="item.tags"
                    label="Štítky (Tags)"
                    name="tags"
                    placeholder="sport, zábava, historie..."
                  />
                  <p class="mt-2 text-xs italic text-slate-400">
                    Pomáhá při vyhledávání a filtrování na webu.
                  </p>
                </div>

                <div class="col-span-full pt-4">
                  <BaseFormEditor
                    key="description"
                    v-model="item.description"
                    label="Úvodní popis kvízu"
                    name="description"
                  />
                </div>
              </div>
            </LayoutContainer>
          </div>

          <div class="col-span-1 lg:sticky lg:top-24 lg:col-span-3">
            <LayoutActionsDetailBlock
              v-model:state="item.status"
              v-model:sites="item.sites"
              :allow-image="false"
              :states="[
                { value: 'draft', name: 'Koncept' },
                { value: 'public', name: 'Veřejný' },
                { value: 'private', name: 'Soukromý' },
                { value: 'archived', name: 'Archivováno' },
              ]"
              :allow-state="true"
              image-type="quiz"
            />
          </div>
        </div>
      </template>

      <template v-if="tabs.find((tab) => tab.current && tab.link === '#otazky')">
        <div class="space-y-8">
          <div
            v-for="(question, index) in item.questions"
            :key="index"
            class="relative rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition-all hover:shadow-md lg:p-8"
          >
            <div class="mb-8 flex items-start justify-between gap-4">
              <div class="flex items-center gap-3">
                <span
                  class="flex size-10 items-center justify-center rounded-xl bg-slate-900 text-lg font-bold text-white shadow-lg"
                >
                  {{ index + 1 }}
                </span>
                <LayoutTitle class="!mb-0">Otázka</LayoutTitle>
              </div>
              <BaseButton
                type="button"
                variant="danger"
                size="md"
                class="rounded-xl shadow-none ring-1 ring-red-200 hover:bg-red-50"
                @click="removeItemQuestion(index)"
              >
                <TrashIcon class="size-5" />
              </BaseButton>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
              <div class="col-span-1 space-y-6 lg:col-span-8">
                <div class="md:hidden">
                  <BaseFormUploadImage
                    v-model="question.image"
                    :multiple="false"
                    type="quiz"
                    format="large"
                    label="Obrázek k otázce"
                    :allow-remote-url="true"
                    @update-files="updateQuestionImage($event, index)"
                    @remove-file="removeQuestionImage(index)"
                  />
                </div>

                <BaseFormInput
                  v-model="question.name"
                  label="Znění otázky"
                  :name="'question_' + index"
                  required
                  placeholder="Co se stane, když..."
                />

                <div class="pt-4">
                  <span
                    class="mb-4 block text-[11px] font-bold uppercase tracking-widest text-slate-400"
                    >Možnosti odpovědí</span
                  >
                  <div class="grid grid-cols-1 gap-4">
                    <div
                      v-for="(answer, answerIndex) in question.answers"
                      :key="answerIndex"
                      class="group flex flex-col gap-3 rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200/60 transition-all hover:bg-slate-100/50"
                    >
                      <div class="flex items-end gap-3">
                        <BaseFormInput
                          v-model="answer.name"
                          :label="'Odpověď ' + (answerIndex + 1)"
                          :name="'answer_' + answerIndex + '_' + index"
                          class="flex-1"
                          required
                        />
                        <BaseButton
                          v-if="question.answers.length > 2"
                          type="button"
                          variant="danger"
                          size="md"
                          class="mb-0.5 rounded-xl bg-white shadow-sm ring-1 ring-slate-200 hover:text-red-600"
                          @click="question.answers.splice(answerIndex, 1)"
                        >
                          <TrashIcon class="size-4" />
                        </BaseButton>
                      </div>

                      <div
                        class="flex items-center rounded-lg bg-white/60 p-2 px-3 ring-1 ring-inset ring-slate-200/50"
                      >
                        <BaseFormCheckbox
                          v-model="answer.is_correct"
                          :label="'Označit jako správnou'"
                          :name="'isCorrect_' + answerIndex + '_' + index"
                          class="w-full flex-row-reverse justify-between font-bold text-slate-700"
                        />
                      </div>
                    </div>
                  </div>

                  <div class="mt-6">
                    <BaseButton
                      type="button"
                      variant="secondary"
                      size="sm"
                      class="w-full border-dashed bg-transparent ring-slate-300"
                      @click="question.answers.push({ id: null, name: '', is_correct: false })"
                    >
                      + Přidat další odpověď
                    </BaseButton>
                  </div>
                </div>
              </div>

              <div class="hidden lg:col-span-4 lg:block">
                <div class="sticky top-4 rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200">
                  <BaseFormUploadImage
                    v-model="question.image"
                    :multiple="false"
                    type="quiz"
                    format="large"
                    label="Obrázek k otázce"
                    :allow-remote-url="true"
                    @update-files="updateQuestionImage($event, index)"
                    @remove-file="removeQuestionImage(index)"
                  />
                  <p class="mt-4 text-center text-xs italic text-slate-400">
                    Obrázek pomůže uživatelům lépe pochopit kontext otázky.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="pt-4 text-center">
            <BaseButton
              type="button"
              variant="primary"
              size="xl"
              class="shadow-indigo-200 ring-4 ring-indigo-50"
              @click="addQuestion"
            >
              <PlusIcon class="mr-2 size-5" />
              Nová otázka do kvízu
            </BaseButton>
          </div>
        </div>
      </template>
    </Form>
  </div>
</template>
