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
        <div class="fixed inset-0 bg-deep-blue/60 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 sm:p-6 text-center">
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
                class="relative w-full max-w-2xl transform overflow-hidden rounded-[2.5rem] border-4 border-deep-blue bg-background-light p-6 sm:p-10 text-left shadow-[12px_12px_0px_0px_rgba(26,83,92,1)] transition-all"
            >
              <div class="mb-8">
                <span class="inline-block bg-primary text-white text-xs font-black px-4 py-1 rounded-full uppercase tracking-widest mb-4 border-2 border-deep-blue shadow-[2px_2px_0px_0px_rgba(26,83,92,1)]">
                  Nastavení
                </span>
                <h3 class="font-display text-4xl sm:text-5xl font-black text-deep-blue leading-tight italic">
                  {{ t('cookies.title') }}
                </h3>
              </div>

              <div class="space-y-4 mb-8">

                <div class="bg-white border-4 border-deep-blue rounded-2xl p-4 sm:p-5 flex gap-4 items-start">
                  <div class="mt-1">
                    <input type="checkbox" checked disabled class="size-6 rounded-md border-2 border-deep-blue text-deep-blue focus:ring-deep-blue" />
                  </div>
                  <div>
                    <h4 class="font-black text-deep-blue text-lg mb-1">{{ t('cookies.technicalCookiesTitle') }}</h4>
                    <p class="text-sm text-deep-blue/70 font-medium">{{ t('cookies.technicalCookiesDescription') }}</p>
                  </div>
                </div>

                <div class="bg-turquoise/10 border-4 border-deep-blue rounded-2xl p-4 sm:p-5 flex gap-4 items-start hover:bg-turquoise/20 transition-colors">
                  <div class="mt-1">
                    <input type="checkbox" v-model="cookies.analytics" class="size-6 rounded-md border-2 border-deep-blue text-primary focus:ring-primary bg-white" />
                  </div>
                  <div>
                    <h4 class="font-black text-deep-blue text-lg mb-1">{{ t('cookies.analyticsCookiesTitle') }}</h4>
                    <p class="text-sm text-deep-blue/70 font-medium">{{ t('cookies.analyticsCookiesDescription') }}</p>
                  </div>
                </div>

                <div class="bg-sunny/10 border-4 border-deep-blue rounded-2xl p-4 sm:p-5 flex gap-4 items-start hover:bg-sunny/20 transition-colors">
                  <div class="mt-1">
                    <input type="checkbox" v-model="cookies.marketing" class="size-6 rounded-md border-2 border-deep-blue text-primary focus:ring-primary bg-white" />
                  </div>
                  <div>
                    <h4 class="font-black text-deep-blue text-lg mb-1">{{ t('cookies.marketingCookiesTitle') }}</h4>
                    <p class="text-sm text-deep-blue/70 font-medium">{{ t('cookies.marketingCookiesDescription') }}</p>
                  </div>
                </div>

                <div class="bg-primary/5 border-4 border-deep-blue rounded-2xl p-4 sm:p-5 flex gap-4 items-start hover:bg-primary/10 transition-colors">
                  <div class="mt-1">
                    <input type="checkbox" v-model="cookies.advertisement" class="size-6 rounded-md border-2 border-deep-blue text-primary focus:ring-primary bg-white" />
                  </div>
                  <div>
                    <h4 class="font-black text-deep-blue text-lg mb-1">{{ t('cookies.advertisementCookiesTitle') }}</h4>
                    <p class="text-sm text-deep-blue/70 font-medium">{{ t('cookies.advertisementCookiesDescription') }}</p>
                  </div>
                </div>

              </div>

              <div class="flex flex-col sm:flex-row gap-4">
                <button
                    @click="emit('acceptAll')"
                    class="flex-1 bg-primary text-white font-black px-6 py-4 rounded-xl border-4 border-deep-blue shadow-[6px_6px_0px_0px_rgba(26,83,92,1)] hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all uppercase tracking-widest text-sm"
                >
                  {{ t('cookies.acceptAll') }}
                </button>
                <button
                    @click="emit('acceptSelected')"
                    class="flex-1 bg-turquoise text-deep-blue font-black px-6 py-4 rounded-xl border-4 border-deep-blue shadow-[6px_6px_0px_0px_rgba(26,83,92,1)] hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all uppercase tracking-widest text-sm"
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
