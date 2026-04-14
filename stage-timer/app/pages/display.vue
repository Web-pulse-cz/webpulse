<template>
  <ClientOnly>
    <div
        class="h-screen w-screen flex items-center justify-center relative overflow-hidden transition-colors duration-1000 select-none"
        :class="[
        isChillOut ? 'bg-slate-950' : 'bg-black',
        isFullscreen ? 'cursor-none' : 'cursor-auto'
      ]"
        @click="enterFullscreen"
    >

      <div v-if="!isFullscreen" class="absolute top-6 bg-white/20 px-6 py-3 rounded-xl font-sans text-xl text-white animate-pulse pointer-events-none">
        Klikněte kamkoliv pro režim celé obrazovky
      </div>

      <div v-if="!isChillOut"
           class="font-mono font-black leading-none transition-colors duration-500 -translate-y-[2vh]"
           :style="{ fontSize: '35vw' }"
           :class="[
             timeLeft <= 0 ? 'text-red-600 animate-pulse' :
             (timeLeft <= 60 ? 'text-red-500' : 'text-white')
           ]"
      >
        {{ formatTime(Math.max(0, timeLeft)) }}
      </div>

      <div v-else class="text-center animate-bounce-slow">
        <h2 class="text-[15vw] m-0 text-indigo-400 tracking-widest font-black">CHILL OUT</h2>
        <p class="text-[4vw] text-indigo-500 mt-6 uppercase tracking-[0.2em]">...and no stress</p>
      </div>

      <div v-if="!isChillOut && totalTime > 0" class="absolute bottom-0 left-0 w-full h-[2vh] bg-neutral-900">
        <div
            class="h-full transition-all duration-1000 ease-linear"
            :class="timeLeft <= 60 ? 'bg-red-500' : 'bg-blue-500'"
            :style="{ width: progressPercentage + '%' }"
        ></div>
      </div>

      <div
          v-if="isFlashVisible && flashMessage"
          class="absolute inset-x-0 bottom-[15%] bg-yellow-500 text-black py-8 px-4 flex items-center justify-center shadow-[0_0_100px_rgba(234,179,8,0.5)] z-50 animate-slide-up"
      >
        <span class="text-[8vw] font-black uppercase tracking-wider text-center leading-tight">
          {{ flashMessage }}
        </span>
      </div>

    </div>
  </ClientOnly>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

const { timeLeft, totalTime, isChillOut, flashMessage, isFlashVisible } = useStageTimer(false)
const isFullscreen = ref(false)

const progressPercentage = computed(() => {
  if (totalTime.value <= 0) return 0
  // Aby progress bar necouval do mínusu
  return Math.max(0, (timeLeft.value / totalTime.value) * 100)
})

const enterFullscreen = async () => {
  if (!document.fullscreenElement) {
    try { await document.documentElement.requestFullscreen() }
    catch (err) { console.warn("Fullscreen error:", err) }
  }
}

const handleFullscreenChange = () => isFullscreen.value = !!document.fullscreenElement

onMounted(() => {
  if (import.meta.client) document.addEventListener('fullscreenchange', handleFullscreenChange)
})

onUnmounted(() => {
  if (import.meta.client) document.removeEventListener('fullscreenchange', handleFullscreenChange)
})

definePageMeta({ layout: false })
</script>

<style scoped>
.animate-bounce-slow {
  animation: float 4s ease-in-out infinite;
}
@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
  100% { transform: translateY(0px); }
}

.animate-slide-up {
  animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes slideUp {
  from { transform: translateY(100%); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>