<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';

// --- IMPORTY DAT ---
import rawCitiesData from '~/assets/data/cities.json';
import rawMonumentsData from '~/assets/data/castles.json';
import rawPeaksData from '~/assets/data/peaks.json';
import rawNatureData from '~/assets/data/nature.json';

const categories = [
  { id: 'cities', name: 'Města', icon: '🏙️' },
  { id: 'monuments', name: 'Hrady a zámky', icon: '🏰' },
  { id: 'peaks', name: 'Vrcholy hor', icon: '⛰️' },
  { id: 'nature', name: 'Chráněná příroda', icon: '🌲' },
];

// --- STAV APLIKACE ---
const locationsData = ref({ cities: [], monuments: [], peaks: [], nature: [] });
const map = ref(null);
const L = ref(null);
const currentMode = ref('cities');
const gameStatus = ref('playing');
const currentRound = ref(1);
const score = ref(0);
const gameQueue = ref([]);
const currentTarget = ref(null);
const lastGuess = ref(null);
const totalRoundsInGame = ref(10);

// --- VÝRAZNÉ HRANICE ---
const addMapBoundaries = async () => {
  if (!map.value || !L.value) return;

  // Vytvoření speciální vrstvy pro hranice, aby byly vždy vidět
  map.value.createPane('borders');
  map.value.getPane('borders').style.zIndex = 450;
  map.value.getPane('borders').style.pointerEvents = 'none';

  const borderStyles = {
    country: { color: '#000000', weight: 4, opacity: 1, fillOpacity: 0, pane: 'borders' },
    regions: { color: '#334155', weight: 2, opacity: 0.8, fillOpacity: 0, pane: 'borders' },
    districts: { color: '#64748b', weight: 1, opacity: 0.5, dashArray: '5, 5', fillOpacity: 0, pane: 'borders' }
  };

  try {
    const sources = [
      { url: 'https://raw.githubusercontent.com/smartmaps/districts-cz/master/districts-cz.json', style: borderStyles.districts },
      { url: 'https://raw.githubusercontent.com/smartmaps/regions-cz/master/regions-cz.json', style: borderStyles.regions },
      { url: 'https://raw.githubusercontent.com/smartmaps/czech-republic/master/czech-republic.json', style: borderStyles.country }
    ];

    for (const source of sources) {
      const res = await fetch(source.url);
      const data = await res.json();
      L.value.geoJSON(data, { style: source.style }).addTo(map.value);
    }
  } catch (e) {
    console.error("Hranice se nepodařilo načíst:", e);
  }
};

// --- TRANSFORMACE DAT ---
const processGeoData = (geoJson) => {
  const output = [];
  const features = geoJson?.features || [];
  features.forEach((item) => {
    const p = item.properties || {};
    const g = item.geometry || {};
    if (!g.coordinates && !item.center) return;

    let lat, lng;
    if (g.type === 'Point') [lng, lat] = g.coordinates;
    else if (item.center) { lat = item.center.lat; lng = item.center.lon; }
    else {
      const first = g.type === 'Polygon' ? g.coordinates[0][0] : g.coordinates[0][0][0];
      [lng, lat] = Array.isArray(first) && !Array.isArray(first[0]) ? first : first[0];
    }

    let infoText = 'Zajímavé místo v České republice.';
    if (p.ele) infoText = `Nadmořská výška: ${p.ele} m n. m.`;
    else if (p.population) infoText = `Počet obyvatel: ${parseInt(p.population).toLocaleString()}`;
    else if (p.protection_title) infoText = `${p.protection_title} na území ČR.`;
    else if (p.wikipedia) infoText = `Více na Wikipedii (heslo: ${p.wikipedia.split(':')[1]})`;

    output.push({ name: p.name || p["name:cs"] || 'Neznámé místo', lat, lng, info: infoText });
  });
  return output;
};

// --- HERNÍ LOGIKA ---
const restartGame = () => {
  const data = locationsData.value[currentMode.value] || [];
  if (data.length === 0) return;
  const shuffled = [...data].sort(() => 0.5 - Math.random());
  totalRoundsInGame.value = Math.min(10, shuffled.length);
  gameQueue.value = shuffled.slice(0, totalRoundsInGame.value);
  currentRound.value = 1;
  score.value = 0;
  gameStatus.value = 'playing';
  initRound();
};

