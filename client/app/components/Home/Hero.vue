<script setup lang="ts">
const model = defineModel('search', {
  type: String,
  default: '',
});
const { t, locale } = useI18n();

const emit = defineEmits(['search']);
</script>

<template>
  <section class="relative overflow-hidden bg-sunny/20 px-6 py-16 lg:py-24">
    <div
      class="blob-shape absolute -right-20 -top-20 -z-10 size-80 animate-pulse bg-turquoise/20"
    ></div>
    <div class="blob-shape absolute -bottom-20 -left-20 -z-10 size-96 bg-primary/10"></div>
    <div class="mx-auto flex max-w-6xl flex-col items-center">
      <h1
        class="mb-12 text-center font-display text-6xl font-black leading-[0.9] text-deep-blue md:text-8xl"
      >
        Explore ideas <br />
        <span class="italic text-primary underline decoration-turquoise">that matter.</span>
      </h1>
      <div class="grid w-full grid-cols-1 items-center gap-8 lg:grid-cols-5">
        <div class="hidden flex-col gap-6 lg:flex">
          <a
            class="group relative block aspect-[4/5] -rotate-3 transform overflow-hidden rounded-portal bg-turquoise shadow-xl transition-transform hover:rotate-0"
            href="#"
          >
            <div
              class="absolute inset-0 z-10 bg-deep-blue/20 transition-colors group-hover:bg-transparent"
            ></div>
            <div class="absolute bottom-4 left-4 z-20">
              <span class="rounded-full bg-white px-4 py-1 text-sm font-black text-deep-blue"
                >Lifestyle</span
              >
            </div>
            <span
              class="material-symbols-outlined absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-6xl text-white opacity-50"
              >spa</span
            >
          </a>
        </div>
        <div class="flex flex-col items-center space-y-8 lg:col-span-3">
          <p class="max-w-md text-center text-xl font-medium text-deep-blue">
            Discover stories, thinking, and expertise from writers on any topic. Curated just for
            you.
          </p>
          <div class="group relative w-full max-w-2xl">
            <div
              class="absolute -inset-2 rounded-full bg-gradient-to-r from-primary via-turquoise to-sunny opacity-40 blur transition duration-1000 group-hover:opacity-100"
            ></div>
            <div
              class="relative flex items-center rounded-full border-4 border-deep-blue bg-white p-2"
            >
              <span class="material-symbols-outlined ml-4 text-deep-blue/40">search</span>
              <input
                class="w-full border-none bg-transparent px-4 text-xl font-bold placeholder:text-deep-blue/20 focus:ring-0"
                placeholder="Search for articles..."
                type="text"
                v-model="model"
              />
              <span
                class="rounded-full bg-primary px-8 py-4 text-lg font-black text-white shadow-[4px_4px_0px_0px_rgba(26,83,92,1)] transition-all hover:translate-x-1 hover:translate-y-1 hover:shadow-none"
                @click="$emit('search', model)"
              >
                Search
              </span>
            </div>
          </div>
        </div>
        <div class="hidden flex-col gap-6 lg:flex">
          <a
            class="group relative block aspect-[4/5] rotate-3 transform overflow-hidden rounded-portal bg-primary shadow-xl transition-transform hover:rotate-0"
            href="#"
            style="border-radius: 5rem 2rem 2rem 5rem"
          >
            <div
              class="absolute inset-0 z-10 bg-deep-blue/20 transition-colors group-hover:bg-transparent"
            ></div>
            <div class="absolute bottom-4 right-4 z-20">
              <span class="rounded-full bg-white px-4 py-1 text-sm font-black text-deep-blue"
                >Travel</span
              >
            </div>
            <span
              class="material-symbols-outlined absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-6xl text-white opacity-50"
              >flight_takeoff</span
            >
          </a>
        </div>
      </div>
    </div>
  </section>
</template>
