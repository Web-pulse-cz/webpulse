<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { Form } from 'vee-validate';
const show = defineModel('show', {
  type: Boolean,
  default: false,
});
const filter = defineModel('filter', {
  type: String,
  default: 'month',
});
const year = defineModel('year', {
  type: String,
  default: new Date().getFullYear(),
});
const month = defineModel('month', {
  type: String,
  default: new Date().getMonth() + 1,
});
const emit = defineEmits(['submit']);

const years = computed(() => {
  const currentYear = new Date().getFullYear();
  const years = [];
  for (let i = currentYear; i >= 2024; i--) {
    years.push({ value: i.toString(), name: i.toString() });
  }
  return years;
});
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
                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
              >
                <Form
                  @submit="
                    emit('submit');
                    show = false;
                  "
                >
                  <div class="sm:flex sm:items-start">
                    <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                      <DialogTitle
                        as="h3"
                        class="mb-4 text-sm font-semibold text-grayDark lg:mb-6 lg:text-base"
                      >
                        Filtrovat
                      </DialogTitle>
                      <div class="mt-6 grid w-full grid-cols-2 gap-4">
                        <BaseFormSelect
                          v-model="filter"
                          name="filter"
                          label="Typ"
                          :options="[
                            { value: 'month', name: 'Podle měsíce' },
                            { value: 'year', name: 'Podle roku' },
                          ]"
                          class="col-span-full"
                        />
                        <BaseFormSelect
                          v-model="year"
                          name="year"
                          label="Rok"
                          :options="years"
                          class="col-span-1"
                        />
                        <BaseFormSelect
                          v-if="filter === 'month'"
                          v-model="month"
                          name="month"
                          label="Měsíc"
                          :options="[
                            { value: '1', name: 'Leden' },
                            { value: '2', name: 'Únor' },
                            { value: '3', name: 'Březen' },
                            { value: '4', name: 'Duben' },
                            { value: '5', name: 'Květen' },
                            { value: '6', name: 'Červen' },
                            { value: '7', name: 'Červenec' },
                            { value: '8', name: 'Srpen' },
                            { value: '9', name: 'Září' },
                            { value: '10', name: 'Říjen' },
                            { value: '11', name: 'Listopad' },
                            { value: '12', name: 'Prosinec' },
                          ]"
                          class="col-span-1"
                        />
                      </div>
                    </div>
                  </div>
                  <div
                    class="mt-4 flex justify-end gap-x-4 lg:mt-6 lg:flex-row-reverse lg:justify-start"
                  >
                    <BaseButton type="submit" variant="success" size="lg"> Filtrovat </BaseButton>
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
