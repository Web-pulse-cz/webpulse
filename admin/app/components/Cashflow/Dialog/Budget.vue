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
                class="relative transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl shadow-slate-200/50 transition-all sm:my-8 sm:w-full sm:max-w-sm sm:p-8"
              >
                <Form
                  @submit="
                    emit('save-budget', id, budget);
                    show = false;
                  "
                >
                  <div class="w-full text-left">
                    <DialogTitle as="h3" class="mb-6 text-lg font-bold text-slate-900">
                      Upravit budget pro měsíc {{ month }}/{{ year }}
                    </DialogTitle>

                    <div class="mt-2 w-full">
                      <input
                        v-model="budget"
                        :min="0"
                        step="0.01"
                        type="number"
                        class="block w-full rounded-xl border-0 px-4 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 transition-all duration-200 placeholder:text-slate-400 hover:ring-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500"
                        name="budget"
                        :autofocus="false"
                        tabindex="-1"
                      />
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
