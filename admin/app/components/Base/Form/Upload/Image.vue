<script setup lang="ts">
import { ref, watch } from 'vue';

import Draggable from 'vuedraggable';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import useImageFormatMessage from '~/composables/useImageFormatMessage';

const { $toast } = useNuxtApp();
const manualUploaded = ref(false);
const props = defineProps({
  fileType: {
    type: String,
    default: 'image',
    required: false,
  },
  viewType: {
    type: String,
    default: 'single',
    required: false,
  },
  label: {
    type: String,
    default: 'Nahrát soubory',
    required: false,
  },
  format: {
    type: String,
    default: 'service',
    required: true,
  },
  type: {
    type: String,
    default: 'icon',
    required: false,
  },
  multiple: {
    type: Boolean,
    default: false,
  },
  modelValue: {
    type: String,
    default: '',
    required: false,
  },
});

const emit = defineEmits(['update-files' /* , 'update:modelValue' */]);

const imageFormatMessage = await useImageFormatMessage(
  props.fileType,
  props.multiple,
  props.type,
  props.format,
);

// Inicializace souborů z v-model
const files = ref<{ file: File | null; name: string; preview?: string }[]>([]);

// Synchronizace files s v-model při změně modelValue
watch(
  () => props.modelValue,
  () => {
    if (!props.modelValue) {
      files.value = [];
      return;
    }
    files.value = [
      {
        file: null, // u předvyplněných souborů File není
        name: props.modelValue,
        preview: `/content/images/${props.type}/${props.format}/${props.modelValue}`,
      },
    ];
  },
  { immediate: true },
);

const acceptTypes = 'image/*,application/pdf,application/msword';

function handleFileChange(event: Event) {
  const input = event.target as HTMLInputElement;
  if (!input.files) return;

  const filesArray = Array.from(input.files);
  if (props.multiple) {
    for (const file of filesArray) {
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
          const preview = e.target?.result as string;
          files.value.push({ file, name: file.name, preview });
          /* emit(
            'update:modelValue',
            files.value.map((f) => ({ name: f.name, preview: f.preview })),
          ); */
        };
        reader.readAsDataURL(file);
      } else {
        files.value.push({ file, name: file.name });
        /* emit(
          'update:modelValue',
          files.value.map((f) => ({ name: f.name, preview: f.preview })),
        ); */
      }
    }
  } else {
    const file = filesArray[0];
    if (file) {
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
          const preview = e.target?.result as string;
          files.value = [{ file, name: file.name, preview }];
          /* emit(
            'update:modelValue',
            files.value.map((f) => ({ name: f.name, preview: f.preview })),
          ); */
        };
        reader.readAsDataURL(file);
      } else {
        files.value = [{ file, name: file.name }];
        /* emit(
          'update:modelValue',
          files.value.map((f) => ({ name: f.name, preview: f.preview })),
        ); */
      }
    }
  }
  manualUploaded.value = true;
}

function removeFile(index: number) {
  files.value.splice(index, 1);
  /* emit(
    'update:modelValue',
    files.value.map((f) => ({ name: f.name, preview: f.preview })),
  ); */
}

async function uploadFiles() {
  const formData = new FormData();
  files.value.forEach(({ file }) => {
    if (file) formData.append('images[]', file);
  });

  const client = useSanctumClient();
  formData.append('securityKey', 'your_security_key_here');
  formData.append('type', props.type);

  try {
    const response = await client('/api/filemanager/upload/images', {
      method: 'POST',
      body: formData,
    });

    const result = await response;
    emit('update-files', result);

    $toast.show({
      summary: 'Hotovo',
      detail: props.multiple ? 'Soubory byly úspěšně nahrány.' : 'Soubor byl úspěšně nahrán.',
      severity: 'success',
      group: 'bc',
    });
  } catch (error) {
    console.log(error);
    $toast.show({
      summary: 'Chyba',
      detail: props.multiple
        ? 'Nepodařilo se nahrát jeden nebo více souborů. Zkuste to prosím později.'
        : 'Nepodařilo se nahrát soubor. Zkuste to prosím později.',
      severity: 'error',
      group: 'bc',
    });
  }
}
</script>

<template>
  <div class="col-span-full w-full">
    <label class="mb-2 block text-left text-xs font-medium text-grayCustom lg:text-sm/6">{{
      label
    }}</label>
    <span
      class="inline-flex w-full items-center rounded-md bg-blue-50 px-2 py-1 text-sm font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10"
    >
      {{ imageFormatMessage }}
    </span>

    <input
      ref="fileInput"
      type="file"
      class="block hidden bg-indigo-400 text-left text-xs font-medium text-grayCustom lg:text-sm/6"
      :multiple="multiple"
      :accept="acceptTypes"
      @change="handleFileChange"
    />
    <div
      :class="[
        multiple ? 'grid-cols-4' : 'grid-cols-1',
        'my-4 grid w-full gap-4 rounded bg-gray-100 p-6 ring-1 ring-inset ring-grayLight',
      ]"
    >
      <draggable v-model="files" item-key="name" style="display: contents" class="cursor-grab">
        <template #item="{ element, index }">
          <div
            :class="[
              multiple ? 'col-span-1' : 'col-span-full',
              'relative col-span-full overflow-hidden rounded-md border border-gray-300',
            ]"
          >
            <UTooltip text="Odstranit soubor" placement="top" class="absolute right-1 top-1">
              <div
                class="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-dangerLight ring-1 ring-danger"
                @click="removeFile(index)"
              >
                <XMarkIcon class="h-4 w-4 text-white" />
              </div>
            </UTooltip>
            <img :src="element.preview" alt="náhled" class="h-full w-full object-cover" />
          </div>
        </template>
      </draggable>
    </div>

    <div class="flex w-full flex-wrap gap-x-4">
      <BaseButton type="button" variant="secondary" size="md" @click="$refs.fileInput.click()">{{
        multiple ? 'Vybrat soubory' : 'Vybrat soubor'
      }}</BaseButton>
      <BaseButton
        v-if="files && files.length && manualUploaded"
        type="button"
        variant="primary"
        size="md"
        @click="uploadFiles"
        >{{ multiple ? 'Nahrát soubory' : 'Nahrát soubor' }}</BaseButton
      >
    </div>
  </div>
</template>
