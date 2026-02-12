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
  <div>
    <label class="mb-2 block text-left text-xs font-medium text-grayCustom lg:text-sm/6">
      {{ label }}
    </label>
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
    <p v-if="props.modelValue && maxlength > 0" class="pt-1 text-end text-xs text-grayLight">
      {{ props.modelValue.length }} / {{ maxlength }}
    </p>
  </div>
</template>

<style scoped>
.my-quill-editor {
  margin: 1rem auto;
  border: 1px solid #ccc;
  border-radius: 8px;
  background-color: #fff;
  color: #000;
}
.ql-editor {
  min-height: 150px;
}

.ql-editor img {
  max-width: 100%;
  resize: both;
  overflow: auto;
  display: block;
}
</style>
