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
  }
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
    <div class="grid grid-cols-1 gap-y-4">
      <div v-if="allowTranslations" class="col-span-full">
        <BaseFormSelect
          v-model="selectedLocale"
          label="Jazyk"
          name="locale"
          class="w-full"
          :options="languageStore.languageOptions"
        />
        <BaseFormCheckbox
          v-model="translateAutomatically"
          :checked="translateAutomatically"
          label="Automaticky přeložit do ostatních jazyků"
          name="translate_automatically"
          class="mt-2 flex-row-reverse justify-between"
          label-color="grayCustom"
        />
      </div>
      <div v-if="allowState" class="col-span-full">
        <BaseFormSelect
          v-model="state"
          label="Stav"
          name="status"
          class="col-span-full"
          :options="states"
        />
      </div>
      <div v-if="allowPosition" class="col-span-full">
        <BaseFormInput
          v-model="position"
          label="Pořadí ve výpisu"
          name="position"
          class="col-span-full"
          :min="0"
          type="number"
        />
      </div>
      <div v-if="allowCategories" class="col-span-full">
        <BaseFormSelect
          v-model="categoryId"
          label="Kategorie"
          name="event_category_id"
          class="col-span-full"
          :options="categories"
        />
      </div>
      <div v-if="allowIsActive" class="col-span-full">
        <BaseFormCheckbox
          v-model="active"
          name="active"
          label="Aktivní"
          class="col-span-full flex-row-reverse justify-between"
          :checked="active"
          label-color="grayCustom"
          :reverse="true"
        />
      </div>
    </div>
    <div v-if="allowSites" class="grid grid-cols-1 gap-y-0">
      <LayoutDivider v-if="user && user.sites" class="col-span-full"
        >Zařazení do stránek</LayoutDivider
      >
      <BaseFormCheckbox
        v-for="(site, key) in user.sites"
        v-if="sites && user.sites"
        :key="key"
        :label="site.name"
        :name="site.id"
        :checked="sites.includes(site.id)"
        class="col-span-full"
        :reverse="true"
        label-color="grayCustom"
        @change="addRemoveItemSite(site.id)"
      />
    </div>
    <div v-if="allowImage" class="grid grid-cols-1 gap-y-0">
      <LayoutDivider class="col-span-full">Náhledový obrázek</LayoutDivider>
      <BaseFormUploadImage
        v-model="image"
        :multiple="false"
        :type="imageType"
        format="medium"
        label="Náhledový obrázek"
        class="col-span-full"
        @update-files="updateItemImage"
      />
    </div>
  </LayoutContainer>
</template>
