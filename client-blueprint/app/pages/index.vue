<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { onMounted, onUnmounted } from 'vue';
import { useApi } from '~/../app/composables/useApi';
import { useAsyncData } from '#app';

const { locale, t } = useI18n();

const isOpen = ref(false);

const pageMeta = ref({
  title: t('general.metaTitle'),
  description: t('general.metaDescription'),
  meta_title: t('general.metaTitle'),
  meta_description: t('general.metaDescription'),
});

const carouselSlides = Array.from({ length: 3 }, (_, i) => ({
  title: t(`carousel.data.${i}.title`),
  titleColor: t(`carousel.data.${i}.titleColor`),
  description: t(`carousel.data.${i}.description`),
  img: t(`carousel.data.${i}.img`),
}));

const benefitCards = [
  // TODO: nahradíme za reálná data
  {
    title: 'Pasportizace budov',
    description:
      'Pasportizace budov je naším hlavním směrem, která je klíčová pro efektivní správu budov.',
    image: 'static/img/cards/PasportizaceBudov.svg',
    link: '/sluzby',
  },
  {
    title: 'Inženýrská činnost',
    description:
      'Při koordinaci inženýrských služeb využíváme spolupráci se špičkami ve stavebnictví.',
    image: 'static/img/cards/InzenyrskaCinnost.svg',
    link: '/sluzby',
  },
  {
    title: 'Facility management',
    description:
      'Kooperujeme s experty ve facility managmentu pro ideální spojení pasportizace a správy.',
    image: 'static/img/cards/FacilityManagment.svg',
    link: '/sluzby',
  },
];

const { data, status, error, pending } = useAsyncData('logos', () =>
  useApi().logo.logo(locale.value),
);

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
    src: `/static/img/CompanyLogos/${item.name === 'Komplex CR' || item.name === 'Capexus' ? item.name + '.svg' : item.name + '.png'}`,
    alt: item.name,
    link: item.url,
  }));
});
const cardContainer = ref<HTMLElement | null>(null);
const isVisible = ref(false);
let lastScrollY = window.scrollY;
let isScrolling = false;

onMounted(() => {
  const observer = new IntersectionObserver(
    ([entry]) => {
      isVisible.value = entry.isIntersecting;
    },
    { threshold: 0.7 },
  );
  if (cardContainer.value) observer.observe(cardContainer.value);

  onUnmounted(() => {
    if (cardContainer.value) observer.unobserve(cardContainer.value);
  });
});

function onScroll() {
  if (!cardContainer.value || !isVisible.value || isScrolling) return;
  const currentScrollY = window.scrollY;
  const direction = currentScrollY > lastScrollY ? 1 : -1;
  cardContainer.value.scrollBy({
    left: direction * 0.1,
    behavior: 'smooth',
  });
  isScrolling = true;
  lastScrollY = currentScrollY;
  setTimeout(() => {
    isScrolling = false;
    // Timeout between scrolls
  }, 500);
}

const localePath = useLocalePath();
console.log(localePath({ name: 'review-index' }));

onMounted(() => {
  window.addEventListener('scroll', onScroll);
});
onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll);
});
</script>

<template>
  <div>
    <!-- w-full background -->
    <div class="w-full rounded-br-[160px] bg-chppGray md:rounded-br-[300px]">
      <LayoutContainer class="mb-20">
        <HomeCarousel :slides="carouselSlides" height="600px" :interval="3000" />
      </LayoutContainer>
    </div>
    <LayoutContainer class="h-80 py-20">
      <div class="flex h-full w-full items-center">
        <div class="grid h-full w-full grid-cols-2 gap-10">
          <div class="flex h-full flex-col text-start">
            <BasePropsHeading type="h5" color="brand" margin-bottom="mb-2">{{
              t('whyUs.title')
            }}</BasePropsHeading>
            <BasePropsHeading type="h4" color="black">{{ t('whyUs.subtitle') }}</BasePropsHeading>
          </div>
          <div class="flex h-full flex-col items-start space-y-4">
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
      <div
        ref="cardContainer"
        class="flex w-full snap-x snap-mandatory gap-4 overflow-x-auto scroll-smooth pb-3 lg:justify-between lg:overflow-x-hidden"
        style="scrollbar-width: none"
      >
        <HomeBenefitCard
          v-for="card in benefitCards"
          :key="card.title"
          :title="card.title"
          :description="card.description"
          :image="card.image"
          :link="card.link"
          class="min-w-[80%] snap-center md:min-w-0"
        />
      </div>
    </LayoutContainer>

    <LayoutContainer class="py-20">
      <!-- <pre>{{ data }}</pre> -->
      <HomeColaborations
        :title="t('home.collaborations.title')"
        :description="t('home.collaborations.description')"
        :logos="transformedLogos"
      >
      </HomeColaborations>
    </LayoutContainer>

    <LayoutContainer class="py-40">
      <HomePhotoAndText
        photo-position="left"
        image="/static/img/support.svg"
        :title="t('home.photoAndText.title')"
        :description="t('home.photoAndText.description')"
      >
      </HomePhotoAndText>
    </LayoutContainer>

    <BaseModalContactForm :open="isOpen" @close="isOpen = false" />
  </div>
</template>
