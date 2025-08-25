<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import dayjs from 'dayjs';
import { useApi } from '~/../app/composables/useApi';
import { useAsyncData } from '#app';

const localePath = useLocalePath();

const { t, locale } = useI18n();
const route = useRoute();
const api = useApi();
const pageMeta = ref({
  title: t('blog.title'),
  description: t('blog.meta_description'),
  meta_title: t('blog.meta_title'),
  meta_description: t('blog.meta_description'),
  id: route.params.id,
  slug: route.params.slug,
});

const {
  data: postData,
  status: postStatus,
  error: postError,
  pending: postPending,
} = useAsyncData('basePosts', () =>
  api.blog
    .postDetail(route.params.id, locale.value)
    .then()
    .catch(() => {
      throw createError({
        statusCode: 404,
        statusMessage: 'Page Not Found',
      });
    }),
);

function canonicalUrl() {
  const appUrl = useRuntimeConfig().public.appUrl;
  let string = locale.value !== 'cs' ? `${appUrl}/${locale.value}` : appUrl;
  string += `/blog/${t('canonical.post')}`;
  string += postData && postData.id ? `/${postData.id}` : `/${route.params.id}`;
  string += postData && postData.slug ? `/${postData.slug}` : `/${route.params.slug}`;

  return string;
}
useHead({
  title: pageMeta.value.title,
  meta: [
    { name: 'description', content: pageMeta.value.description },
    { property: 'og:title', content: pageMeta.value.meta_title },
    { property: 'og:description', content: pageMeta.value.meta_description },
  ],
  link: [
    {
      rel: 'canonical',
      href:
        useRuntimeConfig().public.appUrl +
        (locale.value !== 'cs' ? `/${locale.value}` : '') +
        `/${t('canonical.blog')}/${t('canonical.post')}/${pageMeta.value.id}/${pageMeta.value.slug}`,
    },
  ],
});
</script>

<template>
  <div v-if="!postPending && !postError && postData">
    <div v-if="!postPending && !postError && postData">
      <div class="bg-white px-28 py-24">
        <div class="flex flex-col items-center justify-start gap-16">
          <div class="flex w-[800px] flex-col items-center justify-start gap-6">
            <div class="text-base leading-snug text-textDescription">
              {{ dayjs(postData.created_at).format('DD. MM. YYYY') }}
            </div>

            <BasePropsHeading v-if="!postPending && !postError && postData">
              {{ postData.name }}
            </BasePropsHeading>

            <div
              v-if="postData.categories?.length"
              class="inline-flex items-start justify-center gap-2"
            >
              <div
                v-for="category in postData.categories"
                :key="category.id"
                class="rounded-full bg-indigo-50 px-3 py-1.5"
              >
                <div class="text-center text-sm leading-tight text-brand">
                  {{ category.name }}
                </div>
              </div>
            </div>
          </div>

          <img
            v-if="postData.image"
            class="relative h-[512px] w-[1216px] rounded-[32px] object-cover"
            :src="postData.image"
            :alt="postData.name"
          />
        </div>

        <div class="mt-16 flex flex-col items-start justify-start gap-16 px-52">
          <div class="flex w-[800px] flex-col items-start justify-start gap-6">
            <div
              class="self-stretch text-lg leading-7 text-textDescription"
              v-html="postData.text"
            ></div>
          </div>

          <div
            class="inline-flex items-center justify-between self-stretch border-t border-slate-200 pt-8"
          >
            <div class="flex items-center justify-start gap-4">
              <div class="text-base leading-relaxed text-textDescription">
                {{ dayjs(postData.created_at).format('DD. MM. YYYY') }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <LayoutContainer v-if="!postPending && !postError && postData" class="space-y-12 py-24 pb-24">
      <BasePropsHeading type="h1" class="my-y-24">
        {{ postData.name }}
      </BasePropsHeading>
      <div class="text-lg" v-html="postData.text"></div>
      <pre>{{ postData }}</pre>
    </LayoutContainer>
  </div>
</template>
