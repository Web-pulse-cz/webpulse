<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { Form } from 'vee-validate';

const show = defineModel('show', {
  type: Boolean,
  default: false,
});
const budget = defineModel('budget', {
  type: Number,
  default: 0,
});
const id = defineModel('id', {
  type: Number,
  default: 0,
});
const props = defineProps({
  month: {
    type: Number,
    required: true,
    default: new Date().getMonth() + 1,
  },
  year: {
    type: Number,
    required: true,
    default: new Date().getFullYear(),
  },
});
const emit = defineEmits(['save-budget']);
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
                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-sm sm:p-6"
              >
                <Form
                  @submit="
                    emit('save-budget', id, budget);
                    show = false;
                  "
                >
                  <div class="sm:flex sm:items-start">
                    <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                      <DialogTitle
                        as="h3"
                        class="mb-4 text-sm font-semibold text-grayDark lg:mb-6 lg:text-base"
                      >
                        Upravit budget pro měsíc {{ month }}/{{ year }}
                      </DialogTitle>
                      <div class="mt-6 grid w-full grid-cols-5 gap-4">
                        <input
                          v-model="budget"
                          :min="0"
                          step="0.01"
                          type="number"
                          class="sm:text-sm/6' col-span-full mt-2 block w-full rounded-md border-0 py-2 text-grayDark shadow-sm ring-1 ring-inset ring-grayLight placeholder:text-grayLight focus:ring-1 focus:ring-inset focus:ring-primaryLight"
                          name="budget"
                          :autofocus="false"
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
