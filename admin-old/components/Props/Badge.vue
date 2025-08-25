<script setup lang="ts">
defineProps({
  color: {
    type: String,
    required: false,
    default: 'gray',
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
  <span :class="badgeClass(color)"><slot /></span>
</template>
