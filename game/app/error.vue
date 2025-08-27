<script setup lang="ts">
import type { NuxtError } from '#app';

const { t } = useI18n();

defineProps({
  error: Object as () => NuxtError,
});
</script>

<template>
  <LayoutContainer>
    <div class="flex w-full flex-col items-center justify-center py-24">
      <NuxtImg src="/static/img/404.jpeg" alt="Error" class="h-72 w-auto" />
      <div class="mt-8 text-center">
        <BasePropsHeading type="h3" class="mt-6">
          {{ t('error.title') }}
        </BasePropsHeading>
        <BaseButton variant="primary" size="xxl" @click="navigateTo('/')">{{
          t('layout.home')
        }}</BaseButton>
      </div>
    </div>
  </LayoutContainer>
</template>
