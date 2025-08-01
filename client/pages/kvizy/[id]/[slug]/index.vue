<script setup lang="ts">
import { useApi } from '~/composables/useApi';
import { useAsyncData } from '#app';

definePageMeta({
  layout: 'quiz',
});

const route = useRoute();

const stats = ref([]);
const quizStarted = ref(false);
const quizFinished = ref(false);
const progress = ref(0);
const currentQuestionIndex = ref(0);

function getNextQuestion() {
  if (currentQuestionIndex.value < quizData.value?.questions.length - 1) {
    currentQuestionIndex.value++;
    progress.value = ((currentQuestionIndex.value + 1) / quizData.value?.questions.length) * 100;
  }
}

function getPreviousQuestion() {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--;
    progress.value = (currentQuestionIndex.value / quizData.value?.questions.length) * 100;
  }
}

function markSelected(answer) {
  if (quizData?.value && quizData.value.questions[currentQuestionIndex.value]) {
    const currentQuestion = quizData.value.questions[currentQuestionIndex.value];
    currentQuestion.answers.forEach((ans) => {
      if (ans.id === answer.id) {
        ans.is_selected = true; // Toggle selection
      } else {
        ans.is_selected = false; // Deselect other answers
      }
    });
  }
}

// send the quiz data to the server when finished
function submitQuiz() {
  if (quizData.value) {
    api.quiz
      .submit(route.params.id, quizData.value)
      .then((response) => {
        console.log(response);
        stats.value = response;
        console.log(stats.value);
        quizFinished.value = true;
      })
      .catch((error) => {
        console.error('Error submitting quiz:', error);
      });
  }
}

const api = useApi();
const {
  data: quizData,
  status: quizStatus,
  error: quizError,
  pending: quizPending,
} = useAsyncData(`quiz-${route.params.id}`, () => api.quiz.quiz(route.params.id));

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
    <div
      :class="[quizStarted ? 'justify-start' : 'justify-center', 'mb-6 flex flex-col items-center']"
    >
      <BasePropsHeading type="h1">{{ quizData?.name }}</BasePropsHeading>
      <p
        v-if="!quizStarted"
        class="mb-4 text-center text-gray-600 dark:text-gray-400"
        v-html="quizData?.description"
      />
      <p v-if="!quizStarted">Počet otázek: {{ quizData?.questions.length }}</p>
      <p v-if="!quizStarted">Průměrná úspěšnost: {{ quizData?.accuracy }}%</p>
      <BaseButton
        v-if="!quizStarted"
        size="xxl"
        variant="primary"
        class="mt-6"
        @click="quizStarted = true"
        >Zahájit kvíz</BaseButton
      >
    </div>
    <div v-if="quizStarted && !quizFinished" class="mt-6">
      <div class="mt-6" aria-hidden="true">
        <div class="overflow-hidden rounded-full bg-gray-200">
          <div class="h-2 rounded-full bg-primary" :style="`width: ${progress}%`" />
        </div>
      </div>
    </div>
    <div v-if="quizStarted && !quizFinished" class="mt-12">
      <p
        class="mb-4 text-center font-bold text-primary"
        v-html="quizData?.questions[currentQuestionIndex]?.name"
      />
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:gap-6">
        <div
          v-for="(answer, index) in quizData?.questions[currentQuestionIndex]?.answers"
          :key="index"
          :class="[
            answer.is_selected ? 'bg-primary text-white' : 'bg-white text-gray-500',
            'cursor-pointer rounded-lg p-4 text-sm shadow lg:p-6',
          ]"
          @click="markSelected(answer)"
        >
          <pre>{{ answer.name }}</pre>
        </div>
      </div>
    </div>
    <div v-if="quizStarted && !quizFinished" class="mt-12 flex justify-between">
      <div v-if="currentQuestionIndex === 0">&nbsp;</div>
      <BaseButton
        v-if="currentQuestionIndex > 0"
        variant="secondary"
        size="xxl"
        :disabled="currentQuestionIndex === 0"
        @click="getPreviousQuestion"
      >
        Předchozí</BaseButton
      >
      <BaseButton
        v-if="currentQuestionIndex < quizData?.questions.length - 1"
        variant="secondary"
        size="xxl"
        @click="getNextQuestion"
        >Další</BaseButton
      >
      <BaseButton v-else variant="secondary" size="xxl" @click="submitQuiz"
        >Dokončit kvíz</BaseButton
      >
    </div>
    <div v-if="quizFinished" class="mb-6 flex flex-col items-center justify-center">
      <BasePropsHeading type="h2" class="mt-12">Výsledky kvízu</BasePropsHeading>
      <p class="mb-4 text-gray-600 dark:text-gray-400">
        Počet správných odpovědí: {{ stats?.correctAnswers }} z {{ quizData?.questions.length }}
      </p>
      <p class="mb-4 text-gray-600 dark:text-gray-400">Vaše úspěšnost: {{ stats?.accuracy }}%</p>
      <NuxtLink to="/kvizy">
        <BaseButton size="xxl" variant="primary">Zahrát si další kvízy</BaseButton>
      </NuxtLink>
    </div>
    <NuxtLink
      to="/kvizy"
      class="relative bottom-2 left-1/2 mt-6 inline-block -translate-x-1/2 transform rounded-lg p-2 text-center text-xs text-primary ring-1 ring-primary lg:hidd"
    >
      Zpět na kvízy
    </NuxtLink>
  </div>
</template>
