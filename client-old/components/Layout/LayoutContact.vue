<script setup lang="ts">
import { ref } from 'vue';
import Button from '~/components/Base/Button.vue';
import FooterContainer from '~/components/Layout/FooterContainer.vue';

const { t } = useI18n();
const isOpen = ref(false);
</script>

<template>
  <FooterContainer class="bg-chppGray">
    <div class="text-center">
      <BasePropsHeading color="black" type="h1" class="mt-2">
        {{ t('layoutContact.title') }}
      </BasePropsHeading>
      <div class="mt-6">
        <Button variant="primary" size="xxl" @click="isOpen = true">{{
          t('contactForm.button')
        }}</Button>
      </div>
    </div>
  </FooterContainer>
  <BaseModalContactForm :open="isOpen" @close="isOpen = false" />
</template>

<style scoped></style>
