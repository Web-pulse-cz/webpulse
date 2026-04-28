<script setup lang="ts">
const model = defineModel('search', {
  type: String,
  default: '',
});
defineEmits(['search']);
</script>

<template>
  <section
    class="relative overflow-hidden bg-gradient-to-br from-primary/5 via-white to-secondary/5 px-6 py-16 lg:py-24"
  >
    <div class="mx-auto flex max-w-4xl flex-col items-center">
      <h1 class="mb-6 text-center text-4xl font-bold leading-tight text-text-primary md:text-5xl">
        Discover our latest content
      </h1>
      <p class="mb-10 max-w-md text-center text-lg text-text-secondary">
        Discover stories, thinking, and expertise from writers on any topic.
      </p>
      <div class="relative w-full max-w-2xl">
        <div
          class="flex items-center rounded-xl border border-slate-300 bg-white p-2 transition-all focus-within:ring-2 focus-within:ring-primary"
        >
          <span class="material-symbols-outlined ml-3 text-slate-400">search</span>
          <input
            v-model="model"
            class="w-full border-none bg-transparent px-4 text-lg placeholder:text-slate-400 focus:ring-0"
            placeholder="Search for articles..."
            type="text"
          />
          <span
            class="cursor-pointer rounded-lg bg-primary px-6 py-3 text-base font-semibold text-white transition-colors hover:bg-primary-dark"
            @click="$emit('search', model)"
          >
            Search
          </span>
        </div>
      </div>
    </div>
  </section>
</template>
