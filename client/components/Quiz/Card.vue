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
      <p class="text-gray-600 dark:text-gray-400" v-html="props.quiz.description" />
    </div>
  </NuxtLink>
</template>
