<script setup lang="ts"></script>

<template>
  <section class="relative overflow-hidden bg-cream-dark/50 px-6 py-16 lg:py-24">
    <!-- Decorative leaf left -->
    <svg
      class="absolute -left-6 top-1/4 h-48 w-48 rotate-[-30deg] text-leaf/10"
      viewBox="0 0 100 100"
      fill="currentColor"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path d="M50 5C50 5 20 25 15 55C10 85 40 95 50 95C60 95 90 85 85 55C80 25 50 5 50 5Z" />
      <path d="M50 20V90" stroke="white" stroke-width="1.5" fill="none" opacity="0.3" />
      <path d="M50 40L35 55" stroke="white" stroke-width="1" fill="none" opacity="0.2" />
      <path d="M50 60L65 50" stroke="white" stroke-width="1" fill="none" opacity="0.2" />
    </svg>

    <!-- Decorative leaf right -->
    <svg
      class="absolute -right-6 bottom-1/4 h-44 w-44 rotate-[150deg] text-moss/10"
      viewBox="0 0 100 100"
      fill="currentColor"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path d="M50 5C50 5 20 25 15 55C10 85 40 95 50 95C60 95 90 85 85 55C80 25 50 5 50 5Z" />
      <path d="M50 20V90" stroke="white" stroke-width="1.5" fill="none" opacity="0.3" />
    </svg>

    <!-- Small fox illustration -->
    <svg
      class="absolute bottom-8 right-12 hidden h-20 w-20 text-bark-light/20 lg:block"
      viewBox="0 0 100 100"
      fill="currentColor"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path d="M25 30L40 10L50 35L60 10L75 30V55C75 70 65 80 50 80C35 80 25 70 25 55V30Z" />
      <circle cx="40" cy="45" r="3" fill="white" opacity="0.5" />
      <circle cx="60" cy="45" r="3" fill="white" opacity="0.5" />
      <path d="M45 58Q50 63 55 58" stroke="white" stroke-width="1.5" fill="none" opacity="0.4" />
    </svg>

    <div class="relative mx-auto max-w-3xl text-center">
      <h2 class="mb-8 font-display text-3xl font-bold italic text-forest md:text-4xl">O nás</h2>

      <p class="mb-6 text-base leading-relaxed text-earth-light md:text-lg">
        Palučiny jsou místem, kde se děti učí poznávat přírodu, rozvíjí svou kreativitu a prožívají
        nezapomenutelná dobrodružství. Naše oddílové schůzky, víkendové výpravy a letní tábory jsou
        plné her, poznávání a kamarádství v přírodě.
      </p>

      <p class="text-base leading-relaxed text-earth-light md:text-lg">
        Věříme, že čas strávený venku, daleko od obrazovek, pomáhá dětem růst. Učíme je orientaci v
        lese, stavění přístřešků, rozpoznávání rostlin a zvířat, a hlavně je vedeme k lásce k
        přírodě a ohleduplnosti k okolnímu světu.
      </p>
    </div>
  </section>
</template>
