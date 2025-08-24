<script setup>
import Button from '~/components/Base/Button.vue';

defineProps(['error']);
const { t } = useI18n();
</script>

<template>
  <LayoutContainer>
    <div class="flex h-96 w-full flex-col items-center justify-center">
      <img src="/static/img/error404.svg" alt="Error" class="h-64 w-auto" />
      <div class="mt-6">
        <Button variant="primary" size="xxl" @click="navigateTo('/')">{{
          t('navbar.home')
        }}</Button>
      </div>
    </div>
  </LayoutContainer>
</template>

<style scoped></style>
