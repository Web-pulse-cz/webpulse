<script setup lang="ts">
import { useLanguageStore } from '~~/stores/languageStore';

const languageStore = useLanguageStore();
const user = useSanctumUser();

const selectedLocale = defineModel('selectedLocale', {
  type: String,
  default: 'cs',
});

const translateAutomatically = defineModel('translateAutomatically', {
  type: Boolean,
  default: false,
});

const state = defineModel('state', {
  type: String,
  default: 'draft',
});

const active = defineModel('active', {
  type: Boolean,
  default: true,
});

const position = defineModel('position', {
  type: Number,
  default: 0,
});

const categoryId = defineModel('categoryId', {
  type: Number,
  default: 0,
});

const sites = defineModel('sites', {
  type: Array,
  default: () => [],
});

const image = defineModel('image', {
  type: String,
  default: '',
});

defineProps({
  allowTranslations: {
    type: Boolean,
    default: true,
    required: false,
  },
  allowIsActive: {
    type: Boolean,
    default: false,
    required: false,
  },
  allowState: {
    type: Boolean,
    default: false,
    required: false,
  },
  allowPosition: {
    type: Boolean,
    default: false,
    required: false,
  },
  allowCategories: {
    type: Boolean,
    default: false,
    required: false,
  },
  allowSites: {
    type: Boolean,
    default: true,
    required: false,
  },
  allowImage: {
    type: Boolean,
    default: true,
    required: false,
  },
  states: {
    type: Array,
    default: () => [],
    required: false,
  },
  categories: {
    type: Array,
    default: () => [],
    required: false,
  },
  imageType: {
    type: String,
    default: 'event',
    required: false,
  },
});

function updateItemImage(files) {
  image.value = files[0];
}

function addRemoveItemSite(siteId) {
  if (sites.value.includes(siteId)) {
    sites.value = sites.value.filter((site) => site !== siteId);
    return;
  } else {
    sites.value.push(siteId);
  }
}
</script>

<template>
  <LayoutContainer class="w-full">
    <div class="grid grid-cols-1 gap-y-6">
      <div v-if="allowTranslations" class="space-y-3">
        <BaseFormSelect
          v-model="selectedLocale"
          label="Jazyk"
          name="locale"
          class="w-full"
          :options="languageStore.languageOptions"
        />
        <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-inset ring-slate-200">
          <BaseFormCheckbox
            v-model="translateAutomatically"
            :checked="translateAutomatically"
            label="Automatický překlad"
            name="translate_automatically"
            class="flex-row-reverse justify-between"
            label-color="slate-600"
          />
        </div>
      </div>

      <div v-if="allowState">
        <BaseFormSelect v-model="state" label="Stav" name="status" :options="states" />
      </div>

      <div v-if="allowPosition">
        <BaseFormInput
          v-model="position"
          label="Pořadí ve výpisu"
          name="position"
          :min="0"
          type="number"
        />
      </div>

      <div v-if="allowCategories">
        <BaseFormSelect
          v-model="categoryId"
          label="Kategorie"
          name="event_category_id"
          :options="categories"
        />
      </div>

      <div v-if="allowIsActive" class="pt-2">
        <div
          class="flex items-center justify-between rounded-xl border border-slate-200 p-4 transition-colors hover:bg-slate-50"
        >
          <BaseFormCheckbox
            v-model="active"
            name="active"
            label="Aktivní"
            class="w-full flex-row-reverse justify-between"
            :checked="active"
            label-color="slate-700 font-bold"
            :reverse="true"
          />
        </div>
      </div>

      <div
        v-if="allowSites && user?.sites"
        :class="[
          allowSites &&
          !allowIsActive &&
          !allowState &&
          !allowCategories &&
          !allowPosition &&
          !allowTranslations
            ? 'pt-0'
            : 'pt-4',
          'space-y-4',
        ]"
      >
        <LayoutDivider class="text-xs font-bold uppercase tracking-widest text-slate-400">
          Zařazení do stránek
        </LayoutDivider>

        <div class="grid grid-cols-1 gap-y-1">
          <div
            v-for="(site, key) in user.sites"
            :key="key"
            class="group flex items-center rounded-lg px-2 py-1 transition-colors hover:bg-slate-50"
          >
            <BaseFormCheckbox
              v-if="sites"
              :label="site.name"
              :name="site.id"
              :checked="sites.includes(site.id)"
              class="w-full flex-row-reverse justify-between"
              :reverse="true"
              label-color="slate-600 group-hover:text-slate-900"
              @change="addRemoveItemSite(site.id)"
            />
          </div>
        </div>
      </div>

      <div v-if="allowImage" class="space-y-4 pt-4">
        <LayoutDivider class="text-xs font-bold uppercase tracking-widest text-slate-400">
          Náhledový obrázek
        </LayoutDivider>
        <div class="overflow-hidden rounded-2xl bg-slate-50 pt-0">
          <BaseFormUploadImage
            v-model="image"
            :multiple="false"
            :type="imageType"
            format="medium"
            label=""
            class="w-full"
            @update-files="updateItemImage"
          />
        </div>
      </div>
    </div>
  </LayoutContainer>
</template>
