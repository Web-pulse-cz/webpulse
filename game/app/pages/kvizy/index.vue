<script setup lang="ts">
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';
import { ChevronUpIcon } from '@heroicons/vue/20/solid';
import { useApi } from '~/composables/useApi';
import { useAsyncData } from '#app';

const search = ref('');

const api = useApi();
const { data: quizzesData } = useAsyncData(`quiz`, () => api.quiz.quizzes(search.value));
const { data: filtersData } = useAsyncData(`quiz-filters`, () => api.quiz.filter());
const selectedFilters = ref([]);

function addToFilters(filter: string) {
  if (selectedFilters.value.includes(filter)) {
    selectedFilters.value = selectedFilters.value.filter((f) => f !== filter);
  } else {
    selectedFilters.value.push(filter);
  }

  api.quiz
    .quizzes(search.value, selectedFilters.value)
    .then((response) => {
      quizzesData.value = response;
    })
    .catch((error) => {
      console.error('Error fetching quizzes:', error);
    });
}
watch(
  search,
  (newSearch) => {
    api.quiz
      .quizzes(newSearch, selectedFilters.value)
      .then((response) => {
        quizzesData.value = response;
      })
      .catch((error) => {
        console.error('Error fetching quizzes:', error);
      });
  },
  { immediate: true },
);

useHead(() => {
  return {
    title: 'Kvízy',
    meta: [
      {
        name: 'description',
        content: 'Zde najdete všechny kvízy, které jsou dostupné na našem webu.',
      },
    ],
  };
});
</script>

<template>
  <div class="grid grid-cols-12 items-baseline gap-y-6 md:gap-x-24 md:gap-y-0">
    <div class="col-span-12 grid grid-cols-1 md:col-span-4">
      <BasePropsHeading type="h1" margin-bottom="mb-0">Kvízy</BasePropsHeading>
      <BaseFormInput v-model="search" type="text" placeholder="Hledat kvízy..." class="w-full" />
      <div v-if="filtersData" class="mt-6 flex flex-wrap gap-2 text-xs md:text-sm">
        <Disclosure v-slot="{ open }">
          <DisclosureButton
            class="flex w-full justify-between rounded-lg bg-primaryLight px-4 py-2 text-left text-sm font-medium text-primaryDark hover:bg-primary focus:outline-none focus-visible:ring focus-visible:ring-purple-500/75"
          >
            <span>Filtrovat podle tagů</span>
            <ChevronUpIcon
              :class="open ? 'rotate-180 transform' : ''"
              class="h-5 w-5 text-primaryDark"
            />
          </DisclosureButton>
          <DisclosurePanel class="flex gap-2 flex-wrap">
            <div
              v-for="(filter, index) in filtersData"
              :key="index"
              :class="[
                selectedFilters.includes(filter.name) ? 'bg-primaryLight' : 'bg-backgroundLight',
                'inline-fex flex w-auto cursor-pointer items-center gap-x-2 rounded-lg p-1.5 ring-1 ring-inset ring-gray-200 hover:bg-gray-200 md:p-2',
              ]"
              @click="addToFilters(filter.name)"
            >
              <span>
                {{ filter.name }}
              </span>
              <span
                class="inline-block h-4 w-4 rounded-full bg-primaryLight text-center md:h-6 md:w-6"
                >{{ filter.count }}</span
              >
            </div>
          </DisclosurePanel>
        </Disclosure>
      </div>
    </div>
    <div v-if="quizzesData" class="col-span-12 grid grid-cols-1 gap-6 md:col-span-8 md:grid-cols-2">
      <QuizCard v-for="(quiz, index) in quizzesData" :key="index" :quiz="quiz" class="col-span-1" />
    </div>
  </div>
</template>
