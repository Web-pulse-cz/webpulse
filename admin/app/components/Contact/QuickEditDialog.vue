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
      <Dialog class="relative z-50">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" />
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
                class="relative transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl shadow-slate-200/50 transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-8"
              >
                <Form @submit="emit('save-item', item)">
                  <div class="w-full text-left">
                    <DialogTitle
                      as="h3"
                      class="mb-6 text-xl font-bold tracking-tight text-slate-900 lg:text-2xl"
                    >
                      {{ item.firstname + ' ' + item.lastname }}
                    </DialogTitle>

                    <div class="mt-4 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:gap-12">
                      <div class="space-y-4">
                        <div
                          v-for="(val, label) in {
                            Fáze: 'phase',
                            Zdroj: 'source',
                            'E-mail': 'email',
                            Telefon: 'phone',
                            'Obor/Studium': 'occupation',
                            'Sen/cíl': 'goal',
                          }"
                          :key="label"
                          class="flex flex-col border-b border-slate-50 pb-2 last:border-0"
                        >
                          <span
                            class="mb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400"
                            >{{ label }}</span
                          >

                          <div v-if="val === 'phase'" class="mt-0.5">
                            <PropsBadge :color="item.phase_color">{{ item.phase }}</PropsBadge>
                          </div>
                          <div v-else-if="val === 'source'" class="mt-0.5">
                            <PropsBadge :color="item.source_color">{{ item.source }}</PropsBadge>
                          </div>
                          <p v-else class="text-sm font-semibold text-slate-700">
                            {{ item[val] ?? '-' }}
                          </p>
                        </div>
                      </div>

                      <div class="flex flex-col gap-6">
                        <BaseFormTextarea
                          v-model="item.note"
                          label="Poznámka"
                          name="note"
                          rules="required"
                          rows="4"
                        />
                        <BaseFormInput
                          v-model="item.formatted_last_contacted_at"
                          type="datetime-local"
                          label="Poslední kontakt"
                          name="last_contacted_at"
                        />
                      </div>
                    </div>
                  </div>

                  <div class="mt-10 flex flex-col gap-3 sm:flex-row-reverse sm:justify-start">
                    <BaseButton type="submit" variant="success" size="lg" class="w-full sm:w-auto">
                      Uložit změny
                    </BaseButton>
                    <BaseButton
                      ref="cancelButtonRef"
                      type="button"
                      variant="secondary"
                      size="lg"
                      class="w-full sm:w-auto"
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
