<script setup lang="ts">
import { ref } from 'vue';
import Draggable from 'vuedraggable';

const files = ref<{ file: File; name: string; preview?: string }[]>([]);
const acceptTypes = 'image/*,application/pdf,application/msword'; // Přijatelné typy

function handleFileChange(event: Event) {
  const input = event.target as HTMLInputElement;
  if (!input.files) return;

  for (const file of Array.from(input.files)) {
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
}

async function uploadFiles() {
  const formData = new FormData();
  files.value.forEach(({ file }) => {
    formData.append('images[]', file);
  });

  const client = useSanctumClient();
  formData.append('securityKey', 'your_security_key_here'); // Přidejte svůj bezpečnostní klíč
  formData.append('type', 'service')
  try {
    const response = await client<{}>('https://api.web-pulse.cz/api/filemanager/upload/images', {
      method: 'POST',
      body: formData,
    });
    formData.append({'securityKey': 'your_security_key_here'})

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
    <!-- Vstup pro nahrávání více souborů najednou -->
    <input
        type="file"
        class="block text-xs lg:text-sm/6 font-medium text-grayCustom text-left"
        multiple
        @change="handleFileChange"
        :accept="acceptTypes"
    />

    <!-- Galerie nahraných souborů -->
    <draggable
        v-if="files.length > 0"
        v-model="files"
        item-key="name"
        class="gallery"
    >
      <template #item="{ element, index }">
        <div>
          <img v-if="element.preview" :src="element.preview" alt="Náhled" class="text-primaryCustom" />
          <span class="text-xs text-primaryCustom">{{ element.name }}</span>
        </div>
      </template>
    </draggable>

    <!-- Tlačítko pro odeslání na API -->
    <BaseButton variant="primary" size="md" @click="uploadFiles" class="upload-btn">Nahrát soubory</BaseButton>
  </div>
</template>
