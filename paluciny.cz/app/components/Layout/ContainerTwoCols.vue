<script setup lang="ts">
const { t } = useI18n();
const localePath = useLocalePath();
const route = useRoute();

interface LayoutProps {
  sidebar?: boolean;
  links?: [];
  path?: string;
}

defineProps<LayoutProps>();

const isActive = (link: { id: number | string }) => {
  return String(route.params.id) === String(link.id);
};
</script>

<template>
  <div class="flex min-h-full flex-col">
    <div
      class="mx-auto flex w-full max-w-[1880px] items-stretch gap-x-8 px-8 py-6 lg:px-8 2xl:px-72"
    >
      <aside class="sticky top-8 hidden w-64 shrink-0 border-r border-cream-dark xl:block">
        <BasePropsHeading type="h5">{{ t('general.categories') }}</BasePropsHeading>
        <NuxtLink
          v-for="(link, index) in links"
          :key="index"
          :to="localePath({ name: path, params: { id: link.id, slug: link.slug } })"
          class="block py-2 text-lg font-medium transition-all duration-300"
          :class="{
            '-ml-2 rounded-md px-2 text-forest': isActive(link),
            '-ml-2 rounded-md px-2 text-earth hover:text-forest': !isActive(link),
          }"
        >
          {{ link.name }}
        </NuxtLink>
      </aside>
      <main class="flex-1">
        <slot />
      </main>
    </div>
  </div>
</template>
