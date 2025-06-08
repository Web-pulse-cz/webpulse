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
                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl sm:p-6"
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
                  <div class="sm:flex sm:items-start">
                    <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                      <DialogTitle
                        as="h3"
                        class="mb-4 text-sm font-semibold text-grayDark lg:mb-6 lg:text-base"
                      >
                        Upravit denní záznam
                      </DialogTitle>
                      <div
                        v-for="(data, index) in dayRecords"
                        :key="index"
                        class="mt-6 grid w-full grid-cols-5 gap-4"
                      >
                        <BaseFormInput
                          v-model="dayRecords[index].amount"
                          label="Částka"
                          :min="0"
                          step="0.01"
                          type="number"
                          :name="'dayRecords[' + index + '][amount]'"
                          class="col-span-2"
                          :autofocus="false"
                          tabindex="-1"
                        />
                        <BaseFormInput
                          v-model="dayRecords[index].description"
                          label="Popis"
                          type="text"
                          :name="'dayRecords[' + index + '][description]'"
                          :autofocus="false"
                          class="col-span-3"
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
                          class="col-span-3"
                          tabindex="-1"
                        />
                        <BaseFormSelect
                          v-if="categories && categories.length"
                          v-model="categoryId"
                          label="Kategorie"
                          name="category_id"
                          :autofocus="false"
                          :options="categories"
                          class="col-span-2"
                          tabindex="-1"
                        />
                        <BaseFormSelect
                          v-if="currencies && currencies.length"
                          v-model="currencyId"
                          label="Měna"
                          name="currency_id"
                          :autofocus="false"
                          :options="currencies"
                          class="col-span-2"
                          tabindex="-1"
                        />
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
