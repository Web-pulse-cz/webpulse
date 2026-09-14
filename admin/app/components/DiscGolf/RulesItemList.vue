<script setup lang="ts">
export interface RuleItem {
  marker: string;
  text: string;
  children?: RuleItem[];
}

const { renderRuleText } = useHighlight();

defineProps({
  items: {
    type: Array as () => RuleItem[],
    required: true,
    default: () => [],
  },
  depth: {
    type: Number,
    required: false,
    default: 0,
  },
  search: {
    type: String,
    required: false,
    default: '',
  },
});
</script>

<template>
  <ul :class="depth === 0 ? 'space-y-2' : 'mt-2 space-y-2 border-l border-slate-100 pl-4'">
    <li v-for="(item, idx) in items" :key="idx" class="flex gap-2 text-sm leading-relaxed">
      <span
        class="mt-0.5 min-w-[1.75rem] shrink-0 font-bold tabular-nums"
        :class="depth === 0 ? 'text-emerald-600' : 'text-slate-400'"
      >
        {{ item.marker }}
      </span>
      <div class="min-w-0 flex-1 text-slate-700">
        <span v-html="renderRuleText(item.text, search)" />
        <RulesItemList
          v-if="item.children && item.children.length"
          :items="item.children"
          :depth="depth + 1"
          :search="search"
        />
      </div>
    </li>
  </ul>
</template>
