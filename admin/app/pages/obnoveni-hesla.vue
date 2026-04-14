<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';
import {
  LockClosedIcon,
  CheckCircleIcon,
  ArrowLeftIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

useHead({ title: 'Obnovení hesla' });
definePageMeta({ layout: 'login' });

const { $toast } = useNuxtApp();
const route = useRoute();
const router = useRouter();

const token = ref((route.query.token as string) || '');
const email = ref((route.query.email as string) || '');
const password = ref('');
const passwordConfirmation = ref('');
const isSubmitting = ref(false);
const resetSuccess = ref(false);

const isValidLink = computed(() => token.value && email.value);

async function handleSubmit() {
  if (!password.value || !passwordConfirmation.value) return;

  if (password.value !== passwordConfirmation.value) {
    $toast.show({
      summary: 'Chyba',
      detail: 'Hesla se neshodují.',
      severity: 'error',
    });
    return;
  }

  isSubmitting.value = true;
  try {
    await $fetch('/api/admin/auth/reset-password', {
      method: 'POST',
      body: {
        token: token.value,
        email: email.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
      },
    });
    resetSuccess.value = true;
  } catch (error) {
    $toast.show({
      summary: 'Chyba',
      detail: 'Nepodařilo se obnovit heslo. Odkaz mohl vypršet nebo je neplatný.',
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
        <LockClosedIcon class="size-8 text-white" />
      </div>
      <h1 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
        Obnovení hesla
      </h1>
      <p class="mt-3 text-sm text-slate-500">Zadejte nové heslo pro svůj účet.</p>
    </div>

    <LayoutContainer
      class="rounded-[2rem] border border-slate-200 !bg-white p-8 shadow-2xl shadow-slate-200/40"
      :background-show="false"
    >
      <!-- Invalid link -->
      <div v-if="!isValidLink" class="py-4 text-center">
        <div
          class="mx-auto mb-5 flex size-16 items-center justify-center rounded-full bg-red-50"
        >
          <ExclamationTriangleIcon class="size-8 text-red-600" />
        </div>
        <h2 class="text-lg font-bold text-slate-900">Neplatný odkaz</h2>
        <p class="mt-2 text-sm text-slate-500">
          Tento odkaz pro obnovení hesla je neplatný nebo poškozený. Zkuste požádat o nový.
        </p>
        <NuxtLink
          to="/zapomenute-heslo"
          class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-700"
        >
          Požádat o nový odkaz
        </NuxtLink>
      </div>

      <!-- Success state -->
      <div v-else-if="resetSuccess" class="py-4 text-center">
        <div
          class="mx-auto mb-5 flex size-16 items-center justify-center rounded-full bg-emerald-50"
        >
          <CheckCircleIcon class="size-8 text-emerald-600" />
        </div>
        <h2 class="text-lg font-bold text-slate-900">Heslo obnoveno</h2>
        <p class="mt-2 text-sm text-slate-500">
          Vaše heslo bylo úspěšně změněno. Nyní se můžete přihlásit s novým heslem.
        </p>
        <NuxtLink
          to="/login"
          class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-xl shadow-indigo-100 transition hover:bg-indigo-500"
        >
          <ArrowLeftIcon class="size-4" />
          Přejít na přihlášení
        </NuxtLink>
      </div>

      <!-- Form state -->
      <Form v-else class="space-y-6" @submit="handleSubmit">
        <div class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
          Obnova hesla pro: <strong class="text-slate-900">{{ email }}</strong>
        </div>

        <div class="space-y-5">
          <BaseFormInput
            v-model="password"
            type="password"
            name="password"
            label="Nové heslo"
            placeholder="••••••••"
            rules="required|min:8"
          />

          <BaseFormInput
            v-model="passwordConfirmation"
            type="password"
            name="password_confirmation"
            label="Potvrzení hesla"
            placeholder="••••••••"
            rules="required|min:8"
          />
        </div>

        <div class="pt-2">
          <BaseButton
            class="group w-full justify-center rounded-2xl !py-4 shadow-xl shadow-indigo-100 transition-all active:scale-[0.98]"
            type="submit"
            variant="primary"
            size="xl"
            :loading="isSubmitting"
          >
            <span class="font-bold uppercase tracking-wide">Nastavit nové heslo</span>
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
