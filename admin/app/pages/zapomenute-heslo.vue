<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';
import { EnvelopeIcon, ArrowLeftIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

useHead({ title: 'Zapomenuté heslo' });
definePageMeta({ layout: 'login' });

const { $toast } = useNuxtApp();

const email = ref('');
const isSubmitting = ref(false);
const emailSent = ref(false);

async function handleSubmit() {
  if (!email.value) return;

  isSubmitting.value = true;
  try {
    await $fetch('/api/admin/auth/forgot-password', {
      method: 'POST',
      body: { email: email.value },
    });
    emailSent.value = true;
  } catch (error) {
    $toast.show({
      summary: 'Chyba',
      detail: 'Nepodařilo se odeslat odkaz pro obnovení hesla. Zkontrolujte zadaný e-mail.',
      severity: 'error',
    });
  } finally {
    isSubmitting.value = false;
  }
}
</script>

<template>
  <div class="mx-auto w-full max-w-[440px] px-4">
    <div class="mb-10 text-center">
      <div
        class="mb-6 inline-flex size-14 items-center justify-center rounded-2xl bg-indigo-600 shadow-xl shadow-indigo-100"
      >
        <EnvelopeIcon class="size-8 text-white" />
      </div>
      <h1 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
        Zapomenuté heslo
      </h1>
      <p class="mt-3 text-sm text-slate-500">
        Zadejte svůj e-mail a my vám pošleme odkaz pro obnovení hesla.
      </p>
    </div>

    <LayoutContainer
      class="rounded-[2rem] border border-slate-200 !bg-white p-8 shadow-2xl shadow-slate-200/40"
      :background-show="false"
    >
      <!-- Success state -->
      <div v-if="emailSent" class="py-4 text-center">
        <div
          class="mx-auto mb-5 flex size-16 items-center justify-center rounded-full bg-emerald-50"
        >
          <CheckCircleIcon class="size-8 text-emerald-600" />
        </div>
        <h2 class="text-lg font-bold text-slate-900">E-mail odeslán</h2>
        <p class="mt-2 text-sm text-slate-500">
          Na adresu <strong class="text-slate-700">{{ email }}</strong> jsme odeslali odkaz pro
          obnovení hesla. Zkontrolujte svou e-mailovou schránku.
        </p>
        <p class="mt-4 text-xs text-slate-400">
          Odkaz je platný 60 minut. Pokud e-mail nevidíte, zkontrolujte složku spam.
        </p>
        <NuxtLink
          to="/login"
          class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-700"
        >
          <ArrowLeftIcon class="size-4" />
          Zpět na přihlášení
        </NuxtLink>
      </div>

      <!-- Form state -->
      <Form v-else class="space-y-6" @submit="handleSubmit">
        <BaseFormInput
          v-model="email"
          type="email"
          name="email"
          label="Váš e-mail"
          placeholder="zadejte svůj e-mail"
          rules="required|email"
        />

        <div class="pt-2">
          <BaseButton
            class="group w-full justify-center rounded-2xl !py-4 shadow-xl shadow-indigo-100 transition-all active:scale-[0.98]"
            type="submit"
            variant="primary"
            size="xl"
            :loading="isSubmitting"
          >
            <span class="font-bold uppercase tracking-wide">Odeslat odkaz</span>
          </BaseButton>
        </div>

        <div class="text-center">
          <NuxtLink
            to="/login"
            class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 transition hover:text-slate-700"
          >
            <ArrowLeftIcon class="size-3" />
            Zpět na přihlášení
          </NuxtLink>
        </div>
      </Form>
    </LayoutContainer>

    <div class="mt-12 flex flex-col items-center gap-4 opacity-40">
      <div
        class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400"
      >
        <span class="size-1.5 rounded-full bg-emerald-500" />
        Zabezpečené rozhraní
      </div>
      <p class="text-center text-[10px] font-medium uppercase tracking-widest text-slate-400">
        &copy; 2026 Všechna práva vyhrazena.
      </p>
    </div>
  </div>
</template>

<style scoped>
.w-full {
  animation: slideUp 0.7s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
