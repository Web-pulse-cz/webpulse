<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { Form } from 'vee-validate';

const show = defineModel('show', {
  type: Boolean,
  default: false,
});
const props = defineProps({
  day: {
    type: Number,
    required: false,
    default: new Date().getDate(),
  },
  categories: {
    type: Array,
    required: false,
    default: () => [],
  },
  currencies: {
    type: Array,
    required: false,
    default: () => [],
  },
  dayRecords: {
    type: Array,
    required: false,
    default: [
      {
        id: null as number | null,
        amount: 0,
        description: '',
      },
    ],
  },
});

const categoryId = ref(1);
const currencyId = ref(1);
const type = ref('expense');
const emit = defineEmits(['save-day-records']);
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
                <Form
                  @submit="
                    emit('save-day-records', {
                      categoryId,
                      currencyId,
                      day,
                      type,
                      dayRecords,
                    });
                    show = false;
                  "
                >
                  <div class="w-full text-left">
                    <DialogTitle as="h3" class="mb-6 text-lg font-bold text-slate-900">
                      Upravit denní záznam
                    </DialogTitle>

                    <div class="max-h-[65vh] overflow-y-auto overflow-x-hidden pr-2">
                      <div
                        v-for="(data, index) in dayRecords"
                        :key="index"
                        class="mb-4 rounded-xl border border-slate-200 bg-slate-50 p-5 transition-colors hover:border-slate-300"
                      >
                        <div class="mb-4 grid w-full grid-cols-1 gap-4 sm:grid-cols-5">
                          <BaseFormInput
                            v-model="dayRecords[index].amount"
                            label="Částka"
                            :min="0"
                            step="0.01"
                            type="number"
                            :name="'dayRecords[' + index + '][amount]'"
                            class="sm:col-span-2"
                            :autofocus="false"
                            tabindex="-1"
                          />
                          <BaseFormInput
                            v-model="dayRecords[index].description"
                            label="Popis"
                            type="text"
                            :name="'dayRecords[' + index + '][description]'"
                            :autofocus="false"
                            class="sm:col-span-3"
                            tabindex="-1"
                          />
                        </div>

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                          <BaseFormSelect
                            v-if="categories && categories.length"
                            v-model="categoryId"
                            label="Kategorie"
                            name="category_id"
                            :autofocus="false"
                            :options="categories"
                            class="w-full"
                            tabindex="-1"
                          />
                          <BaseFormSelect
                            v-if="currencies && currencies.length"
                            v-model="currencyId"
                            label="Měna"
                            name="currency_id"
                            :autofocus="false"
                            :options="currencies"
                            class="w-full"
                            tabindex="-1"
                          />
                          <BaseFormSelect
                            v-model="type"
                            label="Typ"
                            name="type"
                            :autofocus="false"
                            :options="[
                              { label: 'Výdej', name: 'Výdej', value: 'expense' },
                              { label: 'Příjem', name: 'Příjem', value: 'income' },
                            ]"
                            class="w-full"
                            tabindex="-1"
                          />
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="mt-8 flex flex-col gap-3 sm:flex-row-reverse sm:justify-start">
                    <BaseButton type="submit" variant="success" size="lg" class="w-full sm:w-auto">
                      Uložit
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
