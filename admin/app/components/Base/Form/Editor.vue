<script setup lang="ts">
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps({
  modelValue: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: false,
    default: 'Editor',
  },
  maxlength: {
    type: Number,
    required: false,
    default: 0,
  },
});
const emit = defineEmits(['update:modelValue']);

const toolbarOptions = [
  [{ header: [1, 2, 3, false] }],
  ['bold', 'italic', 'underline', 'strike'],
  [{ list: 'ordered' }, { list: 'bullet' }],
  ['code-block', 'blockquote'],
  ['color', 'background'],
  ['link', 'image', 'video'],
];
</script>

<template>
  <div class="w-full">
    <label class="mb-1.5 block text-sm font-medium text-slate-700">
      {{ label }}
    </label>

    <div
      class="group relative overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm transition-all duration-200 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500"
    >
      <ClientOnly>
        <QuillEditor
          v-model:content="props.modelValue"
          content-type="html"
          class="my-quill-editor"
          placeholder="Sem napište text..."
          :toolbar="toolbarOptions"
          @update:content="emit('update:modelValue', $event)"
        />
      </ClientOnly>
    </div>

    <div v-if="props.modelValue && maxlength > 0" class="mt-1.5 flex justify-end">
      <p class="text-xs font-medium text-slate-400">
        {{ props.modelValue.length }} / {{ maxlength }}
      </p>
    </div>
  </div>
</template>

<style scoped>
/* Vypnutí výchozích Quill rámečků a nastavení moderních barev */
:deep(.ql-toolbar.ql-snow) {
  border: none !important;
  border-bottom: 1px solid #e2e8f0 !important; /* slate-200 v Tailwindu */
  background-color: #f8fafc; /* slate-50 pro jemné odlišení lišty */
  font-family: inherit;
  padding: 0.75rem;
}

:deep(.ql-container.ql-snow) {
  border: none !important;
  font-family: inherit;
  font-size: 0.875rem; /* text-sm v Tailwindu */
  color: #0f172a; /* slate-900 */
}

/* Vzdušnější prostor pro samotný text */
:deep(.ql-editor) {
  min-height: 150px;
  padding: 1rem;
}

/* Sladění placeholderu s Tailwindem */
:deep(.ql-editor.ql-blank::before) {
  color: #94a3b8; /* text-slate-400 */
  font-style: normal;
}

/* Moderní vzhled pro nahrané obrázky v textu */
:deep(.ql-editor img) {
  max-width: 100%;
  resize: both;
  overflow: auto;
  display: block;
  border-radius: 0.5rem; /* zakulacení vložených obrázků */
  margin-top: 1rem;
  margin-bottom: 1rem;
}
</style>
