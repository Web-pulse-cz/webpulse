<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { Form } from 'vee-validate';
import Draggable from 'vuedraggable';
import { XMarkIcon } from '@heroicons/vue/24/outline';

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

const emit = defineEmits(['save-item', 'upload-remote-url', 'handle-file-change', 'upload-files']);
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
                class="relative transform overflow-hidden rounded-3xl bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-5xl sm:p-6"
              >
                <Form @submit="emit('save-item', $event)">
                  <div class="sm:flex sm:items-start">
                    <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                      <DialogTitle
                        as="h3"
                        class="mb-4 text-center text-sm font-semibold text-grayDark lg:mb-6 lg:text-lg"
                      >
                        Nahrávání obrázků
                      </DialogTitle>
                      <p class="text-md text-center text-grayCustom">{{ message }}</p>
                      <div
                        :class="[
                          allowRemoteUrl ? 'grid-cols-2' : 'grid-cols-1',
                          'mt-8 grid gap-x-16 p-4',
                        ]"
                      >
                        <div class="col-span-1">
                          <p class="mb-6 text-center font-semibold">Nahrát ze zařízení</p>
                          <input
                            ref="fileInput"
                            type="file"
                            class="block hidden bg-indigo-400 text-left text-xs font-medium text-grayCustom lg:text-sm/6"
                            :multiple="multiple"
                            :accept="acceptTypes"
                            @change="emit('handle-file-change', $event)"
                          />
                          <div
                            v-if="files && files.length"
                            :class="[
                              multiple ? 'grid-cols-4' : 'grid-cols-1',
                              'my-4 grid w-full gap-4 rounded border-2 border-dashed border-gray-300 bg-gray-50 p-2',
                            ]"
                          >
                            <draggable
                              v-model="files"
                              item-key="name"
                              style="display: contents"
                              class="cursor-grab"
                            >
                              <template #item="{ element }">
                                <div
                                  :class="[
                                    multiple ? 'col-span-1' : 'col-span-full',
                                    'relative col-span-full overflow-hidden rounded-md border border-gray-300',
                                  ]"
                                >
                                  <UTooltip
                                    text="Odstranit soubor"
                                    placement="top"
                                    class="absolute right-1 top-1"
                                  >
                                  </UTooltip>

                                  <img
                                    v-if="element.preview"
                                    :src="element.preview"
                                    alt="náhled"
                                    class="h-full w-full object-cover"
                                  />
                                  <div
                                    v-else
                                    class="flex h-40 items-center justify-center bg-white text-sm text-gray-500"
                                  >
                                    {{ element.name }}
                                  </div>
                                </div>
                              </template>
                            </draggable>
                          </div>
                          <div class="flex w-full flex-wrap justify-center gap-x-4">
                            <BaseButton
                              type="button"
                              variant="secondary"
                              size="lg"
                              @click="$refs.fileInput.click()"
                            >
                              {{ multiple ? 'Vybrat soubory' : 'Vybrat soubor' }}
                            </BaseButton>

                            <BaseButton
                              v-if="files && files.length && manualUploaded"
                              type="button"
                              variant="primary"
                              size="lg"
                              @click="emit('upload-files')"
                            >
                              {{ multiple ? 'Nahrát soubory' : 'Nahrát soubor' }}
                            </BaseButton>
                          </div>
                        </div>
                        <div v-if="allowRemoteUrl" class="col-span-1">
                          <p class="mb-6 text-center font-semibold">Nahrát z URL</p>
                          <BaseFormInput
                            v-model="remoteUrl"
                            label=""
                            type="text"
                            name="remote_url"
                            placeholder="https://example.com/image.jpg"
                            class="mb-4"
                          />
                          <div class="w-full text-center">
                            <BaseButton
                              v-if="remoteUrl"
                              type="button"
                              variant="secondary"
                              size="lg"
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
