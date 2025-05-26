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
});
const emit = defineEmits(['update:modelValue']);
</script>

<template>
  <div>
    <label class="block text-left text-xs font-medium text-grayCustom lg:text-sm/6">{{
      label
    }}</label>
    <ClientOnly>
      <QuillEditor
        v-model:content="props.modelValue"
        content-type="html"
        class="my-quill-editor"
        @update:content="emit('update:modelValue', $event)"
      />
    </ClientOnly>
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
</style>
