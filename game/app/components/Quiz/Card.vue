<script setup lang="ts">
const props = defineProps<{
  quiz: {
    id: string;
    name: string;
    slug: string;
    description: string;
    accuracy: number;
    attempts: number;
  };
}>();

const cardClass = computed(() => {
  const baseClass =
    'cursor-pointer rounded-lg p-6 text-sm shadow transition-shadow duration-200 hover:shadow-lg';
  if (props.quiz.attempts <= 3) {
    return `${baseClass} bg-new`;
  } else if (props.quiz.accuracy >= 80) {
    return `${baseClass} bg-success`;
  } else if (props.quiz.accuracy >= 50) {
    return `${baseClass} bg-warning`;
  } else {
    return `${baseClass} bg-danger`;
  }
});

const localePath = useLocalePath();
</script>

<template>
  <NuxtLink :to="localePath(`/kvizy/${props.quiz.id}/${props.quiz.slug}`)" class="block">
    <div :class="cardClass">
      <BasePropsHeading type="h6" class="mb-2 font-semibold">{{
        props.quiz.name
      }}</BasePropsHeading>
      <p class="mb-4 text-xs text-gray-600" v-html="props.quiz.description" />
      <span
        v-for="(tag, index) in quiz.tags_array"
        v-if="quiz.tags_array && quiz.tags_array.length"
        :key="index"
      >
        <span
          class="text-textBlack mb-2 mr-2 inline-block rounded-full bg-backgroundLight px-2.5 py-1 text-xs"
        >
          {{ tag }}
        </span>
      </span>
      <div class="mt-4 flex justify-between">
        <p class="text-xs text-gray-600">{{ quiz.accuracy }}% úspěšnost</p>
        <p class="text-xs text-gray-600">otázek {{ quiz.questions_count }}</p>
      </div>
    </div>
  </NuxtLink>
</template>
