<script setup lang="ts">
import { ref, watch } from 'vue';
import Draggable from 'vuedraggable';
import { TrashIcon, PlusIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';
import useImageFormatMessage from '~/composables/useImageFormatMessage';

const { $toast } = useNuxtApp();
const manualUploaded = ref(false);

const props = defineProps({
  fileType: { type: String, default: 'image', required: false },
  viewType: { type: String, default: 'single', required: false },
  label: { type: String, default: 'Nahrát soubory', required: false },
  format: { type: String, default: 'service', required: true },
  type: { type: String, default: 'icon', required: false },
  multiple: { type: Boolean, default: false },
  modelValue: { type: String, default: '', required: false },
  allowRemoteUrl: { type: Boolean, default: false, required: false },
});

const emit = defineEmits(['update-files', 'remove-file']);
const isUploadDialogVisible = ref(false);

const imageFormatMessage = await useImageFormatMessage(
  props.fileType,
  props.multiple,
  props.type,
  props.format,
);

// Inicializace souborů z v-model
const files = ref<{ file: File | null; name: string; preview?: string }[]>([]);

watch(
  () => props.modelValue,
  () => {
    if (!props.modelValue) {
      files.value = [];
      return;
    }
    files.value = [
      {
        file: null,
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
  manualUploaded.value = true;
}

function removeFile(index: number) {
  files.value.splice(index, 1);
  emit('remove-file');
}

/**
 * Nahrání souborů z inputu na server
 */
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
    });

    // po úspěchu vyčisti lokální frontu
    files.value = [];
    manualUploaded.value = false;
  } catch (error) {
    console.error(error);
    $toast.show({
      summary: 'Chyba',
      detail: props.multiple
        ? 'Nepodařilo se nahrát jeden nebo více souborů. Zkus to později.'
        : 'Nepodařilo se nahrát soubor. Zkus to později.',
      severity: 'error',
    });
  }
  isUploadDialogVisible.value = false;
}

/**
 * Nahrání z URL na server
 * Počítá se s endpointem, který soubor stáhne a uloží na serveru
 * Odpověď pak předáme rodiči skrze 'update-files'
 */
const remoteUrl = ref('');
const remoteLoading = ref(false);

const REMOTE_UPLOAD_ENDPOINT = '/api/filemanager/upload/images';

function isValidUrl(url: string) {
  try {
    const u = new URL(url);
    return !!u.protocol && !!u.host;
  } catch {
    return false;
  }
}

async function uploadFromRemoteUrl() {
  if (!remoteUrl.value || !isValidUrl(remoteUrl.value)) {
    $toast.show({
      summary: 'Chyba',
      detail: 'Zadej platnou URL.',
      severity: 'error',
    });
    return;
  }

  remoteLoading.value = true;

  try {
    const client = useSanctumClient();

    const payload = {
      url: remoteUrl.value,
      type: props.type,
      fileType: props.fileType,
      multiple: props.multiple,
      securityKey: 'your_security_key_here',
    };

    const response = await client(REMOTE_UPLOAD_ENDPOINT, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    });

    // očekáváme stejnou nebo podobnou strukturu, jako vrací upload obrázků
    const result = await response;

    // Notifikace a emit na rodiče
    $toast.show({
      summary: 'Hotovo',
      detail: 'Soubor byl úspěšně nahrán ze vzdálené adresy.',
      severity: 'success',
    });

    emit('update-files', result);

    // volitelně přidáme vizuální položku do seznamu pro náhled
    // pokud server vrací např. result.filename nebo result.path
    // níže jsou dvě nejčastější varianty, nech si tu, která odpovídá tvému API

    // varianta A: server vrátí jeden soubor
    if (!props.multiple && (result?.filename || result?.path)) {
      const displayName = result.filename || result.path.split('/').pop();
      const displayPreview =
        result.preview ||
        result.path ||
        (result.filename
          ? `/content/images/${props.type}/${props.format}/${result.filename}`
          : undefined);

      files.value = [
        {
          file: null,
          name: displayName || 'remote-file',
          preview: props.fileType === 'image' ? displayPreview : undefined,
        },
      ];
    }

    // varianta B: server vrátí pole souborů
    if (props.multiple && Array.isArray(result)) {
      files.value = result.map((item: any) => {
        const displayName = item.filename || item.path?.split('/').pop() || 'remote-file';
        const displayPreview =
          item.preview ||
          item.path ||
          (item.filename
            ? `/content/images/${props.type}/${props.format}/${item.filename}`
            : undefined);
        return {
          file: null,
          name: displayName,
          preview: props.fileType === 'image' ? displayPreview : undefined,
        };
      });
    }

    // vyčisti URL input
    remoteUrl.value = '';
  } catch (err) {
    console.error(err);
    $toast.show({
      summary: 'Chyba',
      detail: 'Nepodařilo se nahrát soubor z URL. Zkus to znovu nebo ověř, že URL je dostupná.',
      severity: 'error',
    });
  } finally {
    remoteLoading.value = false;
    isUploadDialogVisible.value = false;
  }
}
</script>

<template>
  <div class="col-span-full w-full">
    <div
      :class="[
        multiple ? 'grid-cols-4' : 'grid-cols-1',
        'mt-4 grid w-full gap-4 rounded border-2 border-dashed border-gray-300 bg-gray-50 p-4',
      ]"
    >
      <div
        v-if="files.length === 0"
        class="flex cursor-pointer flex-col items-center gap-y-2 col-span-1"
        @click="isUploadDialogVisible = true"
      >
        <PlusIcon class="h-4 w-4 md:h-8 md:w-8 text-gray-600" />
        <p class="text-gray-600 text-xs md:text-base">Nahrát {{ multiple ? 'obrázky' : 'obrázek' }}</p>
      </div>
      <draggable v-model="files" item-key="name" style="display: contents" class="cursor-grab">
        <template #item="{ element, index }">
          <div
            :class="[
              !multiple ? 'col-span-1' : 'w-1/2',
              'relative overflow-hidden',
            ]"
          >
            <div class="absolute left-4 top-4 flex gap-x-2">
              <div
                class="inline-flex h-6 w-6 cursor-pointer items-center justify-center rounded-full bg-dangerLight ring-1 ring-danger"
                @click="removeFile(index)"
              >
                <TrashIcon class="h-3 w-3 text-white" />
              </div>
              <div
                class="inline-flex h-6 w-6 cursor-pointer items-center justify-center rounded-full bg-warningLight ring-1 ring-warning"
                @click="isUploadDialogVisible = true"
              >
                <ArrowPathIcon class="h-3 w-3 text-white" />
              </div>
            </div>

            <img
              v-if="element.preview"
              :src="element.preview"
              alt="náhled"
              class="max-h-72 w-auto object-cover"
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

    <BaseFormUploadDialog
      v-model:show="isUploadDialogVisible"
      v-model:remote-url="remoteUrl"
      v-model:files="files"
      v-model:manual-uploaded="manualUploaded"
      :message="imageFormatMessage"
      :multiple="multiple"
      :accept-types="acceptTypes"
      :allow-remote-url="true"
      :remote-loading="remoteLoading"
      @upload-remote-url="uploadFromRemoteUrl"
      @handle-file-change="handleFileChange"
      @upload-files="uploadFiles"
    />
  </div>
</template>
