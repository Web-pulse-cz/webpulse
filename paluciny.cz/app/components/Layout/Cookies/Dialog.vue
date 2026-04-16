<script setup>
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';

const { t } = useI18n();

const open = defineModel('open', {
  type: Boolean,
  default: false,
});
const cookies = defineModel('cookies', {
  type: Object,
  default: () => ({}),
});
const emit = defineEmits(['acceptSelected', 'acceptAll']);
</script>

<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-[70]" @close="open = false">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-earth/50 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel
              class="relative w-full max-w-2xl transform overflow-hidden rounded-2xl bg-cream p-6 text-left shadow-2xl transition-all sm:p-10"
            >
              <div class="mb-8">
                <span
                  class="mb-4 inline-block rounded-full bg-forest/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-forest"
                >
                  {{ t('cookies.settings') }}
                </span>
                <h3 class="text-2xl font-semibold leading-tight text-earth sm:text-3xl">
                  {{ t('cookies.title') }}
                </h3>
              </div>

              <div class="mb-8 space-y-3">
                <div
                  class="flex items-start gap-4 rounded-xl border border-cream-dark bg-white p-4 sm:p-5"
                >
                  <div class="mt-1">
                    <input
                      type="checkbox"
                      checked
                      disabled
                      class="size-5 rounded border-cream-dark text-forest focus:ring-forest"
                    />
                  </div>
                  <div>
                    <h4 class="mb-1 text-base font-semibold text-earth">
                      {{ t('cookies.technicalCookiesTitle') }}
                    </h4>
                    <p class="text-sm text-earth-light">
                      {{ t('cookies.technicalCookiesDescription') }}
                    </p>
                  </div>
                </div>

                <div
                  class="flex items-start gap-4 rounded-xl border border-cream-dark bg-white p-4 transition-colors hover:bg-cream-dark/30 sm:p-5"
                >
                  <div class="mt-1">
                    <input
                      v-model="cookies.analytics"
                      type="checkbox"
                      class="size-5 rounded border-cream-dark bg-white text-forest focus:ring-forest"
                    />
                  </div>
                  <div>
                    <h4 class="mb-1 text-base font-semibold text-earth">
                      {{ t('cookies.analyticsCookiesTitle') }}
                    </h4>
                    <p class="text-sm text-earth-light">
                      {{ t('cookies.analyticsCookiesDescription') }}
                    </p>
                  </div>
                </div>

                <div
                  class="flex items-start gap-4 rounded-xl border border-cream-dark bg-white p-4 transition-colors hover:bg-cream-dark/30 sm:p-5"
                >
                  <div class="mt-1">
                    <input
                      v-model="cookies.marketing"
                      type="checkbox"
                      class="size-5 rounded border-cream-dark bg-white text-forest focus:ring-forest"
                    />
                  </div>
                  <div>
                    <h4 class="mb-1 text-base font-semibold text-earth">
                      {{ t('cookies.marketingCookiesTitle') }}
                    </h4>
                    <p class="text-sm text-earth-light">
                      {{ t('cookies.marketingCookiesDescription') }}
                    </p>
                  </div>
                </div>

                <div
                  class="flex items-start gap-4 rounded-xl border border-cream-dark bg-white p-4 transition-colors hover:bg-cream-dark/30 sm:p-5"
                >
                  <div class="mt-1">
                    <input
                      v-model="cookies.advertisement"
                      type="checkbox"
                      class="size-5 rounded border-cream-dark bg-white text-forest focus:ring-forest"
                    />
                  </div>
                  <div>
                    <h4 class="mb-1 text-base font-semibold text-earth">
                      {{ t('cookies.advertisementCookiesTitle') }}
                    </h4>
                    <p class="text-sm text-earth-light">
                      {{ t('cookies.advertisementCookiesDescription') }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="flex flex-col gap-3 sm:flex-row">
                <button
                  class="flex-1 rounded-full bg-forest px-6 py-3 text-sm font-medium text-white transition-colors hover:bg-forest-dark"
                  @click="emit('acceptAll')"
                >
                  {{ t('cookies.acceptAll') }}
                </button>
                <button
                  class="flex-1 rounded-full bg-cream-dark px-6 py-3 text-sm font-medium text-earth transition-colors hover:bg-bark-light/20"
                  @click="emit('acceptSelected')"
                >
                  {{ t('cookies.acceptSelected') }}
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
