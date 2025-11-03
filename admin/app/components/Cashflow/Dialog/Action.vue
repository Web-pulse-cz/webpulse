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
                    emit('save-day-records');
                    show = false;
                  "
                >
                  <div class="sm:flex sm:items-start">
                    <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                      <DialogTitle
                        as="h3"
                        class="mb-4 text-sm font-semibold text-grayDark lg:mb-6 lg:text-base"
                      >
                        Upravit záznamy - den {{ day }}. -
                        <span class="text-primaryLight">kategorie {{ category }}</span>
                      </DialogTitle>
                      <div
                        v-for="(data, index) in dayRecords"
                        :key="index"
                        class="mt-6 grid w-full grid-cols-5 gap-x-4 gap-y-1"
                      >
                        <BaseFormInput
                          v-model="data.amount"
                          label="Částka"
                          :min="0"
                          step="0.01"
                          type="number"
                          :name="'dayRecords[' + index + '][amount]'"
                          class="col-span-2"
                        />
                        <BaseFormInput
                          v-model="data.description"
                          label="Popis"
                          type="text"
                          :name="'dayRecords[' + index + '][description]'"
                          class="col-span-3"
                        />
                        <BaseFormCheckbox
                          v-model="data.is_repeated"
                          :name="'dayRecords[' + index + '][is_repeated]'"
                          label="Opakovaně"
                          class="col-span-1"
                          :checked="dayRecords[index].is_repeated"
                          label-color="grayCustom"
                        />
                      </div>
                    </div>
                  </div>
                  <div
                    class="mt-4 flex justify-end gap-x-4 lg:mt-6 lg:flex-row-reverse lg:justify-start"
                  >
                    <BaseButton type="submit" variant="success" size="lg"> Uložit </BaseButton>
                    <BaseButton type="button" variant="warning" size="lg" @click="addDayRecord">
                      Přidat záznam
                    </BaseButton>
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
