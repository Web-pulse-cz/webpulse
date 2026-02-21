<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useApi } from '~/../app/composables/useApi';
import { useAsyncData, useRuntimeConfig, useHead } from '#app';

const { locale, t } = useI18n();
const api = useApi();

// 1. ROZDĚLENÍ VYHLEDÁVACÍCH PROMĚNNÝCH
const searchInput = ref(''); // To, co uživatel právě vidí a píše do políčka
const debouncedSearch = ref(''); // To, co se reálně posílá na backend

const pageMeta = ref({
  title: t('general.metaTitle'),
  description: t('general.metaDescription'),
  meta_title: t('general.metaTitle'),
  meta_description: t('general.metaDescription'),
});

const tableQuery = ref({
  paginate: 3 as number,
  page: 1 as number,
});

// 2. OPRAVA VOLÁNÍ API: Přidán parametr pro vyhledávání
const getPosts = () => {
  return api.blog.posts(
    tableQuery.value.page,
    tableQuery.value.paginate,
    locale.value,
    null,
    debouncedSearch.value, // API nyní skutečně dostane hledaný výraz
  );
};

// Z useAsyncData vytažena funkce 'refresh' pro manuální spuštění
const {
  data: postsData,
  status: postsStatus,
  error: postsError,
  pending: postsPending,
  refresh,
} = useAsyncData('posts', () => getPosts(), {
  // Vyhledávání jsme z hlídání odstranili, budeme ho řídit manuálně
  watch: [locale, () => tableQuery.value.page, () => tableQuery.value.paginate],
});

// 3. DEBOUNCE LOGIKA PRO AUTOMATICKÉ VYHLEDÁVÁNÍ (Zpoždění 300ms)
let timeout: ReturnType<typeof setTimeout>;

watch(searchInput, (newValue) => {
  // Při každém stisku klávesy vyčistíme předchozí odpočet
  clearTimeout(timeout);

  // Nastavíme nový odpočet na 300ms
  timeout = setTimeout(async () => {
    debouncedSearch.value = newValue;
    tableQuery.value.page = 1; // Při novém hledání chceme vždy na 1. stránku

    await refresh(); // Počkáme, až se stáhnou nové články

    // Po úspěšném stažení odskrolujeme k článkům
    // (případně můžeme podmínit if(newValue !== ''), aby se neskrolovalo při promazání pole)
    scrollToArticles();
  }, 300);
});

// 4. MANUÁLNÍ VYHLEDÁVÁNÍ (Např. uživatel zmáčkne Enter)
async function handleManualSearch() {
  clearTimeout(timeout); // Zrušíme automatický odpočet, protože to jdeme udělat hned
  debouncedSearch.value = searchInput.value;
  tableQuery.value.page = 1;

  await refresh();
  scrollToArticles();
}

async function updatePage(paginate: number) {
  tableQuery.value.paginate = paginate;
  // Volání refresh() zde není potřeba, postará se o to 'watch' uvnitř useAsyncData
}

// 5. FUNKCE PRO SCROLL
function scrollToArticles() {
  // Timeout 50ms zajistí, že Vue stihne překreslit DOM s novými daty před samotným scrollem
  setTimeout(() => {
    const articles = document.querySelector('.blog-posts');
    if (articles) {
      articles.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }, 50);
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
</script>

<template>
  <div>
    <HomeHero v-model:search="searchInput" @search="handleManualSearch" />

    <BlogCategoryList />

    <BlogPostList
      v-if="postsData && postsData.data"
      class="blog-posts"
      :posts="postsData.data || []"
      :page="tableQuery.page"
      :per-page="tableQuery.paginate"
      :last-page="postsData.lastPage"
      :total="postsData.total"
      @update-page="updatePage"
    />
  </div>
</template>
