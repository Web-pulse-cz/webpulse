<script setup lang="ts">
defineProps({
  tabs: {
    type: Array,
    required: true,
    default: [] as [],
  },
});
</script>

<template>
  <div>
    <div class="block">
      <nav
        class="isolate flex divide-x divide-gray-200 border-t border-gray-200 shadow"
        aria-label="Tabs"
      >
        <NuxtLink
          v-for="(tab, index) in tabs"
          :key="index"
          :to="tab.link"
          :class="[
            tab.current ? 'bg-primaryCustom text-white' : 'bg-white text-grayCustom',
            'group relative min-w-0 flex-1 overflow-hidden px-2 py-2 text-center text-xs font-medium focus:z-10 lg:px-4 lg:py-4 lg:text-sm',
          ]"
        >
          <span>{{ tab.name }}</span>
          <span
            aria-hidden="true"
            :class="
              tab.current
                ? 'absolute inset-x-0 bottom-0 h-0.5 bg-primaryCustom'
                : 'absolute inset-x-0 bottom-0 h-0.5 bg-transparent'
            "
          />
        </NuxtLink>
      </nav>
    </div>
  </div>
</template>
