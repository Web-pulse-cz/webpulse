<template>
  <div class="control-panel">
    <h1>Stage Timer - Ovládací panel</h1>

    <div class="layout-grid">

      <div class="main-controls">
        <div class="status-box">
          <p class="current-time">{{ formatTime(timeLeft) }}</p>
          <p class="status-text" :class="{ 'text-green': isRunning, 'text-blue': isChillOut }">
            Stav: {{ isChillOut ? 'CHILL OUT MÓD' : (isRunning ? 'BĚŽÍ' : 'PAUZA / PŘIPRAVENO') }}
          </p>
        </div>

        <div class="actions">
          <button
              @click="toggleTimer"
              class="btn-massive"
              :class="isRunning ? 'btn-warning' : 'btn-success'">
            {{ isRunning ? '⏸ Pozastavit' : '▶ Spustit' }}
          </button>

          <button
              @click="toggleChillOut"
              class="btn-massive btn-chill">
            {{ isChillOut ? 'Vypnout Chill Out' : '☕ Chill Out Mód' }}
          </button>
        </div>

        <div class="quick-set">
          <h3>Rychlé nastavení</h3>
          <button @click="setTime(5 * 60)">5 Minut</button>
          <button @click="setTime(10 * 60)">10 Minut</button>
          <button @click="setTime(15 * 60)">15 Minut</button>
          <button @click="setTime(0)" class="btn-danger">Reset na 0</button>
        </div>
      </div>

      <div class="queue-section">
        <h3>Fronta časovačů</h3>

        <div class="add-to-queue">
          <input type="text" v-model="newTimerName" placeholder="Název (např. Řečník 1)" class="input-name" />
          <div class="time-inputs">
            <input type="number" v-model="newTimerMinutes" min="0" placeholder="Min" />
            <span>:</span>
            <input type="number" v-model="newTimerSeconds" min="0" max="59" placeholder="Sek" />
            <button @click="addToQueue" class="btn-add">Přidat</button>
          </div>
        </div>

        <ul class="queue-list">
          <li v-for="(item, index) in queue" :key="item.id" :class="{ 'active-item': index === currentIndex }">

            <div class="item-main">
              <div class="item-info">
                <span class="item-name">{{ item.name }}</span>
                <span class="item-time">{{ formatTime(item.seconds) }}</span>
              </div>
              <div class="item-actions">
                <button @click="loadFromQueue(index)" class="btn-small">Načíst</button>
                <button @click="removeFromQueue(index)" class="btn-small btn-danger">✖</button>
              </div>
            </div>

            <div class="item-deviation">
              <span class="dev-label">Korekce:</span>
              <button @click="adjustDeviation(index, -60)" class="btn-dev">-1m</button>
              <span class="dev-value" :class="item.deviation > 0 ? 'text-red' : (item.deviation < 0 ? 'text-green' : '')">
                {{ formatDeviation(item.deviation) }}
              </span>
              <button @click="adjustDeviation(index, 60)" class="btn-dev">+1m</button>
            </div>

          </li>
          <li v-if="queue.length === 0" class="empty-queue">
            Fronta je prázdná
          </li>
        </ul>

        <button
            @click="loadNext"
            class="btn-next"
            :disabled="queue.length === 0 || currentIndex >= queue.length - 1">
          ⏭ Načíst další z fronty
        </button>

        <div class="final-speaker-section">
          <h4>Vypočítat čas závěrečného řečníka</h4>
          <p class="total-deviation">
            Celkový skluz / ušetřený čas:
            <strong :class="totalDeviation > 0 ? 'text-red' : (totalDeviation < 0 ? 'text-green' : '')">
              {{ formatDeviation(totalDeviation) }}
            </strong>
          </p>
          <div class="final-speaker-inputs">
            <input type="text" v-model="finalName" placeholder="Název" class="input-name" />
            <div style="display: flex; gap: 5px; align-items: center; margin-top: 5px;">
              <input type="number" v-model="finalMin" placeholder="Základ Min" />
              <span>:</span>
              <input type="number" v-model="finalSec" placeholder="Základ Sek" />
            </div>
            <button @click="addFinalSpeaker" class="btn-add-final" :disabled="finalMin === 0 && finalSec === 0">
              Aplikovat korekci a přidat
            </button>
          </div>
        </div>

      </div>

    </div>

    <div class="footer">
      <NuxtLink to="/display" target="_blank" class="open-display">
        Otevřít okno časomíry (Stage Display) ↗
      </NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const { timeLeft, isRunning, isChillOut, setTime, toggleTimer, toggleChillOut } = useStageTimer(true)

