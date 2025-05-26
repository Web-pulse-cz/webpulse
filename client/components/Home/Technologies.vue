<script setup lang="ts">
const { t } = useI18n();
</script>

<template>
  <div class="text-center">
    <BasePropsHeading type="h2" class="mb-6 text-center">
      {{ t('home.technologiesTitle') }}
    </BasePropsHeading>
    <div class="flex flex-wrap justify-center gap-x-6">
      <NuxtImg
        src="/static/img/icon/technologies/laravel.svg"
        alt="Laravel"
        class="h-auto w-[96px]"
      />
      <NuxtImg
        src="/static/img/icon/technologies/docker.svg"
        alt="Docker"
        class="h-auto w-[96px]"
      />
      <NuxtImg
        src="/static/img/icon/technologies/aws.svg"
        alt="Amazon web services"
        class="h-auto w-[96px]"
      />
      <NuxtImg
        src="/static/img/icon/technologies/nodejs.svg"
        alt="Node.js"
        class="h-auto w-[96px]"
      />
      <NuxtImg src="/static/img/icon/technologies/npm.svg" alt="Npm" class="h-auto w-[96px]" />
      <NuxtImg src="/static/img/icon/technologies/php.svg" alt="PHP" class="h-auto w-[96px]" />
      <NuxtImg src="/static/img/icon/technologies/nuxt.svg" alt="Nuxt.js" class="h-auto w-[96px]" />
      <NuxtImg
        src="/static/img/icon/technologies/js.svg"
        alt="JavaScript"
        class="h-auto w-[96px]"
      />
      <NuxtImg
        src="/static/img/icon/technologies/github.svg"
        alt="Github"
        class="h-auto w-[96px]"
      />
      <NuxtImg src="/static/img/icon/technologies/figma.svg" alt="Figma" class="h-auto w-[96px]" />
      <NuxtImg src="/static/img/icon/technologies/mysql.svg" alt="Mysql" class="h-auto w-[96px]" />
    </div>
  </div>
</template>
