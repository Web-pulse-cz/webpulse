<script setup lang="ts">
const model = defineModel({
  type: String,
  required: false,
});
const props = defineProps({
  rules: {
    type: String,
    required: false,
    default: '',
  },
  name: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
  disabled: {
    type: Boolean,
    required: false,
    default: false,
  },
  type: {
    type: String,
    required: false,
    default: 'text',
  },
  color: {
    type: String,
    required: false,
    default: 'primaryCustom',
  },
  checked: {
    type: Boolean,
    required: false,
    default: false,
  },
  labelColor: {
    type: String,
    required: false,
    default: 'gray-900',
  },
  reverse: {
    type: Boolean,
    required: false,
    default: false,
  },
});
const badgeClass = computed(() => (color: string) => {
  if (color === 'red') {
    return 'inline-flex items-center rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-800 ring-1 ring-inset ring-red-600/20';
  } else if (color === 'orange') {
    return 'inline-flex items-center rounded-full bg-orange-50 px-2 py-1 text-xs font-medium text-orange-800 ring-1 ring-inset ring-orange-600/20';
  } else if (color === 'amber') {
    return 'inline-flex items-center rounded-full bg-amber-50 px-2 py-1 text-xs font-medium text-amber-800 ring-1 ring-inset ring-amber-600/20';
  } else if (color === 'yellow') {
    return 'inline-flex items-center rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20';
  } else if (color === 'lime') {
    return 'inline-flex items-center rounded-full bg-lime-50 px-2 py-1 text-xs font-medium text-lime-800 ring-1 ring-inset ring-lime-600/20';
  } else if (color === 'green') {
    return 'inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-800 ring-1 ring-inset ring-green-600/20';
  } else if (color === 'emerald') {
    return 'inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-800 ring-1 ring-inset ring-emerald-600/20';
  } else if (color === 'teal') {
    return 'inline-flex items-center rounded-full bg-teal-50 px-2 py-1 text-xs font-medium text-teal-800 ring-1 ring-inset ring-teal-600/20';
  } else if (color === 'cyan') {
    return 'inline-flex items-center rounded-full bg-cyan-50 px-2 py-1 text-xs font-medium text-cyan-800 ring-1 ring-inset ring-cyan-600/20';
  } else if (color === 'sky') {
    return 'inline-flex items-center rounded-full bg-sky-50 px-2 py-1 text-xs font-medium text-sky-800 ring-1 ring-inset ring-sky-600/20';
  } else if (color === 'blue') {
    return 'inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-800 ring-1 ring-inset ring-blue-600/20';
  } else if (color === 'indigo') {
    return 'inline-flex items-center rounded-full bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-800 ring-1 ring-inset ring-indigo-600/20';
  } else if (color === 'violet') {
    return 'inline-flex items-center rounded-full bg-violet-50 px-2 py-1 text-xs font-medium text-violet-800 ring-1 ring-inset ring-violet-600/20';
  } else if (color === 'purple') {
    return 'inline-flex items-center rounded-full bg-purple-50 px-2 py-1 text-xs font-medium text-purple-800 ring-1 ring-inset ring-purple-600/20';
  } else if (color === 'fuchsia') {
    return 'inline-flex items-center rounded-full bg-fuchsia-50 px-2 py-1 text-xs font-medium text-fuchsia-800 ring-1 ring-inset ring-fuchsia-600/20';
  } else if (color === 'pink') {
    return 'inline-flex items-center rounded-full bg-pink-50 px-2 py-1 text-xs font-medium text-pink-800 ring-1 ring-inset ring-pink-600/20';
  } else if (color === 'rose') {
    return 'inline-flex items-center rounded-full bg-rose-50 px-2 py-1 text-xs font-medium text-rose-800 ring-1 ring-inset ring-rose-600/20';
  } else if (color === 'slate') {
    return 'inline-flex items-center rounded-full bg-slate-50 px-2 py-1 text-xs font-medium text-slate-800 ring-1 ring-inset ring-slate-600/20';
  } else if (color === 'gray') {
    return 'inline-flex items-center rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-800 ring-1 ring-inset ring-gray-600/20';
  } else if (color === 'zinc') {
    return 'inline-flex items-center rounded-full bg-zinc-50 px-2 py-1 text-xs font-medium text-zinc-800 ring-1 ring-inset ring-zinc-600/20';
  } else if (color === 'stone') {
    return 'inline-flex items-center rounded-full bg-stone-50 px-2 py-1 text-xs font-medium text-stone-800 ring-1 ring-inset ring-stone-600/20';
  } else {
    return 'inline-flex items-center rounded-full bg-neutral-50 px-2 py-1 text-xs font-medium text-neutral-800 ring-1 ring-inset ring-neutral-600/20';
  }
});
</script>

<template>
  <fieldset>
    <div class="space-y-5">
      <div
        :class="[
          reverse ? 'flex-row-reverse justify-between' : 'flex-row',
          'group flex cursor-pointer items-center gap-3',
        ]"
      >
        <div class="flex shrink-0 items-center">
          <div class="group/checkbox grid size-5 grid-cols-1 place-items-center">
            <input
              :id="name"
              v-model="model"
              :aria-describedby="name"
              :name="name"
              type="checkbox"
              :checked="checked"
              class="col-start-1 row-start-1 size-5 cursor-pointer appearance-none rounded-md border-2 border-slate-300 bg-white transition-all duration-200 ease-in-out checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-50 disabled:checked:bg-slate-200"
            />
            <svg
              class="pointer-events-none col-start-1 row-start-1 size-3.5 stroke-white group-has-[:disabled]/checkbox:stroke-slate-400"
              viewBox="0 0 14 14"
              fill="none"
            >
              <path
                class="opacity-0 transition-opacity duration-200 group-has-[:checked]/checkbox:opacity-100"
                d="M3 8L6 11L11 3.5"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                class="opacity-0 transition-opacity duration-200 group-has-[:indeterminate]/checkbox:opacity-100"
                d="M3 7H11"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
        </div>

        <div class="flex-1 text-sm/6">
          <label
            v-if="type === 'badge'"
            :for="name"
            :class="[badgeClass(color), 'cursor-pointer select-none']"
          >
            {{ label }}
          </label>
          <label
            v-else
            :for="name"
            :class="[
              'cursor-pointer select-none font-medium transition-colors duration-200',
              labelColor ? 'text-' + labelColor : 'text-slate-700 group-hover:text-slate-900',
            ]"
          >
            {{ label }}
          </label>
        </div>
      </div>
    </div>
  </fieldset>
</template>
