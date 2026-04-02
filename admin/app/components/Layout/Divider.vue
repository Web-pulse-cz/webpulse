<template>
  <div class="relative col-span-full my-8 first:mt-4 last:mb-4">
    <div class="absolute inset-0 flex items-center" aria-hidden="true">
      <div class="w-full border-t border-slate-200" />
    </div>

    <div class="relative flex justify-center">
      <span
        class="select-none bg-white px-4 text-[11px] font-bold uppercase tracking-[0.15em] text-slate-400"
      >
        <slot />
      </span>
    </div>
  </div>
</template>
