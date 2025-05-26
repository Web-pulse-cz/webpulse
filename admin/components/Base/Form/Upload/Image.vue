<script setup lang="ts">
import { ref } from 'vue';
import Draggable from 'vuedraggable';
import useImageFormatMessage from '~/composables/useImageFormatMessage';

const props = defineProps({
  fileType: {
    type: String,
    default: 'image',
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
const imageFormatMessage = await useImageFormatMessage(props.multiple, props.type, props.format);

const files = ref<{ file: File; name: string; preview?: string }[]>([]);
const acceptTypes = 'image/*,application/pdf,application/msword'; // Přijatelné typy

function handleFileChange(event: Event) {
  const input = event.target as HTMLInputElement;
  if (!input.files) return;

  const filesArray = Array.from(input.files);
  if(props.multiple) {
  for (const file of filesArray) {
    const reader = new FileReader();
    const fileItem = { file, name: file.name, preview: '' };
    if (file.type.startsWith('image/')) {
      reader.onload = (e) => {
        fileItem.preview = e.target?.result as string;
      };
      reader.readAsDataURL(file);
    }

    files.value.push(fileItem);
  }
  } else {
    // Pokud není povoleno více souborů, přidejte pouze jeden soubor
    const file = filesArray[0];
    if (file) {
      const reader = new FileReader();
      const fileItem = { file, name: file.name, preview: '' };

      if (file.type.startsWith('image/')) {
        reader.onload = (e) => {
          fileItem.preview = e.target?.result as string;
        };
        reader.readAsDataURL(file);
      }

      files.value = [fileItem]; // Přepište pole s jedním souborem
    }
  }
}

async function uploadFiles() {
  const formData = new FormData();
  files.value.forEach(({ file }) => {
    formData.append('images[]', file);
  });

  const client = useSanctumClient();
  formData.append('securityKey', 'your_security_key_here'); // Přidejte svůj bezpečnostní klíč
  formData.append('type', 'service');
  try {
    const response = await client<{}>('https://api.web-pulse.cz/api/filemanager/upload/images', {
      method: 'POST',
      body: formData,
    });
    formData.append({ securityKey: 'your_security_key_here' });

    if (!response.ok) throw new Error('Nahrávání selhalo');
    const result = await response.json();
    console.log('Nahrané soubory:', result); // Vrácené pole názvů souborů
    alert('Soubor(y) úspěšně nahrány!');
  } catch (error) {
    console.error('Chyba při nahrávání:', error);
    alert('Chyba při nahrávání souborů.');
  }
}
</script>

<template>
  <div>
    <label class="block text-left text-xs font-medium text-grayCustom lg:text-sm/6">{{
      label
    }}</label>
    <span
      class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10"
    >
      {{ imageFormatMessage }}
    </span>
    <!-- Vstup pro nahrávání více souborů najednou -->
    <input
      type="file"
      class="block text-left text-xs font-medium text-grayCustom lg:text-sm/6"
      :multiple="multiple"
      :accept="acceptTypes"
      @change="handleFileChange"
    />

    <!-- Galerie nahraných souborů -->
    <draggable v-if="files.length > 0" v-model="files" item-key="name" class="gallery">
      <template #item="{ element, index }">
        <div>
          <img
            v-if="element.preview"
            :src="element.preview"
            alt="Náhled"
            class="text-primaryCustom"
          />
        </div>
      </template>
    </draggable>

    <!-- Tlačítko pro odeslání na API -->
    <BaseButton variant="primary" size="md" class="upload-btn" @click="uploadFiles"
      >Nahrát soubory</BaseButton
    >
  </div>
</template>
