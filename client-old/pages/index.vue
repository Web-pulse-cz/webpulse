<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useApi } from '@/composables/useApi';
import { useAsyncData } from '#app';

const { locale, t } = useI18n();

const loading = inject('loading', ref(false));
const isOpen = ref(false);

const pageMeta = ref({
  title: t('home.title'),
  description: t('home.meta_description'),
  meta_title: t('home.meta_title'),
  meta_description: t('home.meta_description'),
});

const carouselSlides = [
  {
    image: { src: '/static/img/slide-1.svg', alt: 'Slide 1' },
    content: {
      title: t('carousel.slide1.title'),
      colorTitle: t('carousel.slide1.titleColor'),
      description: t('carousel.slide1.description'),
    },
  },
  {
    image: { src: '/static/img/slide-2.svg', alt: 'Slide 2' },
    content: {
      title: t('carousel.slide2.title'),
      colorTitle: t('carousel.slide2.titleColor'),
      description: t('carousel.slide2.description'),
    },
  },
  {
    image: { src: '/static/img/slide-3.svg', alt: 'Slide 3' },
    content: {
      title: t('carousel.slide3.title'),
      colorTitle: t('carousel.slide3.titleColor'),
      description: t('carousel.slide3.description'),
    },
  },
];

const benefitCards = [
  {
    title: t('benefitCard.pasportization.title'),
    description: t('benefitCard.pasportization.description'),
    image: 'static/img/cards/PasportizaceBudov.svg',
    link: '/sluzby',
  },
  {
    title: t('benefitCard.engineering.title'),
    description: t('benefitCard.engineering.description'),
    image: 'static/img/cards/InzenyrskaCinnost.svg',
    link: '/sluzby',
  },
  {
    title: t('benefitCard.facilityManagement.title'),
    description: t('benefitCard.facilityManagement.description'),
    image: 'static/img/cards/FacilityManagment.svg',
    link: '/sluzby',
  },
];

const { data, status, error, pending } = useAsyncData('logos', () =>
  useApi().logo.logo(locale.value),
);

function canonicalUrl() {
  const appUrl = useRuntimeConfig().public.appUrl;
  return locale.value !== 'cs' ? `${appUrl}/${locale.value}` : appUrl;
}

useHead({
  title: pageMeta.value.title,
  meta: [
    { name: 'description', content: pageMeta.value.meta_description },
    { property: 'og:title', content: pageMeta.value.meta_title },
    { property: 'og:description', content: pageMeta.value.meta_description },
  ],
  link: [
    {
      rel: 'canonical',
      href: useRuntimeConfig().public.appUrl + (locale.value !== 'cs' ? `/${locale.value}` : ''),
    },
  ],
});
const transformedLogos = computed(() => {
  if (!data.value || !Array.isArray(data.value)) return [];

  return data.value.map((item) => ({
    // src: item.logo,
    src: `/static/img/CompanyLogos/${item.name === 'KomplexCR' || item.name === 'Capexus' ? item.name + '.svg' : item.name + '.png'}`,
    alt: item.name,
    link: item.url,
  }));
});
</script>

<template>
  <div>
    <!-- w-full background -->
    <div class="w-full rounded-br-full bg-chppGray py-20">
      <LayoutContainer>
        <HomeCarousel :slides="carouselSlides" height="500px" :interval="3000" />
      </LayoutContainer>
    </div>
    <LayoutContainer class="h-80 py-20">
      <div class="flex h-full w-full items-center">
        <div class="grid h-full w-full grid-cols-2 gap-10">
          <div class="flex h-full flex-col justify-center text-start">
            <BasePropsHeading type="h5" color="brand">{{ t('whyUs.tittle') }}</BasePropsHeading>
            <BasePropsHeading type="h4" color="black">{{ t('whyUs.subtitle') }}</BasePropsHeading>
          </div>
          <div class="flex h-full flex-col items-start justify-center space-y-4">
            <BasePropsParagraph>
              {{ t('whyUs.description') }}
            </BasePropsParagraph>
            <BaseButton variant="primary" size="xxl" @click="navigateTo('/sluzby')">
              {{ t('navbar.services') }}
            </BaseButton>
          </div>
        </div>
      </div>
    </LayoutContainer>
    <LayoutContainer class="h-[480px]">
      <div class="flex w-full items-center justify-between">
        <HomeBenefitCard
          v-for="card in benefitCards"
          :key="card.title"
          :title="card.title"
          :description="card.description"
          :image="card.image"
          :link="card.link"
        />
      </div>
    </LayoutContainer>

    <LayoutContainer class="py-20">
      <!-- <pre>{{ data }}</pre> -->
      <HomeColaborations
        :title="t('colaborations.title')"
        :description="t('colaborations.description')"
        :logos="transformedLogos"
      >
      </HomeColaborations>
    </LayoutContainer>

    <LayoutContainer class="py-40">
      <HomePhotoAndText
        photo-position="left"
        image="/static/img/support.svg"
        :title="t('homePhotoAndText.title')"
        :description="t('homePhotoAndText.description')"
      >
      </HomePhotoAndText>
    </LayoutContainer>

    <BaseModalContactForm :open="isOpen" @close="isOpen = false" />
  </div>
</template>
