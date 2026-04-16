<script setup lang="ts">
import type { NuxtError } from '#app';

const { t } = useI18n();

defineProps({
  error: Object as () => NuxtError,
});
</script>

<template>
  <div>
    <LayoutNavbar />
    <LayoutContainer>
      <div class="flex w-full flex-col items-center justify-center bg-cream py-24">
        <NuxtImg src="/static/img/error404.svg" alt="Error" class="h-72 w-auto" />
        <div class="mt-8 text-center">
          <BasePropsHeading type="h3" class="mt-6 font-display text-earth">
            {{ t('error.title') }}
          </BasePropsHeading>
          <button
            class="mt-4 inline-flex items-center gap-2 rounded-xl bg-forest px-6 py-3 text-base font-semibold text-white transition-colors hover:bg-forest-dark"
            @click="navigateTo('/')"
          >
            {{ t('layout.home') }}
          </button>
        </div>
      </div>
    </LayoutContainer>
    <LayoutFooter />
    <LayoutCookiesBar />
  </div>
</template>
