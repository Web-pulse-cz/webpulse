<script setup lang="ts">
import { QuillEditor } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";

const props = defineProps({
  modelValue: {
    type: String,
    required: true,
  },
});
const emit = defineEmits(["update:modelValue"]);
</script>

<template>
  <div>
    <ClientOnly>
      <QuillEditor
        v-model:content="props.modelValue"
        @update:content="emit('update:modelValue', $event)"
        contentType="html"
        class="my-quill-editor"
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
