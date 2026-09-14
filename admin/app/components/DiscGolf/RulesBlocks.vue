<script setup lang="ts">
import RulesItemList from './RulesItemList.vue';

const { highlightText, renderRuleText } = useHighlight();

interface RuleItem {
  marker: string;
  text: string;
  children?: RuleItem[];
}

export interface RuleBlock {
  type: 'subheading' | 'paragraph' | 'list' | 'table';
  number?: string;
  title?: string;
  text?: string;
  items?: RuleItem[];
  rows?: string[][];
}

defineProps({
  blocks: {
    type: Array as () => RuleBlock[],
    required: true,
    default: () => [],
  },
  search: {
    type: String,
    required: false,
    default: '',
  },
});
</script>

<template>
  <div class="space-y-4">
    <template v-for="(block, idx) in blocks" :key="idx">
      <h4
        v-if="block.type === 'subheading'"
        class="flex items-baseline gap-2 pt-1 text-sm font-extrabold text-slate-900"
      >
        <span class="text-emerald-600">{{ block.number }}</span>
        <span v-html="highlightText(block.title || '', search)" />
      </h4>

      <p v-else-if="block.type === 'paragraph'" class="text-sm leading-relaxed text-slate-700">
        <span v-html="renderRuleText(block.text || '', search)" />
      </p>

      <RulesItemList
        v-else-if="block.type === 'list'"
        :items="block.items || []"
        :search="search"
      />

      <div
        v-else-if="block.type === 'table'"
        class="overflow-x-auto rounded-xl ring-1 ring-slate-200"
      >
        <table class="min-w-full divide-y divide-slate-200 text-sm">
          <thead class="bg-slate-50">
            <tr>
              <th
                v-for="(cell, cellIdx) in block.rows?.[0]"
                :key="cellIdx"
                class="px-4 py-2 text-left font-bold text-slate-700"
              >
                {{ cell }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(row, rowIdx) in block.rows?.slice(1)" :key="rowIdx">
              <td v-for="(cell, cellIdx) in row" :key="cellIdx" class="px-4 py-2 text-slate-700">
                {{ cell }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>
