<script setup lang="ts"></script>

<template>
  <section class="relative overflow-hidden bg-cream">
    <!-- Full-width photo area -->
    <div class="relative min-h-[50vh] md:min-h-[65vh] lg:min-h-[75vh]">
      <!-- Background image -->
      <img
        src="/static/img/hero-nature.jpg"
        alt="Les s prosvítajícím sluncem"
        class="absolute inset-0 h-full w-full object-cover"
      />

      <!-- Gradient overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-earth/40 via-transparent to-transparent" />

      <!-- Text overlay in max-width container -->
      <div
        class="relative mx-auto flex min-h-[50vh] max-w-[1200px] items-end px-6 pb-12 md:min-h-[65vh] md:pb-16 lg:min-h-[75vh] lg:px-20 lg:pb-20"
      >
        <div class="rounded-2xl bg-cream/90 p-8 backdrop-blur-sm md:max-w-lg md:p-10">
          <h1
            class="mb-3 font-display text-3xl font-bold leading-tight text-earth md:text-4xl lg:text-5xl"
          >
            S námi zažiješ dobrodružství.
          </h1>
          <p class="mb-6 text-base text-earth-light md:text-lg">
            Objevuj, hraj si a poznávej přírodu s Palučinami.
          </p>
          <NuxtLink
            to="#akce"
            class="inline-flex items-center gap-2 rounded-xl bg-forest px-6 py-3 text-base font-semibold text-white transition-colors hover:bg-forest-dark"
          >
            <span class="material-symbols-outlined text-xl">calendar_month</span>
            Kalendář akcí
          </NuxtLink>
        </div>
      </div>
    </div>
  </section>
</template>
