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

const localePath = useLocalePath();
</script>

<template>
  <NuxtLink :to="localePath(`/kvizy/${props.quiz.id}/${props.quiz.slug}`)" class="block">
    <div
      class="cursor-pointer rounded-lg bg-white p-6 text-sm shadow transition-shadow duration-200 hover:shadow-lg"
    >
      <h3 class="mb-2 text-xl font-semibold">{{ props.quiz.name }}</h3>
      <p class="mb-4 text-xs text-gray-600 dark:text-gray-400" v-html="props.quiz.description" />
      <span
        v-for="(tag, index) in quiz.tags_array"
        v-if="quiz.tags_array && quiz.tags_array.length"
        :key="index"
      >
        <span
          class="mb-2 mr-2 inline-block rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-700"
        >
          {{ tag }}
        </span>
      </span>
      <div class="mt-4 flex justify-between">
        <p class="text-xs text-gray-600 dark:text-gray-400">{{ quiz.accuracy }}% úspěšnost</p>
        <p class="text-xs text-gray-600 dark:text-gray-400">otázek {{ quiz.questions_count }}</p>
      </div>
    </div>
  </NuxtLink>
</template>
