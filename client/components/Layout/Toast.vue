<template>
  <div class="fixed right-5 top-5 z-50 space-y-2">
    <transition-group name="toast" tag="div">
      <div
        v-for="(toast, index) in toasts"
        :key="toast.id"
        class="rounded bg-gray-800 px-4 py-2 text-white shadow"
      >
        {{ toast.message }}
      </div>
    </transition-group>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

const toasts = ref<{ id: number; message: string }[]>([]);

function addToast(message: string) {
  const id = Date.now();
  toasts.value.push({ id, message });
  setTimeout(() => {
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
  }, 3000);
}

function onToastEvent(event: CustomEvent) {
  addToast(event.detail);
}

onMounted(() => {
  window.addEventListener('add-toast', onToastEvent);
});

onUnmounted(() => {
  window.removeEventListener('add-toast', onToastEvent);
});
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
