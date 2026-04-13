<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { Form } from 'vee-validate';
import { ref } from 'vue';

const show = defineModel('show', {
  type: Boolean,
  default: false,
});
const category = defineModel('category', {
  type: Object,
  default: {
    id: 0,
    name: '',
  },
});
const emit = defineEmits(['submit']);
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
                class="relative transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl shadow-slate-200/50 transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-8"
              >
                <Form
                  @submit="
                    emit('submit', category);
                    show = false;
                  "
                >
                  <div class="w-full text-left">
                    <DialogTitle as="h3" class="mb-6 text-lg font-bold text-slate-900">
                      {{ category.id ? 'Upravit kategorii' : 'Vytvořit kategorii' }}
                    </DialogTitle>

                    <div class="mt-2 grid w-full grid-cols-1 gap-6">
                      <BaseFormInput
                        v-model="category.name"
                        name="name"
                        label="Název"
                        type="text"
                        class="col-span-full"
                      />
                    </div>
                  </div>

                  <div class="mt-8 sm:flex sm:flex-row-reverse sm:gap-3">
                    <BaseButton type="submit" variant="success" size="lg" class="w-full sm:w-auto">
                      Vytvořit
                    </BaseButton>
                    <BaseButton
                      ref="cancelButtonRef"
                      type="button"
                      variant="secondary"
                      size="lg"
                      class="mt-3 w-full sm:mt-0 sm:w-auto"
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
