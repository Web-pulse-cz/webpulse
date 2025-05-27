<script setup lang="ts">
import { ref } from 'vue';
import Draggable from 'vuedraggable';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import useImageFormatMessage from '~/composables/useImageFormatMessage';

const toast = useToast();

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
});
const emit = defineEmits(['update-files']);
const imageFormatMessage = await useImageFormatMessage(
  props.fileType,
  props.multiple,
  props.type,
  props.format,
);

const files = ref<{ file: File; name: string; preview?: string }[]>([]);
const acceptTypes = 'image/*,application/pdf,application/msword'; // Přijatelné typy

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
        };
        reader.readAsDataURL(file);
      } else {
        files.value.push({ file, name: file.name });
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
        };
        reader.readAsDataURL(file);
      } else {
        files.value = [{ file, name: file.name }];
      }
    }
  }
}

function removeFile(index: number) {
  files.value.splice(index, 1);
}

async function uploadFiles() {
  const formData = new FormData();
  files.value.forEach(({ file }) => {
    formData.append('images[]', file);
  });

  const client = useSanctumClient();
  formData.append('securityKey', 'your_security_key_here'); // Přidejte svůj bezpečnostní klíč
  formData.append('type', props.type);
  try {
    const response = await client<{}>('/api/filemanager/upload/images', {
      method: 'POST',
      body: formData,
    });

    const result = await response;
    emit('update-files', result);

    toast.add({
      title: 'Hotovo',
      description: props.multiple ? 'Soubory byly úspěšně nahrány.' : 'Soubor byl úspěšně nahrán.',
      color: 'green',
    });
  } catch (error) {
    console.log(error);
    toast.add({
      title: 'Chyba',
      description: props.multiple
        ? 'Nepodařilo se nahrát jeden nebo více souborů. Zkuste to prosím později.'
        : 'Nepodařilo se nahrát soubor. Zkuste to prosím později.',
      color: 'red',
    });
  }
}
</script>

<template>
  <div class="w-full">
    <label class="block text-left text-xs font-medium text-grayCustom lg:text-sm/6">{{
      label
    }}</label>
    <span
      class="inline-flex w-full items-center rounded-md bg-blue-50 px-2 py-1 text-sm font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10"
    >
      {{ imageFormatMessage }}
    </span>
    <!-- Vstup pro nahrávání více souborů najednou -->
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
          <div class="relative col-span-full overflow-hidden rounded-md border border-gray-300">
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
        v-if="files && files.length"
        type="button"
        variant="primary"
        size="md"
        @click="uploadFiles"
        >{{ multiple ? 'Nahrát soubory' : 'Nahrát soubor' }}</BaseButton
      >
    </div>
  </div>
</template>
