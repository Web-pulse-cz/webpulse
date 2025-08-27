<script setup lang="ts">
defineProps<{
  card: {
    id: number;
    name: string;
    description: string;
    image: string;
    price: number;
    attack: number;
    health: number;
    mana: number;
    type: string;
  };
}>();
</script>

<template>
  <div
    class="font-winky relative h-64 w-48 overflow-hidden rounded-lg border-4 border-black bg-white shadow-lg"
  >
    <slot />
  </div>
</template>
