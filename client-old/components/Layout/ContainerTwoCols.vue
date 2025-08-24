<script setup lang="ts">
const { t, locale } = useI18n();
const localePath = useLocalePath();
const route = useRoute();

interface LayoutProps {
  sidebar?: boolean;
  links?: [];
  path?: string;
}

const props = defineProps<LayoutProps>();

const isActive = (link: any) => {
  return String(route.params.id) === String(link.id);
};
</script>

<template>
  <div class="flex min-h-full flex-col">
    <div class="max-w-xxl mx-auto flex w-full items-stretch gap-x-8 px-8 py-6 lg:px-8 2xl:px-72">
      <aside
        class="sticky top-8 hidden min-h-screen w-64 shrink-0 border-r border-gray-200 xl:block"
      >
        <BasePropsHeading type="h5">{{ t('general.categories') }}</BasePropsHeading>
        <NuxtLink
          v-for="(link, index) in links"
          :key="index"
          :to="localePath({ name: path, params: { id: link.id, slug: link.slug } })"
          class="block py-2 text-lg font-medium text-brand transition-all duration-300 hover:text-brand"
          :class="{
            '-ml-2 rounded-md px-2 text-brand': isActive(link),
            '-ml-2 rounded-md px-2 text-textBlack hover:text-brand': !isActive(link),
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
