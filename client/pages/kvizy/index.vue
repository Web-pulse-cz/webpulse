<script setup lang="ts">
import { useApi } from '~/composables/useApi';
import { useAsyncData } from '#app';

definePageMeta({
  layout: 'quiz',
});

const api = useApi();
const {
  data: quizzesData,
  status: quizzesStatus,
  error: quizzesError,
  pending: quizzesPending,
} = useAsyncData(`quiz`, () => api.quiz.quizzes());

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
    <BasePropsHeading type="h1">Kvízy</BasePropsHeading>
    <div v-if="quizzesData" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
      <QuizCard v-for="(quiz, index) in quizzesData" :key="index" :quiz="quiz" class="col-span-1" />
    </div>
  </div>
</template>
