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
    class="pointer-events-auto relative w-full max-w-sm overflow-hidden rounded-2xl shadow-2xl shadow-slate-200/60 ring-1 transition-all duration-300 sm:w-96"
    :class="[cls.base, cls.ring]"
    role="alert"
    @mouseenter="onMouseEnter"
    @mouseleave="onMouseLeave"
  >
    <div class="p-4 sm:p-5">
      <div class="flex items-start gap-4">
        <div class="min-w-0 flex-1">
          <p class="text-sm font-bold tracking-tight sm:text-base" :class="cls.text">
            {{ toast.summary }}
          </p>
          <p
            v-if="toast.detail"
            class="mt-1 text-xs leading-relaxed opacity-80 sm:text-sm"
            :class="cls.text"
          >
            {{ toast.detail }}
          </p>
        </div>

        <button
          type="button"
          class="shrink-0 rounded-lg p-1.5 transition-colors hover:bg-black/10 focus:outline-none"
          :class="cls.text"
          aria-label="Zavřít"
          @click="remove(toast.id)"
        >
          <svg class="size-4" viewBox="0 0 20 20" fill="currentColor">
            <path
              d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"
            />
          </svg>
        </button>
      </div>
    </div>

    <div class="absolute bottom-0 left-0 h-1 w-full bg-black/10">
      <div
        class="h-full bg-white/40 transition-all duration-100 ease-linear"
        :style="{ width: progress + '%' }"
      ></div>
    </div>
  </div>
</template>
