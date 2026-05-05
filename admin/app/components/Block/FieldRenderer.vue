<script setup lang="ts">
const props = defineProps<{
  fields: Array<{
    name: string;
    type: string;
    label: string;
    translatable: boolean;
    rules?: string;
    multiple?: boolean;
    options?: Array<{ value: string | number; label: string }>;
  }>;
  modelValue: Record<string, any>;
  translatable: boolean;
  selectedLocale?: string;
  imageFormat?: string;
  imageType?: string;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', v: Record<string, any>): void;
}>();

function setField(name: string, value: any) {
  const next = { ...(props.modelValue || {}), [name]: value };
  emit('update:modelValue', next);
}

function normalizeUpload(result: unknown, multiple: boolean, current: unknown) {
  const incoming = Array.isArray(result) ? (result as string[]).filter(Boolean) : result ? [result as string] : [];
  if (!multiple) {
    return incoming[0] ?? '';
  }
  const existing = Array.isArray(current) ? (current as string[]) : [];
  return [...existing, ...incoming];
}

const visibleFields = computed(() =>
  props.fields.filter((f) => f.translatable === props.translatable),
);

const fieldKey = (name: string) =>
  props.translatable ? `${name}-${props.selectedLocale}` : `${name}-shared`;
</script>

<template>
  <div v-if="visibleFields.length" class="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-2">
    <template v-for="field in visibleFields" :key="field.name">
      <BaseFormInput
        v-if="field.type === 'text' || field.type === 'link'"
        :key="fieldKey(field.name)"
        :model-value="modelValue?.[field.name] ?? ''"
        :label="field.label"
        type="text"
        :name="field.name"
        :rules="field.rules"
        class="col-span-full"
        @update:model-value="(v) => setField(field.name, v)"
      />

      <BaseFormInput
        v-else-if="field.type === 'number'"
        :key="fieldKey(field.name)"
        :model-value="modelValue?.[field.name] ?? ''"
        :label="field.label"
        type="number"
        :name="field.name"
        :rules="field.rules"
        class="col-span-full"
        @update:model-value="(v) => setField(field.name, v)"
      />

      <BaseFormTextarea
        v-else-if="field.type === 'textarea'"
        :key="fieldKey(field.name)"
        :model-value="modelValue?.[field.name] ?? ''"
        :label="field.label"
        :name="field.name"
        :rules="field.rules"
        rows="3"
        class="col-span-full"
        @update:model-value="(v) => setField(field.name, v)"
      />

      <BaseFormEditor
        v-else-if="field.type === 'richtext'"
        :key="fieldKey(field.name)"
        :model-value="modelValue?.[field.name] ?? ''"
        :label="field.label"
        :name="field.name"
        class="col-span-full"
        @update:model-value="(v) => setField(field.name, v)"
      />

      <BaseFormSwitch
        v-else-if="field.type === 'boolean'"
        :key="fieldKey(field.name)"
        :model-value="!!modelValue?.[field.name]"
        :label="field.label"
        :name="field.name"
        class="col-span-full"
        @update:model-value="(v) => setField(field.name, v)"
      />

      <BaseFormUploadImage
        v-else-if="field.type === 'image'"
        :key="fieldKey(field.name)"
        :model-value="modelValue?.[field.name] ?? (field.multiple ? [] : '')"
        :label="field.label"
        :format="imageFormat || 'large'"
        :type="imageType || 'block'"
        :multiple="!!field.multiple"
        class="col-span-full"
        @update-files="(result) => setField(field.name, normalizeUpload(result, !!field.multiple, modelValue?.[field.name]))"
        @remove-file="(remaining) => setField(field.name, field.multiple ? remaining || [] : '')"
      />

      <BaseFormInput
        v-else
        :key="fieldKey(field.name)"
        :model-value="modelValue?.[field.name] ?? ''"
        :label="field.label + ' (neznámý typ: ' + field.type + ')'"
        type="text"
        :name="field.name"
        class="col-span-full"
        @update:model-value="(v) => setField(field.name, v)"
      />
    </template>
  </div>

  <div
    v-else
    class="rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-400"
  >
    {{
      translatable
        ? 'Tento typ bloku nemá žádná překládatelná pole.'
        : 'Tento typ bloku nemá žádná sdílená pole.'
    }}
  </div>
</template>
