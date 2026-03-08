<template>
  <div class="min-h-screen bg-slate-950 text-slate-300 p-4 font-sans selection:bg-blue-500/30">
    <div class="max-w-7xl mx-auto">

      <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-800">
        <h1 class="text-2xl font-bold text-white tracking-wide">Stage Timer Control</h1>
        <NuxtLink to="/display" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2">
          Otevřít okno časomíry
          <Icon name="lucide:external-link" class="w-4 h-4 text-slate-400" />
        </NuxtLink>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <div class="lg:col-span-7 flex flex-col gap-4">

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 text-center shadow-lg relative overflow-hidden">
            <div :class="timeLeft < 0 ? 'bg-red-900/20 absolute inset-0' : 'hidden'"></div>

            <p class="relative text-7xl font-mono font-black tracking-tighter" :class="timeLeft < 0 ? 'text-red-500' : 'text-white'">
              {{ formatTime(timeLeft) }}
            </p>

            <p class="relative mt-2 text-sm font-bold uppercase tracking-widest" :class="isChillOut ? 'text-blue-400' : (isRunning ? 'text-emerald-400' : 'text-slate-500')">
              {{ isChillOut ? 'CHILL OUT MÓD' : (isRunning ? 'BĚŽÍ' : 'PAUZA / PŘIPRAVENO') }}
            </p>

            <div class="relative flex justify-center gap-3 mt-6">
              <button @click="adjustLiveTime(-60)" class="px-4 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-full text-sm font-bold transition flex items-center gap-1">
                <Icon name="lucide:minus" class="w-4 h-4" /> 1 Min
              </button>
              <button @click="adjustLiveTime(60)" class="px-4 py-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 rounded-full text-sm font-bold transition flex items-center gap-1">
                <Icon name="lucide:plus" class="w-4 h-4" /> 1 Min
              </button>
            </div>
          </div>

          <div class="flex gap-4">
            <button @click="toggleTimer" class="flex-1 py-4 text-lg font-bold text-white rounded-xl shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2" :class="isRunning ? 'bg-amber-600 hover:bg-amber-500' : 'bg-emerald-600 hover:bg-emerald-500'">
              <Icon :name="isRunning ? 'lucide:pause' : 'lucide:play'" class="w-6 h-6" />
              {{ isRunning ? 'Pozastavit' : 'Spustit' }}
            </button>
            <button @click="toggleChillOut" class="flex-1 py-4 text-lg font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2">
              <Icon :name="isChillOut ? 'lucide:coffee' : 'lucide:cup-soda'" class="w-6 h-6" />
              {{ isChillOut ? 'Vypnout Chill Out' : 'Chill Out Mód' }}
            </button>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
              <h3 class="text-xs uppercase text-slate-500 font-bold mb-3 tracking-wider flex items-center gap-2">
                <Icon name="lucide:zap" class="w-4 h-4" /> Rychlá volba
              </h3>
              <div class="grid grid-cols-2 gap-2">
                <button @click="setTime(15 * 60)" class="btn-secondary">15 m</button>
                <button @click="setTime(25 * 60)" class="btn-secondary">25 m</button>
                <button @click="setTime(40 * 60)" class="btn-secondary">40 m</button>
                <button @click="setTime(0)" class="btn-danger flex justify-center items-center gap-1">
                  <Icon name="lucide:rotate-ccw" class="w-4 h-4" /> Reset
                </button>
              </div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
              <h3 class="text-xs uppercase text-slate-500 font-bold mb-3 tracking-wider flex items-center gap-2">
                <Icon name="lucide:clock-4" class="w-4 h-4" /> Vlastní čas
              </h3>
              <div class="flex items-center gap-2 mb-2">
                <input type="number" v-model="customMinutes" min="0" placeholder="Min" class="input-field w-full text-center" />
                <span class="font-bold">:</span>
                <input type="number" v-model="customSeconds" min="0" max="59" placeholder="Sek" class="input-field w-full text-center" />
              </div>
              <button @click="applyCustomTime" class="btn-primary w-full mt-1 flex justify-center items-center gap-2">
                <Icon name="lucide:check" class="w-4 h-4" /> Připravit čas
              </button>
            </div>
          </div>

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 border-l-4 border-l-yellow-500">
            <h3 class="text-xs uppercase text-slate-500 font-bold mb-3 tracking-wider flex justify-between items-center">
              <span class="flex items-center gap-2"><Icon name="lucide:message-square-warning" class="w-4 h-4 text-yellow-500" /> Zpráva pro řečníka</span>
              <span v-if="isFlashVisible" class="text-yellow-500 animate-pulse text-[10px] bg-yellow-500/10 px-2 py-0.5 rounded">AKTIVNÍ</span>
            </h3>
            <div class="flex gap-2">
              <input type="text" v-model="msgInput" placeholder="Např. Konec za 2 minuty!" class="input-field flex-1" @keyup.enter="handleSendFlash" />
              <button v-if="!isFlashVisible" @click="handleSendFlash" class="bg-yellow-600 hover:bg-yellow-500 text-white px-4 py-2 rounded-lg font-bold transition flex items-center gap-2">
                <Icon name="lucide:send" class="w-4 h-4" /> Zobrazit
              </button>
              <button v-else @click="hideFlash" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-lg font-bold transition flex items-center gap-2">
                <Icon name="lucide:eye-off" class="w-4 h-4" /> Skrýt
              </button>
            </div>
          </div>

        </div>

        <div class="lg:col-span-5 flex flex-col gap-4">

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex flex-col h-full">
            <h3 class="text-xs uppercase text-slate-500 font-bold mb-4 tracking-wider flex items-center gap-2">
              <Icon name="lucide:list-ordered" class="w-4 h-4" /> Fronta řečníků
            </h3>

            <div class="flex gap-2 mb-4 pb-4 border-b border-slate-800">
              <input type="text" v-model="newTimerName" placeholder="Název" class="input-field flex-1" />
              <input type="number" v-model="newTimerMinutes" placeholder="M" class="input-field w-24 text-center" />
              <button @click="addToQueue" class="btn-primary px-3 flex justify-center items-center">
                <Icon name="lucide:plus" class="w-5 h-5" />
              </button>
            </div>

            <ul class="flex-1 overflow-y-auto space-y-2 pr-1 custom-scrollbar min-h-[200px] max-h-[400px]">
              <li v-for="(item, index) in queue" :key="item.id" class="p-3 rounded-lg border transition-all" :class="index === currentIndex ? 'bg-slate-800 border-emerald-500' : 'bg-slate-950 border-slate-800'">

                <div class="flex justify-between items-center mb-2">
                  <div class="font-bold text-white truncate pr-2 flex items-center gap-2">
                    <Icon v-if="index === currentIndex" name="lucide:play-circle" class="w-4 h-4 text-emerald-500" />
                    {{ item.name }}
                  </div>
                  <div class="font-mono text-sm text-slate-400 flex items-center gap-1">
                    <Icon name="lucide:timer" class="w-3 h-3 opacity-50" /> {{ formatTime(item.seconds) }}
                  </div>
                </div>

                <div class="flex justify-between items-center">
                  <div class="flex items-center gap-1 bg-slate-900 rounded p-1">
                    <button @click="adjustDeviation(index, -60)" class="text-slate-400 hover:text-white p-1 rounded hover:bg-slate-800 transition"><Icon name="lucide:minus" class="w-3 h-3"/></button>
                    <span class="text-xs font-mono w-12 text-center" :class="item.deviation > 0 ? 'text-red-400' : (item.deviation < 0 ? 'text-emerald-400' : 'text-slate-500')">
                      {{ formatDeviation(item.deviation) }}
                    </span>
                    <button @click="adjustDeviation(index, 60)" class="text-slate-400 hover:text-white p-1 rounded hover:bg-slate-800 transition"><Icon name="lucide:plus" class="w-3 h-3"/></button>
                  </div>
                  <div class="flex gap-1">
                    <button @click="loadFromQueue(index)" class="btn-secondary px-3 py-1 text-xs flex items-center gap-1">
                      <Icon name="lucide:upload" class="w-3 h-3" /> Načíst
                    </button>
                    <button @click="removeFromQueue(index)" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 px-2 py-1 rounded transition flex items-center justify-center">
                      <Icon name="lucide:trash-2" class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
              </li>
              <li v-if="queue.length === 0" class="flex flex-col items-center justify-center text-slate-600 text-sm py-8 italic gap-2">
                <Icon name="lucide:inbox" class="w-8 h-8 opacity-50" />
                Fronta je prázdná
              </li>
            </ul>

            <button @click="loadNext" class="mt-4 w-full bg-blue-600 hover:bg-blue-500 disabled:bg-slate-800 disabled:text-slate-600 text-white font-bold py-3 rounded-lg transition flex items-center justify-center gap-2" :disabled="queue.length === 0 || currentIndex >= queue.length - 1">
              <Icon name="lucide:skip-forward" class="w-5 h-5" /> Načíst další z fronty
            </button>
          </div>

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <div class="flex justify-between items-end mb-4">
              <h4 class="text-xs uppercase text-slate-500 font-bold tracking-wider flex items-center gap-2">
                <Icon name="lucide:calculator" class="w-4 h-4" /> Chytrý přepočet času
              </h4>
              <p class="text-xs">
                Aktuální skluz: <strong class="font-mono text-sm" :class="totalDeviation > 0 ? 'text-red-400' : (totalDeviation < 0 ? 'text-emerald-400' : '')">{{ formatDeviation(totalDeviation) }}</strong>
              </p>
            </div>

            <div class="flex flex-col gap-3">
              <div class="flex gap-2">
                <select v-model="smartTarget" class="input-field flex-1 text-sm py-2 appearance-none bg-slate-800">
                  <option value="new">+ Přidat jako nového řečníka</option>
                  <optgroup label="Existující ve frontě" v-if="queue.length > 0">
                    <option v-for="item in queue" :key="item.id" :value="item.id">Upravit: {{ item.name }}</option>
                  </optgroup>
                </select>
                <input type="number" v-model="smartBaseMin" placeholder="Plán (Min)" class="input-field w-24 text-center" />
              </div>

              <input v-if="smartTarget === 'new'" type="text" v-model="smartNewName" placeholder="Název nového řečníka" class="input-field w-full" />

              <button @click="applySmartCorrection" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2 rounded-lg transition text-sm flex justify-center items-center gap-2 disabled:opacity-50" :disabled="smartBaseMin <= 0">
                <Icon name="lucide:check-circle" class="w-4 h-4" /> Aplikovat korekci na cíl
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const { timeLeft, isRunning, isChillOut, setTime, adjustLiveTime, toggleTimer, toggleChillOut, sendFlash, hideFlash, isFlashVisible } = useStageTimer(true)

