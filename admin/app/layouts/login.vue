<template>
  <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#F8FAFC]">
    <div class="pointer-events-none absolute inset-0 z-0 opacity-40">
      <svg
        class="absolute left-full -translate-x-1/2 -translate-y-1/4 transform"
        width="404"
        height="784"
        fill="none"
        viewBox="0 0 404 784"
      >
        <defs>
          <pattern id="dots" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
            <circle cx="2" cy="2" r="1" fill="#cbd5e1" />
          </pattern>
        </defs>
        <rect width="404" height="784" fill="url(#dots)" />
      </svg>
    </div>

    <main class="relative z-10 w-full">
      <slot />
    </main>
  </div>
</template>

<style scoped>
/* Globální reset pro login layout */
:deep(body) {
  @apply bg-[#F8FAFC];
}
</style>
