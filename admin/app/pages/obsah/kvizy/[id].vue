<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';

const { $toast } = useNuxtApp();

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
    },
  })
    .then((response) => {
      item.value = response;
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
        detail: 'Nepodařilo se načíst novinku. Zkuste to prosím později.',
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
  const quizUrl = `https://client.martinhanzl.cz/kvizy/${item.value.id}/${item.value.slug}`;
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
  <div>
    <LayoutHeader
      :title="route.params.id === 'pridat' ? 'Nový kvíz' : item.name"
      :breadcrumbs="breadcrumbs"
      :actions="
        route.params.id === 'pridat'
          ? [{ type: 'save' }]
          : [
              { type: 'copy', text: 'Kopírovat odkaz na kvíz' },
              { type: 'save' },
              { type: 'save-and-stay' },
            ]
      "
      slug="quizzes"
      @copy="copyQuizUrl"
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
        <div class="grid grid-cols-1 items-start gap-x-4 gap-y-8 lg:grid-cols-7">
          <LayoutContainer class="col-span-4 w-full">
            <div class="grid grid-cols-2 gap-x-8 gap-y-4">
              <BaseFormInput
                v-model="item.name"
                label="Název kvízu"
                name="name"
                required
                class="col-span-2"
              />
              <BaseFormInput
                v-model="item.tags"
                label="Štítky"
                name="tags"
                placeholder="např. sport, zábava, historie"
                class="col-span-2"
              />
              <BaseFormEditor
                key="description"
                v-model="item.description"
                label="Popis"
                name="description"
                class="col-span-2"
              />
            </div>
          </LayoutContainer>
          <LayoutContainer class="col-span-3 w-full">
            <div class="col-span-1">
              <BaseFormSelect
                v-model="item.status"
                label="Stav"
                name="status"
                :options="[
                  { value: 'draft', name: 'Koncept' },
                  { value: 'public', name: 'Veřejný' },
                  { value: 'private', name: 'Soukromý' },
                  { value: 'archived', name: 'Archivováno' },
                ]"
                class="pt-6"
              />
            </div>
          </LayoutContainer>
        </div>
      </template>
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#otazky')">
        <LayoutContainer class="grid grid-cols-1 items-start gap-x-4 gap-y-8 lg:grid-cols-7">
          <div
            v-for="(question, index) in item.questions"
            class="col-span-full grid grid-cols-7 rounded-lg bg-gray-100 p-6"
          >
            <div class="col-span-6">
              <BaseFormUploadImage
                v-model="question.image"
                :multiple="false"
                type="quiz"
                format="large"
                label="Obrázek"
                class="pt-6"
                @update-files="updateQuestionImage($event, index)"
                @remove-file="removeQuestionImage(index)"
              />
              <BaseFormInput
                v-model="question.name"
                :label="'Otázka ' + (index + 1)"
                :name="'question_' + index"
                required
              />
            </div>
            <div class="col-span-1 flex items-center justify-center">
              <BaseButton
                type="button"
                variant="danger"
                size="sm"
                @click="removeItemQuestion(index)"
              >
                Odstranit otázku
              </BaseButton>
            </div>
            <LayoutDivider />
            <div class="col-span-full mt-2 grid grid-cols-1 gap-4">
              <div v-for="(answer, answerIndex) in question.answers" :key="answerIndex">
                <div class="flex items-end gap-4">
                  <BaseFormInput
                    v-model="answer.name"
                    :label="'Odpověď ' + (answerIndex + 1)"
                    :name="'answer_' + answerIndex + '_' + index"
                    class="flex-1"
                    required
                  />
                  <!-- Button for remove answer -->
                  <BaseButton
                    v-if="question.answers.length > 2"
                    type="button"
                    variant="danger"
                    size="sm"
                    class="col-span-2 mt-2"
                    :disabled="question.answers.length <= 2"
                    @click="question.answers.splice(answerIndex, 1)"
                    >Odstranit odpověď</BaseButton
                  >
                </div>
                <BaseFormCheckbox
                  v-model="answer.is_correct"
                  :label="'Správná odpověď ' + (answerIndex + 1)"
                  :name="'isCorrect_' + answerIndex + '_' + index"
                />
              </div>
              <BaseButton
                type="button"
                variant="primary"
                size="sm"
                class="mt-2"
                @click="question.answers.push({ id: null, name: '', is_correct: false })"
              >
                Přidat odpověď
              </BaseButton>
            </div>
          </div>
          <div class="col-span-full text-center">
            <BaseButton type="button" variant="primary" size="lg" class="mb-4" @click="addQuestion">
              Přidat otázku
            </BaseButton>
          </div>
        </LayoutContainer>
      </template>
    </Form>
  </div>
</template>
