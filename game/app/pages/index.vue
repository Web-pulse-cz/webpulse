<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { locale, t } = useI18n();

const pageMeta = ref({
  title: 'Herní centrum',
  description: t('general.metaDescription'),
  meta_title: t('general.metaTitle'),
  meta_description: t('general.metaDescription'),
});

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

const games = ref([
  {
    name: 'Kvízy',
    imageUrl:
      'https://media.canva.com/v2/image-resize/format:JPG/height:896/quality:92/uri:ifs%3A%2F%2FM%2Fb00c85104f0844f28c97ba6f4cc96ba3/watermark:F/width:1600?csig=AAAAAAAAAAAAAAAAAAAAAI5r31kmL9V6auuHo8qD7rCvhgRVmdQcGIyc0szypPt4&exp=1756330706&osig=AAAAAAAAAAAAAAAAAAAAAISx-sr2jdD7R6d8cC-psMyAFkLMYzfGsGgG1irPVdzA&signer=media-rpc&x-canva-quality=screen_2x',
    linkUrl: 'kvizy',
  },
  {
    name: 'Piškvorky',
    imageUrl:
      'https://media.canva.com/v2/image-resize/format:JPG/height:896/quality:92/uri:ifs%3A%2F%2FM%2F1dfbd69077dd420c81b810ea60ed7d85/watermark:F/width:1600?csig=AAAAAAAAAAAAAAAAAAAAAGEMcVYVE5frpwbu-aRNhG2jgHlMYikYjY1hGKMeZace&exp=1756331824&osig=AAAAAAAAAAAAAAAAAAAAAHdtbF_-zfOhUEpcSvJ_55Ip-PGcpNjKqZNvEbjiESLW&signer=media-rpc&x-canva-quality=screen_2x',
    linkUrl: '/games/game2',
  },
  {
    name: 'Slepá mapa',
    imageUrl:
      'https://media.canva.com/v2/image-resize/format:JPG/height:896/quality:92/uri:ifs%3A%2F%2FM%2Fc93bc83a10564636b3abd0a30eab1919/watermark:F/width:1600?csig=AAAAAAAAAAAAAAAAAAAAAP7lDdyXInj1Lxehkdo3tdlADinuTxCxH_O9MjVUf-RK&exp=1756330767&osig=AAAAAAAAAAAAAAAAAAAAAIebKg8vogXQED7WOdMuYYPzbVR-t2TZobQINdYqW95F&signer=media-rpc&x-canva-quality=screen_2x',
    linkUrl: '/slepa-mapa',
  },
]);
</script>

<template>
  <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
    <HomeGameCard
      v-for="game in games"
      :key="game.name"
      class="col-span-1"
      :text="game.name"
      :image-url="game.imageUrl"
      :link-url="game.linkUrl"
    />
  </div>
</template>
