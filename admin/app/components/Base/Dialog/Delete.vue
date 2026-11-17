<script setup>
import { ref } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const show = defineModel('show', {
  type: Boolean,
  default: false,
});
const item = defineModel('item', {
  type: Object,
  default: {
    id: null,
    name: '',
  },
});
const props = defineProps({
  // Probíhá mazání → spinner na Potvrdit, tlačítka zakázaná, dialog nelze zavřít
  loading: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Smazat položku',
  },
  // Volitelný vlastní text (např. hromadné mazání)
  message: {
    type: String,
    default: '',
  },
});
const emit = defineEmits(['delete-item']);
const cancelButtonRef = ref(null);

function close() {
  if (props.loading) return;
  show.value = false;
}

function confirm() {
  if (props.loading) return;
  emit('delete-item', item.value?.id);
}
</script>

<template>
  <TransitionRoot as="template" :show="show">
    <Dialog class="relative z-50" :initial-focus="cancelButtonRef" @close="close">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" />
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
              class="relative transform overflow-hidden rounded-2xl bg-white px-4 pb-4 pt-5 text-left shadow-xl ring-1 ring-slate-200 transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
            >
              <div class="sm:flex sm:items-start">
                <div
                  class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:size-10"
                >
                  <ExclamationTriangleIcon class="size-6 text-red-600" aria-hidden="true" />
                </div>
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                  <DialogTitle as="h3" class="text-base font-semibold text-slate-900">
                    {{ title }}
                  </DialogTitle>
                  <div class="mt-2">
                    <p v-if="message" class="text-sm text-slate-500">{{ message }}</p>
                    <p v-else class="text-sm text-slate-500">
                      Opravdu si přejete smazat položku
                      <span class="font-semibold text-slate-700">{{
                        item?.name || `s ID ${item?.id}`
                      }}</span
                      >? Tato akce je nevratná.
                    </p>
                  </div>
                </div>
              </div>
              <div class="mt-5 gap-3 sm:mt-4 sm:flex sm:flex-row-reverse">
                <BaseButton
                  size="lg"
                  variant="danger"
                  class="w-full sm:w-auto"
                  :loading="loading"
                  @click="confirm"
                >
                  Potvrdit
                </BaseButton>
                <BaseButton
                  ref="cancelButtonRef"
                  size="lg"
                  variant="secondary"
                  class="mt-3 w-full sm:mt-0 sm:w-auto"
                  :disabled="loading"
                  @click="close"
                >
                  Zrušit
                </BaseButton>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
