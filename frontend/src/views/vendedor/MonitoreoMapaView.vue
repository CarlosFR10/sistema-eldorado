<template>
  <section class="grid gap-4 lg:grid-cols-[380px_1fr]">
    <aside class="space-y-4">
      <section class="panel rounded-lg p-4">
        <div class="mb-3 flex items-center justify-between gap-3">
          <div>
            <p class="text-sm font-black uppercase text-eldorado-teal">GPS Simulator</p>
            <h1 class="text-xl font-black text-slate-900">Monitoreo de viaje</h1>
          </div>
          <button class="btn btn-secondary" :disabled="!viajeId" @click="recargar">
            <RefreshCcw :size="18" />
          </button>
        </div>
        <div v-if="viajeId" class="mt-2 space-y-2 text-sm">
          <p><span class="font-bold text-slate-500">Viaje ID:</span> {{ viajeId }}</p>
          <p v-if="estadoSim"><span class="font-bold text-slate-500">Estado:</span> {{ estadoSim.estado }}</p>
        </div>
        <p v-if="!viajeId" class="mt-2 text-sm text-slate-500">No hay viaje seleccionado.</p>
      </section>

      <section v-if="estadoSim" class="panel rounded-lg p-4">
        <div class="mb-3 flex items-center justify-between">
          <h2 class="font-black text-slate-800">Estado del viaje</h2>
          <span
            class="chip"
            :class="estadoSim.signal_loss ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800'"
          >
            {{ estadoSim.signal_loss ? 'Sin seal' : 'En linea' }}
          </span>
        </div>

        <div class="mb-4">
          <div class="flex justify-between text-sm font-bold text-slate-600 mb-1">
            <span>Progreso</span>
            <span>{{ Math.round(estadoSim.progreso || 0) }}%</span>
          </div>
          <div class="h-3 w-full rounded-full bg-slate-200">
            <div
              class="h-3 rounded-full bg-teal-600 transition-all duration-1000"
              :style="{ width: `${Math.round(estadoSim.progreso || 0)}%` }"
            />
          </div>
        </div>

        <dl class="grid grid-cols-2 gap-3 text-sm">
          <div>
            <dt class="font-bold text-slate-500">Velocidad</dt>
            <dd class="font-black text-slate-900">{{ estadoSim.velocidad || 0 }} km/h</dd>
          </div>
          <div>
            <dt class="font-bold text-slate-500">ETA</dt>
            <dd class="font-black text-slate-900">{{ estadoSim.eta_minutos || 0 }} min</dd>
          </div>
          <div class="col-span-2">
            <dt class="font-bold text-slate-500">Waypoint actual</dt>
            <dd class="font-black text-teal-700">{{ estadoSim.waypoint_actual || '-' }}</dd>
          </div>
          <div class="col-span-2">
            <dt class="font-bold text-slate-500">Siguiente</dt>
            <dd class="font-black text-teal-700">{{ estadoSim.waypoint_siguiente || '-' }}</dd>
          </div>
          <div class="col-span-2">
            <dt class="font-bold text-slate-500">Mensaje</dt>
            <dd class="font-medium text-slate-700">{{ estadoSim.mensaje || '-' }}</dd>
          </div>
        </dl>

        <div v-if="estadoSim.signal_loss" class="mt-3 rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-800">
          <strong>Perdida de seal GPS:</strong> El bus esta fuera de cobertura. Reintentando...
        </div>

        <div v-if="estadoSim.fin" class="mt-3 rounded-md bg-emerald-50 border border-emerald-200 p-3 text-sm text-emerald-800">
          <strong>Viaje finalizado!</strong> El bus llego a su destino.
        </div>

        <div v-if="errorMsg" class="mt-3 rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-800">
          {{ errorMsg }}
        </div>
      </section>

      <section v-else class="panel rounded-lg p-4 text-center text-slate-500">
        <p class="font-bold">No hay simulacion activa.</p>
        <p class="text-sm mt-1">Inicia un viaje desde Viajes Activos.</p>
      </section>

      <section class="panel rounded-lg p-4">
        <h2 class="mb-3 font-black text-slate-800">Waypoints de la ruta</h2>
        <div class="space-y-1">
          <div
            v-for="(wp, i) in waypoints"
            :key="i"
            class="flex items-center gap-2 text-sm p-2 rounded"
            :class="i === 0 ? 'bg-green-50' : i === waypoints.length - 1 ? 'bg-red-50' : 'bg-slate-50'"
          >
            <span class="text-lg">{{ i === 0 ? '🟢' : i === waypoints.length - 1 ? '🔴' : '⚪' }}</span>
            <span class="font-medium">{{ wp.nombre }}</span>
            <span class="text-slate-400 text-xs ml-auto">{{ wp.lat.toFixed(4) }}, {{ wp.lng.toFixed(4) }}</span>
          </div>
        </div>
      </section>
    </aside>

    <MapaGps
      v-if="mapReady"
      ref="mapRef"
      :key="mapKey"
      :waypoints="waypoints"
      :bus-position="busPosition"
      :signal-loss="estadoSim?.signal_loss || false"
      :progreso="estadoSim?.progreso || 0"
      :velocidad="estadoSim?.velocidad || 0"
    />
    <div v-else class="flex items-center justify-center h-[620px] rounded-lg border border-slate-200 bg-slate-50">
      <p class="text-slate-500">Cargando mapa...</p>
    </div>
  </section>
</template>

<script setup>
import { RefreshCcw } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import MapaGps from '../../components/MapaGps.vue';
import { estadoSimulacionGps, iniciarViajeGps, avanzarSimulacionGps } from '../../api/gps';

const route = useRoute();

