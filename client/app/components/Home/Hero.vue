<script setup lang="ts">
const { t, locale } = useI18n();
</script>

<template>
  <section class="relative bg-sunny/20 py-16 lg:py-24 px-6 overflow-hidden">
    <div class="absolute -top-20 -right-20 size-80 bg-turquoise/20 blob-shape -z-10 animate-pulse"></div>
    <div class="absolute -bottom-20 -left-20 size-96 bg-primary/10 blob-shape -z-10"></div>
    <div class="max-w-6xl mx-auto flex flex-col items-center">
      <h1 class="text-6xl md:text-8xl font-black text-center mb-12 font-display leading-[0.9] text-deep-blue">
        Explore ideas <br/>
        <span class="text-primary italic underline decoration-turquoise">that matter.</span>
      </h1>
      <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 items-center w-full">
        <div class="hidden lg:flex flex-col gap-6">
          <a class="group relative block aspect-[4/5] bg-turquoise rounded-portal overflow-hidden shadow-xl transform -rotate-3 hover:rotate-0 transition-transform"
             href="#">
            <div class="absolute inset-0 bg-deep-blue/20 group-hover:bg-transparent transition-colors z-10"></div>
            <div class="absolute bottom-4 left-4 z-20">
              <span class="bg-white text-deep-blue font-black px-4 py-1 text-sm rounded-full">Lifestyle</span>
            </div>
            <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white material-symbols-outlined text-6xl opacity-50">spa</span>
          </a>
        </div>
        <div class="lg:col-span-3 flex flex-col items-center space-y-8">
          <p class="text-xl text-deep-blue font-medium text-center max-w-md">
            Discover stories, thinking, and expertise from writers on any topic. Curated just for you.
          </p>
          <div class="relative w-full max-w-2xl group">
            <div class="absolute -inset-2 bg-gradient-to-r from-primary via-turquoise to-sunny rounded-full blur opacity-40 group-hover:opacity-100 transition duration-1000"></div>
            <div class="relative flex items-center bg-white border-4 border-deep-blue rounded-full p-2">
              <span class="material-symbols-outlined ml-4 text-deep-blue/40">search</span>
              <input class="w-full bg-transparent border-none focus:ring-0 text-xl font-bold placeholder:text-deep-blue/20 px-4"
                     placeholder="Search for articles..." type="text"/>
              <button class="bg-primary text-white font-black rounded-full px-8 py-4 text-lg shadow-[4px_4px_0px_0px_rgba(26,83,92,1)] hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                Search
              </button>
            </div>
          </div>
        </div>
        <div class="hidden lg:flex flex-col gap-6">
          <a class="group relative block aspect-[4/5] bg-primary rounded-portal overflow-hidden shadow-xl transform rotate-3 hover:rotate-0 transition-transform"
             href="#" style="border-radius: 5rem 2rem 2rem 5rem;">
            <div class="absolute inset-0 bg-deep-blue/20 group-hover:bg-transparent transition-colors z-10"></div>
            <div class="absolute bottom-4 right-4 z-20">
              <span class="bg-white text-deep-blue font-black px-4 py-1 text-sm rounded-full">Travel</span>
            </div>
            <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white material-symbols-outlined text-6xl opacity-50">flight_takeoff</span>
          </a>
        </div>
      </div>
    </div>
  </section>
</template>