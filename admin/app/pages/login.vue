<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';

const form = ref({
  email: '' as string,
  password: '' as string,
});
const { $toast } = useNuxtApp();

const { login } = useSanctumAuth();
function handleSubmit() {
  if (form.value.email && form.value.password) {
    login(form.value)
      .then(() => {
        $toast.show({
          summary: 'Přihlášení',
          detail: 'Byli jste úspěšně přihlášeni.',
          severity: 'success',
          group: 'bc',
        });
      })
      .catch(() => {
        $toast.show({
          summary: 'Chyba',
          detail: 'Nepodařilo se přihlásit. Zkontrolujte prosím zadané údaje.',
          severity: 'error',
          group: 'bc',
        });
      });
  }
}

useHead({
  title: 'Login',
});
definePageMeta({
  layout: 'login',
});
</script>

<template>
  <LayoutContainer class="mt-16 w-full max-w-sm lg:mt-16" :background-show="false">
    <h1 class="text-md mb-4 text-center font-semibold text-inherit lg:text-2xl">Přihlášení</h1>
    <Form @submit="handleSubmit">
      <div class="grid grid-cols-1 gap-y-6">
        <BaseFormInput
          v-model="form.email"
          class="col-span-full"
          type="email"
          name="email"
          placeholder="E-mail"
        />
        <BaseFormInput
          v-model="form.password"
          class="col-span-full"
          type="password"
          name="password"
          placeholder="Heslo"
        />
        <BaseButton class="col-span-full mt-2" type="submit" variant="primary" size="xl">
          Přihlásit se
        </BaseButton>
      </div>
    </Form>
  </LayoutContainer>
</template>
