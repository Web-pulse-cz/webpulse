<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { Form } from 'vee-validate';

const show = defineModel('show', {
  type: Boolean,
  default: false,
});
const item = defineModel('item', {
  type: Object,
  default: {},
});

const emit = defineEmits(['save-item']);
</script>

<template>
  <div>
    <TransitionRoot as="template" :show="show">
      <Dialog class="relative z-10">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-grayCustom/75 transition-opacity" />
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
                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-6"
              >
                <Form @submit="emit('save-item', item)">
                  <div class="sm:flex sm:items-start">
                    <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                      <DialogTitle
                        as="h3"
                        class="mb-4 text-sm font-semibold text-grayDark lg:mb-6 lg:text-base"
                      >
                        {{ item.firstname + ' ' + item.lastname }}
                      </DialogTitle>
                      <div class="mt-8 grid grid-cols-2 gap-x-8">
                        <div class="col-span-1 grid grid-cols-1 gap-y-4 text-wrap">
                          <p class="col-span-full text-sm font-medium text-grayDark">
                            <span class="font-semibold">Fáze:</span>
                            <PropsBadge :color="item.phase_color">
                              {{ item.phase }}
                            </PropsBadge>
                          </p>
                          <p class="col-span-full text-sm font-medium text-grayDark">
                            <span class="font-semibold">Zdroj:</span>
                            <PropsBadge :color="item.source_color">
                              {{ item.source }}
                            </PropsBadge>
                          </p>
                          <p class="col-span-full text-sm font-medium text-grayDark">
                            <span class="font-semibold">E-mail:</span> {{ item.email ?? '-' }}
                          </p>
                          <p class="col-span-full text-sm font-medium text-grayDark">
                            <span class="font-semibold">Telefon:</span> {{ item.phone ?? '-' }}
                          </p>
                          <p class="col-span-full text-sm font-medium text-grayDark">
                            <span class="font-semibold">Práce/obor/studium:</span>
                            {{ item.occupation ?? '-' }}
                          </p>
                          <p class="col-span-full text-sm font-medium text-grayDark">
                            <span class="font-semibold">Sen/cíl:</span> {{ item.goal ?? '-' }}
                          </p>
                        </div>
                        <div class="col-span-1 grid grid-cols-1 gap-y-4 text-wrap">
                          <BaseFormTextarea
                            v-model="item.note"
                            class="col-span-full text-sm font-medium text-grayDark"
                            label="Poznámka"
                            rules="required"
                          />
                          <BaseFormInput
                            v-model="item.formatted_last_contacted_at"
                            type="datetime-local"
                            label="Poslední kontakt/pokus o kontakt"
                            name="last_contacted_at"
                            class="col-span-full text-sm font-medium text-grayDark"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                  <div
                    class="mt-4 flex justify-end gap-x-4 lg:mt-6 lg:flex-row-reverse lg:justify-start"
                  >
                    <BaseButton type="submit" variant="success" size="lg"> Uložit </BaseButton>
                    <BaseButton
                      ref="cancelButtonRef"
                      type="button"
                      variant="secondary"
                      size="lg"
                      @click="show = false"
                    >
                      Zavřít
                    </BaseButton>
                  </div>
                </Form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>
