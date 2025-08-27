<script setup lang="ts">
import { ref } from 'vue';

const { t } = useI18n();
const isOpen = ref(false);
</script>

<template>
  <div class="bg-chppGray">
    <LayoutFooterContainer>
      <div class="text-center">
        <BasePropsHeading color="black" type="h1" class="mt-2">
          {{ t('layout.contact.title') }}
        </BasePropsHeading>
        <div class="mt-6">
          <BaseButton variant="primary" size="xxl" @click="isOpen = true">{{
            t('contactForm.button')
          }}</BaseButton>
        </div>
      </div>
    </LayoutFooterContainer>
  </div>
  <BaseModalContactForm :open="isOpen" @close="isOpen = false" />
</template>

<style scoped></style>
