<script setup lang="ts">
import { computed, onMounted, onUnmounted } from 'vue';
import { useToast } from '~/composables/useToast';
import type { Toast } from '~/../types/toast';

const props = defineProps<{ toast: Toast }>();
const { remove, pause, resume, colorClasses } = useToast();

const cls = computed(() => colorClasses(props.toast.severity));

const progress = computed(() => {
  const total = props.toast.duration;
  const remaining = props.toast.remaining;
  return Math.max(0, Math.min(100, 100 - (remaining / total) * 100));
});

let raf: number | null = null;
function tick() {
  if (!props.toast._paused && props.toast._endAt) {
    props.toast.remaining = Math.max(0, props.toast._endAt - Date.now());
  }
  raf = requestAnimationFrame(tick);
}

onMounted(() => {
  raf = requestAnimationFrame(tick);
});
onUnmounted(() => {
  if (raf) cancelAnimationFrame(raf);
});

function onMouseEnter() {
  pause(props.toast.id);
}
function onMouseLeave() {
  resume(props.toast.id);
}
</script>

<template>
  <div
    class="pointer-events-auto w-9/12 overflow-hidden rounded-xl shadow-lg ring-1"
    :class="[cls.base, 'ring-1', cls.ring]"
    @mouseenter="onMouseEnter"
    @mouseleave="onMouseLeave"
  >
    <div class="p-3">
      <div class="flex items-start gap-3">
        <div class="min-w-0 flex-1">
          <p class="font-semibold leading-tight" :class="cls.text">{{ toast.summary }}</p>
          <p v-if="toast.detail" class="mt-0.5 text-sm opacity-90" :class="cls.text">
            {{ toast.detail }}
          </p>
        </div>
        <button
          class="rounded-md p-1 opacity-90 transition hover:opacity-100"
          :class="cls.text"
          aria-label="Close"
          @click="remove(toast.id)"
        >
          ✕
        </button>
      </div>
    </div>

    <!-- track -->
    <div class="h-1 w-full bg-black/20">
      <!-- progress -->
      <div class="h-1 bg-white/70" :style="{ width: progress + '%' }"></div>
    </div>
  </div>
</template>
