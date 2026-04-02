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
    required: true,
    default: new Date().getDate(),
  },
  type: {
    type: String,
    required: true,
    default: 'expense',
  },
  category: {
    type: String,
    required: true,
    default: 'Příjem',
  },
  dayRecords: {
    type: Array,
    required: true,
    default: [
      {
        id: null as number | null,
        amount: 0,
        description: '',
      },
    ],
  },
});
const emit = defineEmits(['save-day-records']);

function addDayRecord() {
  props.dayRecords.push({
    id: null,
    amount: 0,
    is_repeated: false,
    description: '',
  });
}
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
                    emit('save-day-records');
                    show = false;
                  "
                >
                  <div class="w-full text-left">
                    <DialogTitle as="h3" class="mb-6 text-lg font-bold text-slate-900">
                      Upravit záznamy - den {{ day }}. -
                      <span class="text-indigo-600">kategorie {{ category }}</span>
                    </DialogTitle>

                    <div class="max-h-[60vh] space-y-4 overflow-y-auto overflow-x-hidden pr-2">
                      <div
                        v-for="(data, index) in dayRecords"
                        :key="index"
                        class="relative rounded-xl border border-slate-200 bg-slate-50 p-4 transition-colors hover:border-slate-300"
                      >
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-5">
                          <BaseFormInput
                            v-model="data.amount"
                            label="Částka"
                            :min="0"
                            step="0.01"
                            type="number"
                            :name="'dayRecords[' + index + '][amount]'"
                            class="sm:col-span-2"
                          />
                          <BaseFormInput
                            v-model="data.description"
                            label="Popis"
                            type="text"
                            :name="'dayRecords[' + index + '][description]'"
                            class="sm:col-span-3"
                          />
                          <BaseFormCheckbox
                            v-model="data.is_repeated"
                            :name="'dayRecords[' + index + '][is_repeated]'"
                            label="Opakovaně"
                            class="pt-1 sm:col-span-5"
                            :checked="data.is_repeated"
                            label-color="slate-700"
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
                      type="button"
                      variant="warning"
                      size="lg"
                      class="w-full sm:w-auto"
                      @click="addDayRecord"
                    >
                      Přidat záznam
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