// --- Logika Fronty ---
interface QueueItem {
  id: number;
  name: string;
  seconds: number;
  deviation: number; // Nové: odchylka v sekundách (+ znamená přetáhl, - nedočerpal)
}

const queue = ref<QueueItem[]>([])
const currentIndex = ref(-1)

const newTimerName = ref('')
const newTimerMinutes = ref(0)
const newTimerSeconds = ref(0)

const addToQueue = () => {
  const totalSeconds = (newTimerMinutes.value * 60) + newTimerSeconds.value
  if (totalSeconds <= 0) return

  queue.value.push({
    id: Date.now(),
    name: newTimerName.value || `Časovač ${queue.value.length + 1}`,
    seconds: totalSeconds,
    deviation: 0 // Výchozí odchylka je nula
  })

  newTimerName.value = ''
  newTimerMinutes.value = 0
  newTimerSeconds.value = 0
}

const loadFromQueue = (index: number) => {
  if (index >= 0 && index < queue.value.length) {
    currentIndex.value = index
    setTime(queue.value[index].seconds)
    toggleTimer()
  }
}

const loadNext = () => {
  if (queue.value.length === 0) return
  if (currentIndex.value < queue.value.length - 1) {
    loadFromQueue(currentIndex.value + 1)
  }
}

const removeFromQueue = (index: number) => {
  queue.value.splice(index, 1)
  if (currentIndex.value === index) {
    currentIndex.value = -1
  } else if (currentIndex.value > index) {
    currentIndex.value--
  }
}

// --- Logika Korekcí ---

// Úprava odchylky u konkrétní položky
const adjustDeviation = (index: number, seconds: number) => {
  queue.value[index].deviation += seconds
}

// Formátování odchylky pro zobrazení (např. "+01:00" nebo "-02:30")
const formatDeviation = (sec: number) => {
  const sign = sec > 0 ? '+' : (sec < 0 ? '-' : '')
  const absSec = Math.abs(sec)
  const m = Math.floor(absSec / 60).toString().padStart(2, '0')
  const s = (absSec % 60).toString().padStart(2, '0')
  return `${sign}${m}:${s}`
}

// Výpočet celkové odchylky všech řečníků
const totalDeviation = computed(() => {
  return queue.value.reduce((sum, item) => sum + item.deviation, 0)
})

// Přidání finálního speakera
const finalName = ref('Poslední řečník')
const finalMin = ref(0)
const finalSec = ref(0)

const addFinalSpeaker = () => {
  const baseSeconds = (finalMin.value * 60) + finalSec.value

  // Plánovaný čas MINUS nasbíraný skluz (pokud byl skluz kladný, čas se zkrátí)
  let calculatedSeconds = baseSeconds - totalDeviation.value

  // Ochrana, aby poslednímu řečníkovi nezbyl záporný čas
  if (calculatedSeconds < 0) calculatedSeconds = 0

  queue.value.push({
    id: Date.now(),
    name: finalName.value || 'Závěrečný speaker',
    seconds: calculatedSeconds,
    deviation: 0
  })

  // Reset políček
  finalName.value = 'Poslední řečník'
  finalMin.value = 0
  finalSec.value = 0
}
</script>

