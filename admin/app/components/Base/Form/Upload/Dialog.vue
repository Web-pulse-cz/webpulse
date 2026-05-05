<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { Form } from 'vee-validate';
import Draggable from 'vuedraggable';
import { TrashIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const show = defineModel('show', {
  type: Boolean,
  default: false,
});

const remoteUrl = defineModel('remoteUrl', {
  type: String,
  default: '',
});

const files = defineModel('files', {
  type: Array,
  default: [],
});

const manualUploaded = defineModel('manualUploaded', {
  type: Boolean,
  default: false,
});

defineProps({
  message: {
    type: String,
    default: '',
    required: true,
  },
  multiple: {
    type: Boolean,
    default: false,
    required: false,
  },
  acceptTypes: {
    type: String,
    default: 'image/*',
    required: false,
  },
  allowRemoteUrl: {
    type: Boolean,
    default: false,
    required: false,
  },
  remoteLoading: {
    type: Boolean,
    default: false,
    required: false,
  },
});

const emit = defineEmits([
  'save-item',
  'upload-remote-url',
  'handle-file-change',
  'upload-files',
  'remove-staged',
]);

function removeStaged(index: number) {
  const list = files.value ?? [];
  const removed = list[index];
  const next = [...list];
  next.splice(index, 1);
  files.value = next;
  emit('remove-staged', { index, removed, remaining: next });
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

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto" @click="show = false">
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
                class="relative transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl shadow-slate-200/50 transition-all sm:my-8 sm:w-full sm:max-w-4xl sm:p-8"
                @click.stop
              >
                <Form @submit="emit('save-item', $event)">
                  <div class="w-full sm:flex sm:items-start">
                    <div class="w-full text-center sm:text-left">
                      <DialogTitle
                        as="h3"
                        class="mb-2 text-center text-lg font-bold text-slate-900 lg:text-xl"
                      >
                        Nahrávání obrázků
                      </DialogTitle>

                      <p v-if="message" class="mb-6 text-center text-sm text-slate-500">
                        {{ message }}
                      </p>

                      <div
                        :class="[
                          allowRemoteUrl
                            ? 'lg:grid-cols-2 lg:divide-x lg:divide-slate-200'
                            : 'grid-cols-1',
                          'mt-6 grid gap-8 lg:gap-12',
                        ]"
                      >
                        <div class="flex h-full w-full flex-col">
                          <p
                            class="mb-5 text-center text-xs font-bold uppercase tracking-wider text-slate-400"
                          >
                            Nahrát ze zařízení
                          </p>

                          <input
                            ref="fileInput"
                            type="file"
                            class="hidden"
                            :multiple="multiple"
                            :accept="acceptTypes"
                            @change="emit('handle-file-change', $event)"
                          />

                          <div
                            v-if="files && files.length"
                            :class="[
                              multiple ? 'grid-cols-2 sm:grid-cols-3' : 'grid-cols-1',
                              'mb-6 grid w-full gap-4 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-4',
                            ]"
                          >
                            <draggable
                              v-model="files"
                              item-key="name"
                              style="display: contents"
                              class="cursor-grab active:cursor-grabbing"
                            >
                              <template #item="{ element, index }">
                                <div
                                  :class="[
                                    multiple ? 'col-span-1' : 'col-span-full mx-auto max-w-sm',
                                    'group relative overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-shadow hover:shadow-md',
                                  ]"
                                >
                                  <button
                                    type="button"
                                    title="Odstranit soubor"
                                    class="absolute right-2 top-2 z-10 flex size-7 items-center justify-center rounded-full bg-red-500 text-white shadow-sm ring-1 ring-red-600 opacity-100 transition-opacity hover:bg-red-600 md:opacity-0 md:group-hover:opacity-100"
                                    @click="removeStaged(index)"
                                  >
                                    <TrashIcon class="size-3.5" />
                                  </button>

                                  <div class="aspect-square w-full">
                                    <img
                                      v-if="element.preview"
                                      :src="element.preview"
                                      alt="náhled"
                                      class="h-full w-full object-cover"
                                    />
                                    <div
                                      v-else
                                      class="flex h-full w-full items-center justify-center p-4 text-center text-xs font-medium text-slate-500"
                                    >
                                      <span class="truncate">{{ element.name }}</span>
                                    </div>
                                  </div>
                                </div>
                              </template>
                            </draggable>
                          </div>

                          <div
                            class="mt-auto flex w-full flex-col justify-center gap-3 sm:flex-row"
                          >
                            <BaseButton
                              type="button"
                              variant="secondary"
                              size="lg"
                              class="w-full sm:w-auto"
                              @click="$refs.fileInput.click()"
                            >
                              {{ multiple ? 'Vybrat soubory' : 'Vybrat soubor' }}
                            </BaseButton>

                            <BaseButton
                              v-if="files && files.length && manualUploaded"
                              type="button"
                              variant="primary"
                              size="lg"
                              class="w-full sm:w-auto"
                              @click="emit('upload-files')"
                            >
                              {{ multiple ? 'Nahrát soubory' : 'Nahrát soubor' }}
                            </BaseButton>
                          </div>
                        </div>

                        <div v-if="allowRemoteUrl" class="flex h-full w-full flex-col lg:pl-12">
                          <p
                            class="mb-5 text-center text-xs font-bold uppercase tracking-wider text-slate-400"
                          >
                            Nahrát z URL
                          </p>

                          <div class="flex-1">
                            <BaseFormInput
                              v-model="remoteUrl"
                              label=""
                              type="text"
                              name="remote_url"
                              placeholder="https://example.com/obrazek.jpg"
                              class="mb-6"
                            />
                          </div>

                          <div class="mt-auto text-center">
                            <BaseButton
                              v-if="remoteUrl"
                              type="button"
                              variant="secondary"
                              size="lg"
                              class="w-full sm:w-auto"
                              :loading="remoteLoading"
                              :disabled="remoteLoading || !remoteUrl"
                              @click="emit('upload-remote-url', remoteUrl)"
                            >
                              Nahrát z URL
                            </BaseButton>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div
                    class="mt-4 flex justify-end gap-x-4 lg:mt-6 lg:flex-row-reverse lg:justify-start"
                  ></div>
                </Form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>
