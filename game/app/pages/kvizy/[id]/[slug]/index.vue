<script setup lang="ts">
import { useApi } from '~/composables/useApi';
import { useAsyncData } from '#app';

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
        ans.is_selected = true;
      } else {
        ans.is_selected = false;
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
        stats.value = response;
        quizFinished.value = true;
      })
      .catch((error) => {
        console.error('Error submitting quiz:', error);
      });
  }
}

const api = useApi();
const quizData = ref(null);

const { data: quizDataRaw } = await useAsyncData(`quiz-${route.params.id}`, () =>
  api.quiz.quiz(route.params.id),
);

// Po načtení převeď data na reaktivní objekt
watchEffect(() => {
  if (quizDataRaw.value) {
    // vytvoříme hlubokou kopii, abychom zajistili reaktivitu všech úrovní
    quizData.value = reactive(JSON.parse(JSON.stringify(quizDataRaw.value)));
  }
});

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
    <div class="mb-6 flex flex-col items-center justify-center">
      <BasePropsHeading
        type="h1"
        class="text-center"
        :margin-bottom="!quizStarted ? 'mb-8' : 'mb-0'"
        >{{ quizData?.name }}</BasePropsHeading
      >
      <p
        v-if="!quizStarted"
        class="mb-4 text-center text-gray-600"
        v-html="quizData?.description"
      />
      <p v-if="!quizStarted">Počet otázek: {{ quizData?.questions.length }}</p>
      <p v-if="!quizStarted">Průměrná úspěšnost: {{ quizData?.accuracy }}%</p>
      <BaseButton
        v-if="!quizStarted"
        size="lg"
        variant="primary"
        class="mt-6"
        @click="quizStarted = true"
        >Zahájit kvíz</BaseButton
      >
    </div>
    <div v-if="quizStarted && !quizFinished" class="mt-6">
      <div class="mt-6" aria-hidden="true">
        <div class="overflow-hidden rounded-full bg-gray-200">
          <div class="h-2 rounded-full bg-primaryDark" :style="`width: ${progress}%`" />
        </div>
      </div>
    </div>
    <div v-if="quizStarted && !quizFinished" class="mt-12">
      <div v-if="quizData?.questions[currentQuestionIndex]?.image" class="mb-4 flex justify-center">
        <BaseImage
          :image="quizData?.questions[currentQuestionIndex]?.image"
          type="quiz"
          size="screen"
          :width="512"
          :height="288"
        />
      </div>
      <p
        v-if="quizData?.questions[currentQuestionIndex]?.name"
        class="mb-4 text-center font-semibold text-primaryDark"
        v-html="quizData?.questions[currentQuestionIndex]?.name"
      />
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:gap-6">
        <div
          v-for="(answer, index) in quizData?.questions[currentQuestionIndex]?.answers"
          :key="index"
          :class="[
            answer.is_selected ? 'bg-primaryDark text-white' : 'bg-white',
            'cursor-pointer text-wrap rounded-lg p-4 text-sm shadow lg:p-6',
          ]"
          @click="
            markSelected(answer);
            answer.is_selected = true;
          "
        >
          <p>{{ answer.name }}</p>
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
    <div v-if="quizFinished && stats" class="mb-6 flex flex-col items-center justify-center">
      <BasePropsHeading type="h2" class="mt-12">Výsledky kvízu</BasePropsHeading>
      <p class="mb-4 text-gray-600">
        Počet správných odpovědí: {{ stats?.correctAnswers }} z {{ quizData?.questions.length }}
      </p>
      <p class="mb-4 text-gray-600">Vaše úspěšnost: {{ stats?.accuracy }}%</p>
      <div
        v-for="(answer, index) in stats.answers"
        :key="index"
        :class="[
          answer.isCorrect
            ? 'hidden bg-green-100 text-green-500 lg:grid'
            : 'bg-red-100 text-red-500',
          'mb-4 grid w-full grid-cols-1 gap-4 text-wrap rounded-lg p-4 text-center text-xs shadow lg:grid-cols-3 lg:text-sm',
        ]"
      >
        <div class="col-span-1 text-wrap">
          <BaseImage
            v-if="answer.image"
            :image="answer.image"
            type="quiz"
            size="screen"
            :width="128"
            :height="128"
          />
          <p v-if="answer.question">{{ answer.question }}</p>
        </div>
        <div class="col-span-1 text-wrap">
          <p class="font-bold">Vaše odpověď:</p>
          <p>{{ answer.userAnswer }}</p>
        </div>
        <div class="col-span-1 text-wrap">
          <p class="font-bold">Správná odpověď:</p>
          <p>{{ answer.correctAnswer }}</p>
        </div>
      </div>
      <NuxtLink v-if="quizFinished" to="/kvizy" class="mt-2">
        <BaseButton size="xxl" variant="primary">Zahrát si další kvízy</BaseButton>
      </NuxtLink>
    </div>
    <BaseButton
      class="relative bottom-2 left-1/2 mt-6 inline-block -translate-x-1/2 transform"
      size="sm"
    >
      <NuxtLink v-if="!quizFinished" to="/kvizy"> Zpět na kvízy </NuxtLink>
    </BaseButton>
  </div>
</template>
