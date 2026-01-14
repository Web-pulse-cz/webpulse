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
    <Dialog class="relative z-10" @close="open = false">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-200/75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div
          class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
        >
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
              class="relative transform overflow-hidden rounded-lg border border-brand bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl sm:p-6"
            >
              <div>
                <div class="mt-3 sm:mt-5">
                  <BasePropsHeading type="h3" color="bg-gray">
                    {{ t('cookies.title') }}
                  </BasePropsHeading>
                  <div class="mt-5 text-primary sm:mt-6">
                    <div class="flex py-3">
                      <BaseFormCheckbox
                        v-model="cookies.technical"
                        :checked="cookies.technical"
                        :disabled="true"
                        name="technical"
                      />
                      <div class="ml-4">
                        <BasePropsParagraph color="black" bold="bold" class="mb-2">
                          {{ t('cookies.technicalCookiesTitle') }}
                        </BasePropsParagraph>
                        <BasePropsParagraph color="black">
                          {{ t('cookies.technicalCookiesDescription') }}
                        </BasePropsParagraph>
                      </div>
                    </div>
                    <div class="flex py-3">
                      <BaseFormCheckbox
                        v-model="cookies.marketing"
                        :checked="cookies.marketing"
                        name="marketing"
                      />
                      <div class="ml-4">
                        <BasePropsParagraph color="black" bold="bold" class="mb-2">
                          {{ t('cookies.marketingCookiesTitle') }}
                        </BasePropsParagraph>
                        <BasePropsParagraph color="black">
                          {{ t('cookies.marketingCookiesDescription') }}
                        </BasePropsParagraph>
                      </div>
                    </div>
                    <div class="flex py-3">
                      <BaseFormCheckbox
                        v-model="cookies.analytics"
                        :checked="cookies.analytics"
                        name="analytics"
                      />
                      <div class="ml-4">
                        <BasePropsParagraph color="black" bold="bold" class="mb-2">
                          {{ t('cookies.analyticsCookiesTitle') }}
                        </BasePropsParagraph>
                        <BasePropsParagraph color="black">
                          {{ t('cookies.analyticsCookiesDescription') }}
                        </BasePropsParagraph>
                      </div>
                    </div>
                    <div class="flex py-3">
                      <BaseFormCheckbox
                        v-model="cookies.advertisement"
                        :checked="cookies.advertisement"
                        name="advertisement"
                      />
                      <div class="ml-4">
                        <BasePropsParagraph color="black" bold="bold" class="mb-2">
                          {{ t('cookies.advertisementCookiesTitle') }}
                        </BasePropsParagraph>
                        <BasePropsParagraph color="black">
                          {{ t('cookies.advertisementCookiesDescription') }}
                        </BasePropsParagraph>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="mt-5 flex gap-x-4 sm:mt-6">
                <BaseButton size="lg" @click="emit('acceptAll')">
                  {{ t('cookies.acceptAll') }}
                </BaseButton>
                <BaseButton size="lg" @click="emit('acceptSelected')">
                  {{ t('cookies.acceptSelected') }}
                </BaseButton>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