const initRound = () => {
  if (gameQueue.value.length === 0) return;
  currentTarget.value = gameQueue.value[currentRound.value - 1];
  lastGuess.value = null;
  if (map.value && L.value) {
    map.value.eachLayer((layer) => {
      if (layer instanceof L.value.Marker || layer instanceof L.value.Polyline) layer.remove();
    });
    map.value.setView([49.8, 15.5], 7);
  }
};

const handleMapClick = (e) => {
  if (lastGuess.value || !currentTarget.value || gameStatus.value === 'finished') return;
  const { lat, lng } = e.latlng;
  const R = 6371;
  const dLat = (currentTarget.value.lat - lat) * Math.PI / 180;
  const dLon = (currentTarget.value.lng - lng) * Math.PI / 180;
  const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat * Math.PI / 180) * Math.cos(currentTarget.value.lat * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2);
  const dist = R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

  // BODOVÁNÍ S THRESHOLDEM
  const threshold = currentMode.value === 'nature' ? 15 : 5;
  const pointsEarned = dist <= threshold ? 5000 : Math.max(0, 5000 - Math.round(dist * 100));

  lastGuess.value = { lat, lng, distance: Math.round(dist * 10) / 10, points: pointsEarned };
  score.value += pointsEarned;

  const dotIcon = (color) => L.value.divIcon({
    className: 'custom-dot-container',
    html: `<div class="marker-dot" style="background-color: ${color}"></div>`,
    iconSize: [20, 20], iconAnchor: [10, 10]
  });

  L.value.marker([lat, lng], { icon: dotIcon('#ef4444') }).addTo(map.value);
  L.value.marker([currentTarget.value.lat, currentTarget.value.lng], { icon: dotIcon('#22c55e') }).addTo(map.value);
  L.value.polyline([[lat, lng], [currentTarget.value.lat, currentTarget.value.lng]], { color: '#3b82f6', weight: 2, dashArray: '8, 8', opacity: 0.6 }).addTo(map.value);
};

onMounted(async () => {
  if (import.meta.client) {
    await nextTick();
    locationsData.value.cities = processGeoData(rawCitiesData);
    locationsData.value.monuments = processGeoData(rawMonumentsData);
    locationsData.value.peaks = processGeoData(rawPeaksData);
    locationsData.value.nature = processGeoData(rawNatureData);

    const leaflet = await import('leaflet');
    import('leaflet/dist/leaflet.css');
    L.value = leaflet;
    map.value = leaflet.map('map', { center: [49.8, 15.5], zoom: 7, zoomControl: false, attributionControl: false });
    leaflet.tileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}{r}.png').addTo(map.value);

    // PŘIDÁNÍ HRANIC
    await addMapBoundaries();

    map.value.on('click', handleMapClick);
    restartGame();
  }
});

watch(currentMode, restartGame);
definePageMeta({ layout: 'clean' });
</script>