<style scoped>
.control-panel {
  max-width: 1000px;
  margin: 40px auto;
  padding: 20px;
  background: #1e1e1e;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

h1 { text-align: center; border-bottom: 1px solid #333; padding-bottom: 20px; margin-bottom: 20px; }

.layout-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

@media (max-width: 768px) {
  .layout-grid { grid-template-columns: 1fr; }
}

.status-box {
  text-align: center;
  background: #000;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.current-time { font-size: 4rem; font-weight: bold; margin: 0; font-family: monospace; }
.status-text { font-size: 1.2rem; margin-top: 10px; font-weight: bold; }
.text-green { color: #4ade80; }
.text-blue { color: #60a5fa; }
.text-red { color: #ef4444; }

.actions, .quick-set, .queue-section {
  background: #2a2a2a;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

h3 { margin-top: 0; margin-bottom: 15px; color: #aaa; }

button {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 15px;
  margin: 5px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  transition: 0.2s;
}
button:hover:not(:disabled) { filter: brightness(1.2); }
button:disabled { background: #555; cursor: not-allowed; opacity: 0.5; }

.btn-danger { background: #ef4444; }
.btn-success { background: #10b981; }
.btn-warning { background: #f59e0b; }
.btn-chill { background: #8b5cf6; }

.btn-massive {
  width: calc(50% - 15px);
  padding: 15px;
  font-size: 1.2rem;
  font-weight: bold;
}

/* Styly pro frontu */
.add-to-queue {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #444;
}

.input-name { width: 100%; box-sizing: border-box; }

.time-inputs { display: flex; align-items: center; gap: 10px; }

input {
  padding: 10px;
  font-size: 1rem;
  border-radius: 6px;
  border: 1px solid #555;
  background: #111;
  color: white;
}
input[type="number"] { width: 70px; }

.btn-add { flex-grow: 1; margin: 0; background: #2563eb; }

.queue-list {
  list-style: none;
  padding: 0;
  margin: 0 0 20px 0;
  max-height: 350px;
  overflow-y: auto;
}

.queue-list li {
  display: flex;
  flex-direction: column;
  background: #333;
  padding: 10px 15px;
  margin-bottom: 8px;
  border-radius: 6px;
  transition: 0.2s;
}

.queue-list li.active-item {
  border-left: 5px solid #4ade80;
  background: #1f2937;
}

.item-main {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.item-info { display: flex; flex-direction: column; }
.item-name { font-weight: bold; font-size: 1.1rem; }
.item-time { color: #aaa; font-family: monospace; font-size: 1rem; }

.item-actions { display: flex; align-items: center; }

.item-deviation {
  display: flex;
  align-items: center;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid #444;
  font-size: 0.9rem;
  gap: 10px;
}

.dev-label { color: #aaa; }
.dev-value { font-weight: bold; font-family: monospace; font-size: 1rem; width: 60px; text-align: center; }
.btn-dev { padding: 4px 8px; font-size: 0.8rem; background: #555; margin: 0; }

.btn-small { padding: 6px 10px; font-size: 0.9rem; margin: 0 0 0 5px; }
.empty-queue { text-align: center; color: #666; font-style: italic; background: transparent !important; }

.btn-next { width: 100%; padding: 15px; font-size: 1.2rem; font-weight: bold; margin: 0 0 20px 0; background: #4f46e5; }

/* Styly pro finálního speakera */
.final-speaker-section {
  background: #111;
  padding: 15px;
  border-radius: 8px;
  border: 1px dashed #555;
}

.final-speaker-section h4 { margin: 0 0 10px 0; color: #fff; }
.total-deviation { margin: 0 0 15px 0; font-size: 0.95rem; color: #aaa; }

.btn-add-final {
  margin-top: 10px;
  width: 100%;
  background: #10b981;
  font-weight: bold;
}

.footer { text-align: center; margin-top: 20px; }
.open-display {
  display: inline-block;
  padding: 15px 30px;
  background: #fff;
  color: #000;
  text-decoration: none;
  font-weight: bold;
  border-radius: 8px;
}
</style>