<script setup lang="ts">
defineProps({
  color: {
    type: String,
    required: false,
    default: 'unknown',
  },
});

const baseClass =
  'inline-flex items-center rounded-full px-2.5 py-1.5 text-xs font-medium ring-1 ring-inset';

const badgeClass = computed(() => (color: string) => {
  if (color === 'red') {
    return 'bg-red-50 text-red-800 ring-red-600/20';
  } else if (color === 'orange') {
    return 'bg-orange-50 text-orange-800 ring-orange-600/20';
  } else if (color === 'amber') {
    return 'bg-amber-50 text-amber-800 ring-amber-600/20';
  } else if (color === 'yellow') {
    return 'bg-yellow-50 text-yellow-800 ring-yellow-600/20';
  } else if (color === 'lime') {
    return 'bg-lime-50 text-lime-800 ring-lime-600/20';
  } else if (color === 'green') {
    return 'bg-green-50 text-green-800 ring-green-600/20';
  } else if (color === 'emerald') {
    return 'bg-emerald-50 text-emerald-800 ring-emerald-600/20';
  } else if (color === 'teal') {
    return 'bg-teal-50 text-teal-800 ring-teal-600/20';
  } else if (color === 'cyan') {
    return 'bg-cyan-50 text-cyan-800 ring-cyan-600/20';
  } else if (color === 'sky') {
    return 'bg-sky-50 text-sky-800 ring-sky-600/20';
  } else if (color === 'blue') {
    return 'bg-blue-50 text-blue-800 ring-blue-600/20';
  } else if (color === 'indigo') {
    return 'bg-indigo-50 text-indigo-800 ring-indigo-600/20';
  } else if (color === 'violet') {
    return 'bg-violet-50 text-violet-800 ring-violet-600/20';
  } else if (color === 'purple') {
    return 'bg-purple-50 text-purple-800 ring-purple-600/20';
  } else if (color === 'fuchsia') {
    return 'bg-fuchsia-50 text-fuchsia-800 ring-fuchsia-600/20';
  } else if (color === 'pink') {
    return 'bg-pink-50 text-pink-800 ring-pink-600/20';
  } else if (color === 'rose') {
    return 'bg-rose-50 text-rose-800 ring-rose-600/20';
  } else if (color === 'slate') {
    return 'bg-slate-50 text-slate-800 ring-slate-600/20';
  } else if (color === 'gray') {
    return 'bg-gray-50 text-gray-800 ring-gray-600/20';
  } else if (color === 'zinc') {
    return 'bg-zinc-50 text-zinc-800 ring-zinc-600/20';
  } else if (color === 'stone') {
    return 'bg-stone-50 text-stone-800 ring-stone-600/20';
  } else {
    return 'bg-brand/20 text-neutral-800 ring-brand/20';
  }
});
</script>

<template>
  <span :class="baseClass + ' ' + badgeClass(color)"><slot /></span>
</template>
