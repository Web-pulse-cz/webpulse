<template>
  <ClientOnly>
    <div
        class="stage-display"
        :class="{ 'chill-mode': isChillOut, 'hide-cursor': isFullscreen }"
        @click="enterFullscreen"
    >

      <div v-if="!isFullscreen" class="fullscreen-prompt">
        Klikněte kamkoliv pro režim celé obrazovky
      </div>

      <div v-if="!isChillOut" class="timer-wrapper" :class="{ 'time-critical': timeLeft <= 60 && timeLeft > 0, 'time-up': timeLeft === 0 }">
        {{ formatTime(timeLeft) }}
      </div>

      <div v-else class="chill-wrapper">
        <h2>CHILL OUT</h2>
        <p>...and no stress</p>
      </div>

      <div v-if="!isChillOut && totalTime > 0" class="progress-container">
        <div
            class="progress-bar"
            :class="{ 'progress-critical': timeLeft <= 60 && timeLeft > 0 }"
            :style="{ width: progressPercentage + '%' }"
        ></div>
      </div>

    </div>
  </ClientOnly>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

const { timeLeft, totalTime, isChillOut } = useStageTimer(false)
const isFullscreen = ref(false)

const progressPercentage = computed(() => {
  if (totalTime.value <= 0) return 0
  return (timeLeft.value / totalTime.value) * 100
})

const enterFullscreen = async () => {
  if (!document.fullscreenElement) {
    try {
      await document.documentElement.requestFullscreen()
    } catch (err) {
      console.warn("Chyba při pokusu o zobrazení na celou obrazovku:", err)
    }
  }
}

const handleFullscreenChange = () => {
  isFullscreen.value = !!document.fullscreenElement
}

onMounted(() => {
  if (import.meta.client) {
    document.addEventListener('fullscreenchange', handleFullscreenChange)
  }
})

onUnmounted(() => {
  if (import.meta.client) {
    document.removeEventListener('fullscreenchange', handleFullscreenChange)
  }
})

definePageMeta({ layout: false })
</script>

<style scoped>
.stage-display {
  height: 100vh;
  width: 100vw;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #000;
  color: #fff;
  overflow: hidden;
  transition: background-color 1s ease;
  position: relative;
  user-select: none;
}

.hide-cursor { cursor: none; }

.fullscreen-prompt {
  position: absolute;
  top: 20px;
  background: rgba(255, 255, 255, 0.2);
  padding: 10px 20px;
  border-radius: 8px;
  font-family: sans-serif;
  font-size: 1.2rem;
  animation: pulse 2s infinite;
  pointer-events: none;
}

.timer-wrapper {
  font-size: 35vw;
  font-weight: 900;
  font-family: 'Courier New', Courier, monospace;
  line-height: 1;
  transition: color 0.5s ease;
  transform: translateY(-2vh);
}

.time-critical { color: #ef4444; }
.time-up { color: #dc2626; text-decoration: blink; }

.chill-mode { background-color: #0a0a2a; }

.chill-wrapper { text-align: center; animation: float 4s ease-in-out infinite; }
.chill-wrapper h2 { font-size: 15vw; margin: 0; color: #a78bfa; letter-spacing: 0.1em; }
.chill-wrapper p { font-size: 4vw; color: #8b5cf6; margin-top: 20px; text-transform: uppercase; letter-spacing: 0.2em; }

.progress-container {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 2vh;
  background-color: #222;
}

.progress-bar {
  height: 100%;
  background-color: #3b82f6;
  transition: width 1s linear, background-color 0.5s ease;
}

.progress-critical {
  background-color: #ef4444;
}

@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
  100% { transform: translateY(0px); }
}

@keyframes pulse {
  0% { opacity: 0.5; }
  50% { opacity: 1; }
  100% { opacity: 0.5; }
}
</style>