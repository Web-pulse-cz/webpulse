<script setup lang="ts">
const props = defineProps({
  name: {
    type: Array,
    required: true,
    default: () => [],
  },

  links: {
    type: Array,
    required: true,
    default: () => [],
  },
});
</script>

<template>
  <div class="grid grid-cols-2 gap-1">
    <NuxtLink v-for="i in 4" :key="'left-' + i" :to="props.links[i - 1]">
      <div class="flex flex-col gap-1">
        <BaseButton variant="reverse" size="long">
          {{ props.name[i - 1] }}
        </BaseButton>
      </div>
    </NuxtLink>

    <NuxtLink v-for="i in 4" :key="'right-' + i" :to="props.links[i + 3]">
      <div class="flex flex-col gap-1">
        <BaseButton variant="reverse" size="long">
          {{ props.name[i + 3] }}
        </BaseButton>
      </div>
    </NuxtLink>
  </div>
</template>

<style scoped></style>
