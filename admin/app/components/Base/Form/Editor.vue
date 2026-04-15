<script setup lang="ts">
import { QuillEditor } from '@vueup/vue-quill';
import { useField } from 'vee-validate';
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
  name: {
    type: String,
    required: false,
    default: '',
  },
  rules: {
    type: String,
    required: false,
    default: '',
  },
  maxlength: {
    type: Number,
    required: false,
    default: 0,
  },
});
const emit = defineEmits(['update:modelValue']);

const fieldName = computed(() => props.name || props.label);

const { errorMessage, handleChange, value } = props.rules
  ? useField(fieldName, props.rules, {
      initialValue: props.modelValue,
      syncVModel: false,
    })
  : { errorMessage: ref(''), handleChange: () => {}, value: ref(props.modelValue) };

function onContentUpdate(content: string) {
  emit('update:modelValue', content);
  if (props.rules) {
    handleChange(content);
  }
}

watch(
  () => props.modelValue,
  (val) => {
    if (props.rules && value.value !== val) {
      handleChange(val);
    }
  },
);

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
      <span v-if="rules && rules.includes('required')" class="ml-1 text-red-500">*</span>
    </label>

    <div
      :class="[
        errorMessage
          ? 'border-red-400 focus-within:border-red-500 focus-within:ring-red-500'
          : 'border-slate-300 focus-within:border-indigo-500 focus-within:ring-indigo-500',
        'group relative overflow-hidden rounded-xl border bg-white shadow-sm transition-all duration-200 focus-within:ring-2',
      ]"
    >
      <ClientOnly>
        <QuillEditor
          v-model:content="props.modelValue"
          content-type="html"
          class="my-quill-editor"
          placeholder="Sem napište text..."
          :toolbar="toolbarOptions"
          @update:content="onContentUpdate"
        />
      </ClientOnly>
    </div>

    <p v-if="errorMessage" class="mt-1.5 text-xs font-medium text-red-500">
      {{ errorMessage }}
    </p>

    <div v-if="props.modelValue && maxlength > 0" class="mt-1.5 flex justify-end">
      <p class="text-xs font-medium text-slate-400">
        {{ props.modelValue.length }} / {{ maxlength }}
      </p>
    </div>
  </div>
</template>

<style scoped>
:deep(.ql-toolbar.ql-snow) {
  border: none !important;
  border-bottom: 1px solid #e2e8f0 !important;
  background-color: #f8fafc;
  font-family: inherit;
  padding: 0.75rem;
}

:deep(.ql-container.ql-snow) {
  border: none !important;
  font-family: inherit;
  font-size: 0.875rem;
  color: #0f172a;
}

:deep(.ql-editor) {
  min-height: 150px;
  padding: 1rem;
}

:deep(.ql-editor.ql-blank::before) {
  color: #94a3b8;
  font-style: normal;
}

:deep(.ql-editor img) {
  max-width: 100%;
  resize: both;
  overflow: auto;
  display: block;
  border-radius: 0.5rem;
  margin-top: 1rem;
  margin-bottom: 1rem;
}
</style>
