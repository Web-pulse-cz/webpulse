<script setup lang="ts">
import { useApi } from '~/composables/useApi';
import { useAsyncData } from '#app';

const search = ref('');

const api = useApi();
const { data: quizzesData } = useAsyncData(`quiz`, () => api.quiz.quizzes(search.value));

watch(
  search,
  (newSearch) => {
    api.quiz
      .quizzes(newSearch)
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
  <div>
    <div class="flex items-center justify-between">
      <BasePropsHeading type="h1">Kvízy</BasePropsHeading>
      <BaseFormInput v-model="search" type="text" placeholder="Hledat kvízy..." class="w-64" />
    </div>
    <div v-if="quizzesData" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
      <QuizCard v-for="(quiz, index) in quizzesData" :key="index" :quiz="quiz" class="col-span-1" />
    </div>
  </div>
</template>
