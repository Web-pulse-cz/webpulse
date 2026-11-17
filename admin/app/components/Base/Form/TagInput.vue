<script setup lang="ts">
import { computed, ref } from 'vue';
import { XMarkIcon } from '@heroicons/vue/20/solid';

// Tag input: Enter / čárka / Tab přidá štítek, Backspace v prázdném poli smaže poslední.
const model = defineModel<string[]>({ default: () => [] });

const props = defineProps({
  name: { type: String, required: true },
  label: { type: String, required: true },
  placeholder: { type: String, default: 'Napište a stiskněte Enter' },
  maxLength: { type: Number, default: 100 },
  disabled: { type: Boolean, default: false },
});

const draft = ref('');
const inputRef = ref<HTMLInputElement | null>(null);

const tags = computed(() => model.value ?? []);

function addTag(raw: string) {
  const parts = raw
    .split(',')
    .map((part) => part.trim().slice(0, props.maxLength))
    .filter(Boolean);
  if (!parts.length) return;

  const next = [...tags.value];
  for (const part of parts) {
    if (!next.some((tag) => tag.toLowerCase() === part.toLowerCase())) next.push(part);
  }
  model.value = next;
  draft.value = '';
}

function removeTag(index: number) {
  model.value = tags.value.filter((_, i) => i !== index);
}

function onKeydown(event: KeyboardEvent) {
  if (event.key === 'Enter' || event.key === ',' || (event.key === 'Tab' && draft.value.trim())) {
    event.preventDefault();
    addTag(draft.value);
  } else if (event.key === 'Backspace' && !draft.value && tags.value.length) {
    removeTag(tags.value.length - 1);
  }
}

function onPaste(event: ClipboardEvent) {
  const text = event.clipboardData?.getData('text') ?? '';
  if (text.includes(',')) {
    event.preventDefault();
    addTag(draft.value + text);
  }
}
</script>

<template>
  <div>
    <label :for="name" class="mb-1.5 block text-sm font-medium text-slate-700">{{ label }}</label>
    <div
      class="flex min-h-[42px] w-full cursor-text flex-wrap items-center gap-1.5 rounded-xl bg-white px-2.5 py-1.5 shadow-sm ring-1 ring-inset ring-slate-300 transition-all duration-200 focus-within:ring-2 focus-within:ring-indigo-500 hover:ring-slate-400"
      :class="{ 'cursor-not-allowed bg-slate-50 opacity-70': disabled }"
      @click="inputRef?.focus()"
    >
      <span
        v-for="(tag, index) in tags"
        :key="tag + index"
        class="inline-flex items-center gap-1 rounded-md bg-indigo-50 py-0.5 pl-2 pr-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-200"
      >
        {{ tag }}
        <button
          v-if="!disabled"
          type="button"
          class="rounded p-0.5 text-indigo-400 hover:bg-indigo-100 hover:text-indigo-700"
          :aria-label="`Odebrat ${tag}`"
          @click.stop="removeTag(index)"
        >
          <XMarkIcon class="size-3" />
        </button>
      </span>
      <input
        :id="name"
        ref="inputRef"
        v-model="draft"
        :name="name"
        type="text"
        :disabled="disabled"
        :placeholder="tags.length ? '' : placeholder"
        class="min-w-[8rem] flex-1 border-0 bg-transparent p-1 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0"
        autocomplete="off"
        @keydown="onKeydown"
        @paste="onPaste"
        @blur="addTag(draft)"
      />
    </div>
  </div>
</template>
