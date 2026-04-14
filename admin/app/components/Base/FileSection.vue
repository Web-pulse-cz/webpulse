<script setup lang="ts">
import { inject, ref } from 'vue';
import {
  FolderIcon,
  DocumentTextIcon,
  ArrowDownTrayIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const props = defineProps<{
  entityType: string;
  entityId: number | string | null;
  files: any[];
  title?: string;
  allowUpload?: boolean;
}>();

const emit = defineEmits<{
  (e: 'file-uploaded', files: any[]): void;
  (e: 'file-deleted', fileId: number): void;
}>();

const uploading = ref(false);

async function uploadFile(event: Event) {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (!file || !props.entityId) return;

  const client = useSanctumClient();
  const formData = new FormData();
  formData.append('file', file);

  uploading.value = true;
  await client('/api/admin/' + props.entityType + '/' + props.entityId + '/file', {
    method: 'POST',
    body: formData,
    headers: { 'X-Site-Hash': selectedSiteHash.value },
  })
    .then((r: any) => {
      $toast.show({ summary: 'Hotovo', detail: 'Soubor nahrán.', severity: 'success' });
      emit('file-uploaded', r.files || []);
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se nahrát soubor.', severity: 'error' });
    })
    .finally(() => {
      uploading.value = false;
      target.value = '';
    });
}

async function downloadFile(file: any) {
  const client = useSanctumClient();
  try {
    const res = await client.raw(
      '/api/admin/' + props.entityType + '/' + props.entityId + '/file/' + file.id,
      {
        method: 'GET',
        credentials: 'include',
        responseType: 'blob',
      },
    );
    if (!res.ok) throw new Error('Chyba');
    const blob = res._data as Blob;
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = file.name || 'soubor-' + file.id;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  } catch (e) {
    $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se stáhnout soubor.', severity: 'error' });
  }
}

async function deleteFile(file: any) {
  const client = useSanctumClient();
  await client('/api/admin/' + props.entityType + '/' + props.entityId + '/file/' + file.id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'X-Site-Hash': selectedSiteHash.value },
  })
    .then(() => {
      emit('file-deleted', file.id);
      $toast.show({ summary: 'Hotovo', detail: 'Soubor smazán.', severity: 'success' });
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se smazat soubor.', severity: 'error' });
    });
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div
          class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
        >
          <FolderIcon class="size-5" />
        </div>
        <LayoutTitle class="!mb-0">{{ title || 'Soubory' }}</LayoutTitle>
      </div>
      <div class="flex items-center gap-3">
        <label
          v-if="allowUpload !== false && entityId"
          class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-600"
        >
          <ArrowDownTrayIcon class="size-5 rotate-180" />
          {{ uploading ? 'Nahrávám...' : 'Nahrát soubor' }}
          <input
            type="file"
            class="hidden"
            accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
            :disabled="uploading"
            @change="uploadFile"
          />
        </label>
        <slot name="actions" />
      </div>
    </div>

    <div v-if="files.length" class="space-y-3">
      <div
        v-for="file in files"
        :key="file.id"
        class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
      >
        <div class="flex items-center gap-4">
          <div
            class="flex size-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
          >
            <DocumentTextIcon class="size-5" />
          </div>
          <div>
            <p class="text-sm font-medium text-slate-900">{{ file.name }}</p>
            <p class="text-xs text-slate-400">
              {{ file.mime_type }}
              <span v-if="file.size" class="ml-2">{{ (file.size / 1024).toFixed(0) }} KB</span>
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button
            type="button"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-medium text-white transition hover:bg-indigo-500"
            @click="downloadFile(file)"
          >
            Stáhnout
          </button>
          <button
            type="button"
            class="rounded-lg bg-red-50 p-2 text-red-600 transition hover:bg-red-100"
            @click="deleteFile(file)"
          >
            <TrashIcon class="size-4" />
          </button>
        </div>
      </div>
    </div>

    <slot name="extra" />

    <div v-if="!files.length && !$slots.extra" class="py-12 text-center text-sm text-slate-400">
      Žádné soubory.
    </div>
  </div>
</template>
