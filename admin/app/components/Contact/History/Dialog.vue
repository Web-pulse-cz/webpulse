<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { Form } from 'vee-validate';
import { useActivityStore } from '~/../stores/activityStore';

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
                class="relative transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl shadow-slate-200/50 transition-all sm:my-8 sm:w-full sm:max-w-xl sm:p-8"
              >
                <Form @submit="emit('save-item', item)">
                  <div class="w-full text-left">
                    <DialogTitle as="h3" class="mb-6 text-lg font-bold text-slate-900">
                      {{ item.id ? 'Upravit záznam historie' : 'Přidat záznam historie' }}
                    </DialogTitle>

                    <div class="mt-2 grid w-full grid-cols-1 gap-6 sm:grid-cols-2">
                      <BaseFormInput
                        v-model="item.name"
                        type="text"
                        label="Název"
                        name="name"
                        class="sm:col-span-2"
                      />

                      <BaseFormTextarea
                        v-model="item.description"
                        label="Poznámka"
                        name="description"
                        rules="required"
                        class="sm:col-span-2"
                      />

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

                  <div class="mt-8 flex flex-col gap-3 sm:flex-row-reverse sm:justify-start">
                    <BaseButton type="submit" variant="success" size="lg" class="w-full sm:w-auto">
                      {{ item.id ? 'Uložit' : 'Přidat' }}
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
