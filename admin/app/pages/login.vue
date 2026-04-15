<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';
import { ShieldCheckIcon, ArrowRightIcon, LockClosedIcon } from '@heroicons/vue/24/outline';

// Meta data stránky
useHead({ title: 'Přihlášení do systému | Core Admin' });
definePageMeta({ layout: 'login' });

const form = ref({
  email: '' as string,
  password: '' as string,
  remember: false as boolean,
});

const isSubmitting = ref(false);
const { $toast } = useNuxtApp();
const { login } = useSanctumAuth();

async function handleSubmit() {
  if (!form.value.email || !form.value.password) return;

  isSubmitting.value = true;
  try {
    await login(form.value);
    $toast.show({
      summary: 'Přihlášení',
      detail: 'Vstup do systému byl úspěšný.',
      severity: 'success',
      group: 'bc',
    });
  } catch (error) {
    $toast.show({
      summary: 'Chyba',
      detail: 'Neplatné přihlašovací údaje. Zkuste to prosím znovu.',
      severity: 'error',
      group: 'bc',
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
        <ShieldCheckIcon class="size-8 text-white" />
      </div>
      <h1 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
        Administrace systému
      </h1>
      <p class="mt-3 text-sm font-medium uppercase tracking-[0.15em] text-slate-500">
        Core <span class="text-indigo-600">Framework</span> v4.0
      </p>
    </div>

    <LayoutContainer
      class="rounded-[2rem] border border-slate-200 !bg-white p-8 shadow-2xl shadow-slate-200/40"
      :background-show="false"
    >
      <Form class="space-y-6" @submit="handleSubmit">
        <div class="space-y-5">
          <BaseFormInput
            v-model="form.email"
            type="email"
            name="email"
            label="Uživatelský e-mail"
            placeholder="vložit e-mail"
            class="col-span-full"
          />

          <BaseFormInput
            v-model="form.password"
            type="password"
            name="password"
            label="Heslo"
            placeholder="••••••••"
            class="col-span-full"
          />
        </div>

        <div class="flex items-center justify-between px-1">
          <label class="group flex cursor-pointer items-center">
            <input
              v-model="form.remember"
              type="checkbox"
              class="size-4 rounded border-slate-300 text-indigo-600 transition-all focus:ring-indigo-500"
            />
            <span
              class="ml-2 text-xs font-bold uppercase tracking-tight text-slate-600 transition-colors group-hover:text-slate-900"
            >
              Trvalé přihlášení
            </span>
          </label>
          <NuxtLink
            to="/zapomenute-heslo"
            class="text-xs font-bold text-indigo-600 underline decoration-indigo-100 underline-offset-4 hover:text-indigo-700"
          >
            Zapomenuté heslo?
          </NuxtLink>
        </div>

        <div class="pt-2">
          <BaseButton
            class="group w-full justify-center rounded-2xl !py-4 shadow-xl shadow-indigo-100 transition-all active:scale-[0.98]"
            type="submit"
            variant="primary"
            size="xl"
            :loading="isSubmitting"
          >
            <span class="font-bold uppercase tracking-wide">Vstoupit do systému</span>
            <ArrowRightIcon class="ml-2 size-4 transition-transform group-hover:translate-x-1" />
          </BaseButton>
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
/* Plynulý nástup stránky při načtení */
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
