<script setup lang="ts">
import SmudgeSvg from '~/public/static/img/props/smudge_hero.svg';

const { t } = useI18n();
</script>

<template>
  <div class="relative mt-24 flex h-64 items-center py-72">
    <div
      class="hero_circle absolute -right-[256px] z-0 h-[768px] w-[768px] rounded-full bg-cyan-300 opacity-75"
    ></div>
    <div class="grid grid-cols-1 items-center justify-between md:grid-cols-2">
      <div class="col-span-1 space-y-8">
        <h1
          class="mb-8 text-balance text-5xl font-bold leading-tight tracking-normal text-secondary sm:text-7xl"
        >
          <span class="text-light">{{ t('home.hero.titleMark') }}</span>
          <br />
          {{ t('home.hero.title') }}
        </h1>
        <BasePropsHeading type="h4">
          {{ t('home.hero.description') }}
        </BasePropsHeading>
      </div>
      <div class="relative col-span-1 text-center">&nbsp;</div>
    </div>
  </div>
</template>
