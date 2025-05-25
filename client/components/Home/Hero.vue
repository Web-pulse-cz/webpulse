<script setup lang="ts">
import SmudgeSvg from "~/public/static/img/props/smudge_hero.svg";

const { t } = useI18n();
</script>

<template>
  <div class="flex items-center h-64 py-64 mt-24 relative">
    <div
      class="bg-cyan-300 hero_circle h-[768px] w-[768px] rounded-full absolute -right-[256px] z-0 opacity-75"
    ></div>
    <div class="grid grid-cols-1 md:grid-cols-2 items-center justify-between">
      <div class="col-span-1 space-y-8">
        <h1
          class="text-5xl font-bold sm:text-7xl text-primary text-balance mb-8 leading-tight tracking-normal"
        >
          <span class="text-cyan-500">{{ t("home.hero.titleMark") }}</span>
          <br />
          {{ t("home.hero.title") }}
        </h1>
        <BasePropsHeading type="h4">
          {{ t("home.hero.description") }}
        </BasePropsHeading>
      </div>
      <div class="relative col-span-1 text-center">&nbsp;</div>
    </div>
  </div>
</template>