// Flash Message local state
const msgInput = ref('')
const handleSendFlash = () => {
  if (msgInput.value.trim()) sendFlash(msgInput.value)
}

// Custom Time
const customMinutes = ref(0)
const customSeconds = ref(0)
const applyCustomTime = () => setTime((customMinutes.value * 60) + customSeconds.value)

// Queue Logic
interface QueueItem { id: number; name: string; seconds: number; deviation: number; }
const queue = ref<QueueItem[]>([])
const currentIndex = ref(-1)

const newTimerName = ref('')
const newTimerMinutes = ref(0)
const newTimerSeconds = ref(0)

const addToQueue = () => {
  const t = (newTimerMinutes.value * 60) + newTimerSeconds.value
  if (t <= 0) return
  queue.value.push({ id: Date.now(), name: newTimerName.value || `Řečník ${queue.value.length + 1}`, seconds: t, deviation: 0 })
  newTimerName.value = ''; newTimerMinutes.value = 0; newTimerSeconds.value = 0
}

const loadFromQueue = (index: number) => {
  if (index >= 0 && index < queue.value.length) {
    currentIndex.value = index
    setTime(queue.value[index].seconds)
    toggleTimer()
  }
}

const loadNext = () => {
  if (queue.value.length > 0 && currentIndex.value < queue.value.length - 1) loadFromQueue(currentIndex.value + 1)
}