<template>
  <div class="flex h-screen w-screen overflow-hidden bg-slate-50 font-sans text-slate-900">
    <aside class="flex w-80 flex-col border-r border-slate-200 bg-white shadow-sm shrink-0 h-full">
      <div class="p-6 border-b border-slate-50">
        <div class="flex items-center gap-3 mb-1">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-xl shadow-lg shadow-blue-100 font-bold text-white uppercase tracking-tighter">M</div>
          <h1 class="text-xl font-bold tracking-tight text-slate-800">Slepá Mapa</h1>
        </div>
        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-[3.25rem]">Česká Republika</p>
      </div>
      <nav class="flex-1 overflow-y-auto p-4 space-y-1 custom-scrollbar text-slate-600">
        <p class="px-3 mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">Kategorie</p>
        <button v-for="cat in categories" :key="cat.id" @click="currentMode = cat.id" :class="currentMode === cat.id ? 'bg-blue-50 text-blue-600 ring-1 ring-blue-100' : 'text-slate-500 hover:bg-slate-50'" class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition-all duration-200">
          <span class="text-lg opacity-80">{{ cat.icon }}</span> {{ cat.name }}
        </button>
      </nav>
      <div class="p-6 bg-slate-50/50 border-t border-slate-100 mt-auto">
        <div class="grid grid-cols-2 gap-3 mb-4">
          <div class="rounded-2xl bg-white p-3 shadow-sm border border-slate-100"><span class="block text-[9px] font-bold uppercase text-slate-400 mb-1 font-black">Kolo</span><span class="text-base font-bold text-slate-700">{{ currentRound }} / {{ totalRoundsInGame }}</span></div>
          <div class="rounded-2xl bg-white p-3 shadow-sm border border-slate-100"><span class="block text-[9px] font-bold uppercase text-slate-400 mb-1 font-black">Skóre</span><span class="text-base font-bold text-blue-600">{{ (score || 0).toLocaleString() }}</span></div>
        </div>
        <button @click="restartGame" class="w-full flex items-center justify-center gap-2 rounded-xl bg-slate-900 py-3.5 text-sm font-bold text-white shadow-xl hover:bg-slate-800 active:scale-[0.98] transition-all">🔄 Restartovat hru</button>
      </div>
    </aside>

    <main class="relative flex flex-1 flex-col p-4 md:p-6 lg:p-8 min-w-0 h-full bg-[#f8fafc]">
      <div class="mb-6 flex items-center justify-between px-2 shrink-0 text-slate-800">
        <div v-if="currentTarget" class="flex flex-col gap-1">
          <div class="flex items-center gap-2"><span class="px-2 py-0.5 rounded-md bg-blue-100 text-[10px] font-bold text-blue-600 uppercase">{{ categories.find(c => c.id === currentMode)?.name }}</span><span class="h-1 w-1 rounded-full bg-slate-300"></span><span class="text-xs font-medium text-slate-400 italic font-bold">Najdi na mapě</span></div>
          <h2 class="text-4xl font-black uppercase tracking-tight">Najdi: <span class="text-blue-600">{{ currentTarget.name }}</span></h2>
        </div>
      </div>
      <div class="relative flex-1 overflow-hidden rounded-[2.5rem] border-[12px] border-white bg-white shadow-2xl shadow-blue-900/5 ring-1 ring-slate-200/50">
        <div id="map" class="h-full w-full"></div>
        <transition name="card-pop">
          <div v-if="lastGuess" class="absolute bottom-6 left-6 right-6 z-[1000] flex items-center justify-between rounded-3xl border border-white/40 bg-white/80 p-5 shadow-2xl backdrop-blur-xl ring-1 ring-black/5">
            <div class="max-w-[60%] pl-2">
              <h3 class="text-xl font-bold text-slate-900 mb-1 leading-tight">{{ currentTarget?.name }}</h3>
              <p class="text-xs text-slate-600 leading-relaxed italic line-clamp-2">"{{ currentTarget?.info }}"</p>
            </div>
            <div class="flex items-center gap-6 border-l border-slate-200/50 pl-6 shrink-0">
              <div class="text-right">
                <p class="text-[9px] font-black uppercase tracking-tighter text-slate-400 mb-1">Získané Body</p>
                <div :class="lastGuess.points > 3500 ? 'text-green-600' : 'text-orange-500'" class="text-2xl font-black tabular-nums">+{{ lastGuess.points.toLocaleString() }}</div>
                <p class="text-[10px] font-bold text-slate-400">Odchylka: {{ lastGuess.distance }} km</p>
              </div>
              <button @click="currentRound < totalRoundsInGame ? (currentRound++, initRound()) : gameStatus = 'finished'" class="rounded-2xl bg-blue-600 px-8 py-4 font-bold text-white shadow-lg shadow-blue-200 hover:bg-blue-700 active:scale-95 transition-all">Pokračovat</button>
            </div>
          </div>
        </transition>
        <div v-if="gameStatus === 'finished'" class="absolute inset-0 z-[2000] flex items-center justify-center bg-slate-900/40 backdrop-blur-md px-4 text-center">
          <div class="w-full max-w-sm rounded-[3rem] border border-slate-100 bg-white p-12 shadow-2xl">
            <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center text-3xl mx-auto mb-6">🏆</div>
            <h2 class="text-sm font-bold uppercase tracking-[0.4em] text-blue-600 mb-2">Hra dokončena</h2>
            <div class="my-6"><p class="text-[10px] font-bold uppercase text-slate-400 mb-1 tracking-widest font-black">Konečné skóre</p><div class="text-7xl font-black text-slate-900 tracking-tighter">{{ score.toLocaleString() }}</div></div>
            <button @click="restartGame" class="w-full rounded-2xl bg-blue-600 py-5 text-xl font-bold text-white shadow-2xl hover:bg-blue-700 active:scale-95 transition">Hrát znovu</button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style>
html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
.leaflet-container { background: #f1f5f9 !important; cursor: crosshair !important; font-family: inherit; }
.marker-dot { width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
.card-pop-enter-active, .card-pop-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.card-pop-enter-from { transform: translateY(30px) scale(0.95); opacity: 0; }
.card-pop-leave-to { transform: translateY(50px); opacity: 0; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>