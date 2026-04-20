<script setup lang="ts">
import { ref } from 'vue';
import { useApi } from '~/../app/composables/useApi';

const { locale } = useI18n();
const api = useApi();

const form = ref({
  fullname: '',
  email: '',
  phone: '',
  text: '',
});

const sending = ref(false);
const sent = ref(false);
const error = ref(false);

async function handleSubmit() {
  if (sending.value) return;

  sending.value = true;
  error.value = false;

  try {
    await api.global.demand(form.value, locale.value);
    sent.value = true;
    form.value = { fullname: '', email: '', phone: '', text: '' };
  } catch {
    error.value = true;
  } finally {
    sending.value = false;
  }
}
</script>

<template>
  <section class="bg-cream px-6 py-16 lg:px-20 lg:py-24">
    <div class="mx-auto max-w-[1200px] overflow-hidden rounded-3xl bg-forest">
      <div class="grid grid-cols-1 lg:grid-cols-2">
        <!-- Form half -->
        <div class="p-8 md:p-12 lg:p-16">
          <h2 class="mb-2 font-display text-3xl font-bold text-white md:text-4xl">Kontakt</h2>
          <p class="mb-8 text-white/60">Máte dotaz? Napište nám, rádi odpovíme.</p>

          <div v-if="sent" class="py-8 text-center">
            <span class="material-symbols-outlined mb-4 text-5xl text-leaf">check_circle</span>
            <p class="text-lg text-white">Děkujeme za zprávu! Brzy se vám ozveme.</p>
          </div>

          <form v-else class="space-y-5" @submit.prevent="handleSubmit">
            <div>
              <label for="contact-name" class="mb-2 block text-sm font-medium text-white/70">
                Jméno
              </label>
              <input
                id="contact-name"
                v-model="form.fullname"
                type="text"
                required
                placeholder="Vaše jméno"
                class="w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-white placeholder:text-white/50 focus:border-leaf focus:ring-leaf"
              />
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
              <div>
                <label for="contact-email" class="mb-2 block text-sm font-medium text-white/70">
                  E-mail
                </label>
                <input
                  id="contact-email"
                  v-model="form.email"
                  type="email"
                  required
                  placeholder="vas@email.cz"
                  class="w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-white placeholder:text-white/50 focus:border-leaf focus:ring-leaf"
                />
              </div>
              <div>
                <label for="contact-phone" class="mb-2 block text-sm font-medium text-white/70">
                  Telefon
                </label>
                <input
                  id="contact-phone"
                  v-model="form.phone"
                  type="tel"
                  placeholder="+420 ..."
                  class="w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-white placeholder:text-white/50 focus:border-leaf focus:ring-leaf"
                />
              </div>
            </div>

            <div>
              <label for="contact-text" class="mb-2 block text-sm font-medium text-white/70">
                Zpráva
              </label>
              <textarea
                id="contact-text"
                v-model="form.text"
                required
                rows="4"
                placeholder="Vaše zpráva..."
                class="w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-white placeholder:text-white/50 focus:border-leaf focus:ring-leaf"
              ></textarea>
            </div>

            <p v-if="error" class="text-sm text-red-300">
              Nepodařilo se odeslat zprávu. Zkuste to prosím znovu.
            </p>

            <button
              type="submit"
              :disabled="sending"
              class="inline-flex items-center gap-2 rounded-xl bg-bark px-8 py-3 text-base font-semibold text-white transition-colors hover:bg-bark-light disabled:opacity-50"
            >
              <span v-if="sending" class="material-symbols-outlined animate-spin text-xl">
                progress_activity
              </span>
              <span>{{ sending ? 'Odesílám...' : 'Odeslat zprávu' }}</span>
            </button>
          </form>
        </div>

        <!-- Image half -->
        <div class="relative hidden lg:block">
          <img
            src="/static/img/camp.jpg"
            alt="Pohled ze stanu do lesa"
            class="absolute inset-0 h-full w-full object-cover"
          />
          <div class="absolute inset-0 bg-gradient-to-r from-forest via-forest/30 to-transparent" />
        </div>
      </div>
    </div>
  </section>
</template>