const removeFromQueue = (index: number) => {
  queue.value.splice(index, 1)
  if (currentIndex.value === index) currentIndex.value = -1
  else if (currentIndex.value > index) currentIndex.value--
}

// Deviation Logic
const adjustDeviation = (index: number, seconds: number) => queue.value[index].deviation += seconds

const formatDeviation = (sec: number) => {
  const sign = sec > 0 ? '+' : (sec < 0 ? '-' : '')
  const m = Math.floor(Math.abs(sec) / 60).toString().padStart(2, '0')
  const s = (Math.abs(sec) % 60).toString().padStart(2, '0')
  return `${sign}${m}:${s}`
}

const totalDeviation = computed(() => queue.value.reduce((sum, item) => sum + item.deviation, 0))

// Smart Correction Logic (Nové)
const smartTarget = ref<number | 'new'>('new')
const smartNewName = ref('Závěrečný řečník')
const smartBaseMin = ref(0)

const applySmartCorrection = () => {
  // Původní plánovaný čas MINUS nahromaděný skluz
  let calculatedSeconds = (smartBaseMin.value * 60) - totalDeviation.value
  if (calculatedSeconds < 0) calculatedSeconds = 0 // Pojistka proti zápornému času

  if (smartTarget.value === 'new') {
    // Přidání nového záznamu do fronty
    queue.value.push({
      id: Date.now(),
      name: smartNewName.value || 'Nový řečník',
      seconds: calculatedSeconds,
      deviation: 0
    })
    smartNewName.value = 'Závěrečný řečník' // Reset
  } else {
    // Aktualizace existující položky ve frontě
    const targetItem = queue.value.find(item => item.id === smartTarget.value)
    if (targetItem) {
      targetItem.seconds = calculatedSeconds
      targetItem.deviation = 0 // Vynulujeme jeho případnou vlastní odchylku, protože jsme mu čas právě nastavili na "čisto"
    }
  }

  // Reset základních minut, abychom nenaklikali omylem dvakrát
  smartBaseMin.value = 0
}
</script>

<style>
.input-field {
  @apply bg-slate-950 border border-slate-700 text-white rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition;
}
.btn-primary {
  @apply bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 rounded-lg transition;
}
.btn-secondary {
  @apply bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold py-2 rounded-lg transition;
}
.btn-danger {
  @apply bg-red-600/80 hover:bg-red-500 text-white font-semibold py-2 rounded-lg transition;
}
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #0f172a; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
</style>