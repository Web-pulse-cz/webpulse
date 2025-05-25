<script setup lang="ts">
import { QuillEditor, Quill } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";

const model = defineModel({
  type: String,
  default: "<p>Začni psát...</p>",
  required: true,
});
</script>

<template>
  <ClientOnly>
    <QuillEditor v-model="model" theme="snow" class="text-grayDark" />
  </ClientOnly>
</template>
