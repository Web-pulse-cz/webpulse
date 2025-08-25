<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { Form } from 'vee-validate';
import { useActivityStore } from '~/stores/activityStore';

const activityStore = useActivityStore();

const show = defineModel('show', {
  type: Boolean,
  default: false as boolean,
});

const item = defineModel('item', {
  type: Object,
  default: {} as Record<string, any>,
});

defineProps({
  phases: {
    type: Array,
    required: true,
    default: [] as [],
  },
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
                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl sm:p-6"
              >
                <Form @submit="emit('save-item', item)">
                  <div class="sm:flex sm:items-start">
                    <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                      <DialogTitle
                        as="h3"
                        class="mb-4 text-sm font-semibold text-grayDark lg:mb-6 lg:text-base"
                      >
                        {{ item.id ? 'Upravit záznam historie' : 'Přidat záznam historie' }}
                      </DialogTitle>
                      <div class="mt-8 grid grid-cols-2 gap-x-8 gap-y-4">
                        <BaseFormInput
                          v-model="item.name"
                          type="text"
                          label="Název"
                          name="name"
                          class="col-span-full text-sm font-medium text-grayDark"
                        />
                        <BaseFormTextarea
                          v-model="item.description"
                          class="col-span-full text-sm font-medium text-grayDark"
                          label="Poznámka"
                          name="description"
                          rules="required"
                        />
                        <!-- <BaseFormSelect
													v-model="item.contact_phase_id"
													:options="phases"
													label="Fáze"
													name="contact_phase_id"
													class="col-span-1"
												/> -->
                        <BaseFormSelect
                          v-model="item.activity_id"
                          :options="activityStore.activitiesOptions"
                          label="Aktivita"
                          name="activity_id"
                          class="col-span-1"
                        />
                        <BaseFormSelect
                          v-model="item.type"
                          :options="[
                            { value: 'activity', name: 'Aktivita' },
                            { value: 'email', name: 'E-mail' },
                            { value: 'meeting', name: 'Meeting' },
                            { value: 'other', name: 'Neurčeno' },
                            { value: 'call', name: 'Telefonát' },
                          ]"
                          label="Typ"
                          name="type"
                          class="col-span-1"
                        />
                      </div>
                    </div>
                  </div>
                  <div
                    class="mt-4 flex justify-end gap-x-4 lg:mt-6 lg:flex-row-reverse lg:justify-start"
                  >
                    <BaseButton type="submit" variant="success" size="lg">
                      {{ item.id ? 'Uložit' : 'Přidat' }}
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