const mapKey = ref(0);
const mapReady = ref(false);
const viajeId = ref(route.query.viaje ? Number(route.query.viaje) : null);
const viaje = ref(null);
const estadoSim = ref(null);
const waypoints = ref([]);
const busPosition = ref(null);
const isLoading = ref(false);
const errorMsg = ref('');
let pollInterval = null;

const RUTAS_WAYPOINTS = {
  'CBB-LPZ': [
    { lat: -17.3895, lng: -66.1568, nombre: 'Cochabamba' },
    { lat: -17.2500, lng: -66.3500, nombre: 'Oruro' },
    { lat: -16.5000, lng: -68.1500, nombre: 'El Alto' },
    { lat: -16.4890, lng: -68.1190, nombre: 'La Paz' },
  ],
  'CBB-SCZ': [
    { lat: -17.3895, lng: -66.1568, nombre: 'Cochabamba' },
    { lat: -17.1000, lng: -65.8000, nombre: 'Villa Tunari' },
    { lat: -16.5000, lng: -64.9000, nombre: 'Montero' },
    { lat: -17.8140, lng: -63.1710, nombre: 'Santa Cruz' },
  ],
  'CBB-ORU': [
    { lat: -17.3895, lng: -66.1568, nombre: 'Cochabamba' },
    { lat: -17.2500, lng: -66.3500, nombre: 'Oruro' },
  ],
  'CBB-PTS': [
    { lat: -17.3895, lng: -66.1568, nombre: 'Cochabamba' },
    { lat: -17.2500, lng: -66.3500, nombre: 'Oruro' },
    { lat: -19.5836, lng: -65.7556, nombre: 'Potosi' },
  ],
  'CBB-SRE': [
    { lat: -17.3895, lng: -66.1568, nombre: 'Cochabamba' },
    { lat: -18.2000, lng: -65.8000, nombre: 'Aiquile' },
    { lat: -19.0459, lng: -65.2594, nombre: 'Sucre' },
  ],
  'CBB-TJA': [
    { lat: -17.3895, lng: -66.1568, nombre: 'Cochabamba' },
    { lat: -19.0459, lng: -65.2594, nombre: 'Sucre' },
    { lat: -19.6000, lng: -64.9000, nombre: 'Camargo' },
    { lat: -21.5355, lng: -64.7299, nombre: 'Tarija' },
  ],
  'CBB-TDD': [
    { lat: -17.3895, lng: -66.1568, nombre: 'Cochabamba' },
    { lat: -16.9000, lng: -65.5000, nombre: 'Villa Tunari' },
    { lat: -15.8000, lng: -64.8000, nombre: 'San Ignacio de Moxos' },
    { lat: -14.8333, lng: -64.9000, nombre: 'Trinidad' },
  ],
  'CBB-CIJ': [
    { lat: -17.3895, lng: -66.1568, nombre: 'Cochabamba' },
    { lat: -14.8333, lng: -64.9000, nombre: 'Trinidad' },
    { lat: -11.1000, lng: -66.4000, nombre: 'Riberalta' },
    { lat: -11.0267, lng: -68.7347, nombre: 'Cobija' },
  ],
};

onMounted(async () => {
  if (viajeId.value) {
    await cargarViaje();
    if (!estadoSim.value?.fin) {
      iniciarPoll();
    }
  }
  setTimeout(() => { mapReady.value = true; }, 100);
});

onUnmounted(() => {
  detenerPoll();
});

function iniciarPoll() {
  detenerPoll();
  pollInterval = window.setInterval(pollAvanzar, 2000);
}

function detenerPoll() {
  if (pollInterval) {
    window.clearInterval(pollInterval);
    pollInterval = null;
  }
}

async function pollAvanzar() {
  if (!viajeId.value || isLoading.value) return;
  isLoading.value = true;
  try {
    const res = await avanzarSimulacionGps(viajeId.value);
    estadoSim.value = res.data;

    if (!res.data.fin) {
      busPosition.value = { lat: res.data.latitud, lng: res.data.longitud };
    } else {
      detenerPoll();
    }
  } catch (e) {
    console.error('Poll avanzar error:', e);
    detenerPoll();
  } finally {
    isLoading.value = false;
  }
}

async function cargarViaje() {
  if (!viajeId.value) return;
  errorMsg.value = '';
  try {
    const res = await estadoSimulacionGps(viajeId.value);
    estadoSim.value = res.data;

    if (res.data?.waypoints?.length) {
      waypoints.value = res.data.waypoints;
    } else {
      waypoints.value = RUTAS_WAYPOINTS['CBB-LPZ'];
    }

    if (res.data?.latitud && res.data?.longitud) {
      busPosition.value = { lat: res.data.latitud, lng: res.data.longitud };
    } else if (res.data?.waypoints?.length) {
      busPosition.value = { lat: res.data.waypoints[0].lat, lng: res.data.waypoints[0].lng };
    }

    if (!res.data?.fin && res.data?.estado === 'en_ruta') {
      iniciarPoll();
    }
  } catch (e) {
    errorMsg.value = 'No se pudo cargar el estado del viaje.';
    console.error(' cargarViaje error:', e);
  }
}

async function recargar() {
  await cargarViaje();
}

function seleccionarBus(item) {
  if (item.viaje?.id) {
    detenerPoll();
    viajeId.value = item.viaje.id;
    viaje.value = item.viaje;
    mapKey.value++;
    cargarViaje();
  }
}

watch(() => route.query.viaje, (newId) => {
  if (newId) {
    detenerPoll();
    viajeId.value = Number(newId);
    estadoSim.value = null;
    busPosition.value = null;
    waypoints.value = [];
    mapKey.value++;
    cargarViaje();
  }
});
</script>