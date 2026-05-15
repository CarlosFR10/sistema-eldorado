<template>
  <section class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white">
      <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3">
        <RouterLink class="text-lg font-black text-eldorado-ink" to="/">Terminal El Dorado</RouterLink>
        <nav class="flex flex-wrap items-center gap-2 text-sm font-bold">
          <RouterLink class="rounded-md px-3 py-2 text-slate-700 hover:bg-teal-50" to="/registro">Registrarse</RouterLink>
          <RouterLink class="rounded-md px-3 py-2 text-slate-700 hover:bg-teal-50" to="/boleteria">Comprar boletos</RouterLink>
          <RouterLink class="btn btn-secondary" to="/login">Iniciar sesion</RouterLink>
        </nav>
      </div>
    </header>

    <main class="mx-auto grid max-w-7xl gap-4 px-4 py-5 lg:grid-cols-[380px_1fr]">
      <aside class="space-y-4">
        <section class="panel rounded-lg p-4">
          <p class="text-sm font-black uppercase text-eldorado-teal">Rastreo publico</p>
          <h1 class="text-2xl font-black text-slate-900">Rastrear bus</h1>
          <p class="mt-2 text-sm font-semibold text-slate-600">
            Ingresa codigo de boleto, codigo de viaje, placa o codigo GPS del bus.
          </p>
          <div class="mt-4 flex gap-2">
            <input v-model.trim="codigo" class="field" placeholder="Ej: BLT-... o 1234ABC" @keyup.enter="rastrear" />
            <button class="btn btn-primary" type="button" :disabled="loading" @click="rastrear">
              <Search :size="18" />
            </button>
          </div>
          <p v-if="error" class="mt-3 rounded-md bg-red-50 p-3 text-sm font-bold text-red-800">{{ error }}</p>
        </section>

        <section v-if="rastreo" class="panel rounded-lg p-4">
          <h2 class="mb-3 font-black text-slate-800">Estado del viaje</h2>
          <span class="chip" :class="estadoClass">{{ rastreo.estado_operativo }}</span>
          <p class="mt-3 text-sm font-bold text-slate-700">{{ rastreo.mensaje_estado }}</p>
          <dl class="mt-4 space-y-3 text-sm">
            <div>
              <dt class="font-bold text-slate-500">Bus</dt>
              <dd class="font-black text-slate-900">{{ rastreo.bus?.placa }} | {{ rastreo.bus?.marca }} {{ rastreo.bus?.modelo }}</dd>
            </div>
            <div>
              <dt class="font-bold text-slate-500">Ruta</dt>
              <dd>{{ rastreo.viaje?.ruta?.origen || '-' }} - {{ rastreo.viaje?.ruta?.destino || '-' }}</dd>
            </div>
            <div>
              <dt class="font-bold text-slate-500">Progreso</dt>
              <dd>
                <div class="flex items-center gap-2">
                  <div class="h-2 flex-1 rounded-full bg-slate-200">
                    <div
                      class="h-2 rounded-full bg-teal-600 transition-all"
                      :style="{ width: `${rastreo.progreso || 0}%` }"
                    />
                  </div>
                  <span class="text-xs font-bold">{{ Math.round(rastreo.progreso || 0) }}%</span>
                </div>
              </dd>
            </div>
            <div>
              <dt class="font-bold text-slate-500">Velocidad</dt>
              <dd class="font-black text-slate-900">{{ rastreo.ubicacion?.velocidad || 0 }} km/h</dd>
            </div>
            <div>
              <dt class="font-bold text-slate-500">Tiempo estimado</dt>
              <dd class="text-lg font-black text-teal-800">{{ rastreo.eta_minutos ? `${rastreo.eta_minutos} minutos` : 'Sin estimacion' }}</dd>
            </div>
            <div v-if="rastreo.ubicacion?.signal_loss" class="rounded-md bg-red-50 border border-red-200 p-2 text-sm text-red-800">
              <strong>Perdida de seal GPS:</strong> El bus esta fuera de cobertura.
            </div>
            <div>
              <dt class="font-bold text-slate-500">Waypoint actual</dt>
              <dd class="text-teal-700">{{ rastreo.waypoint_actual || '-' }}</dd>
            </div>
            <div>
              <dt class="font-bold text-slate-500">Siguiente</dt>
              <dd class="text-teal-700">{{ rastreo.waypoint_siguiente || '-' }}</dd>
            </div>
            <div>
              <dt class="font-bold text-slate-500">Ultimo reporte</dt>
              <dd>{{ fecha(rastreo.ubicacion?.timestamp) }}</dd>
            </div>
            <div v-if="rastreo.ubicacion?.simulada" class="rounded-md bg-amber-50 border border-amber-200 p-2 text-xs text-amber-800">
              <strong>Simulacion activa:</strong> Los datos son simulados para demo.
            </div>
          </dl>
        </section>
      </aside>

      <section class="panel rounded-lg p-4">
        <div class="mb-3 flex items-center justify-between gap-3">
          <h2 class="font-black text-slate-800">Ubicacion actual</h2>
          <div class="flex items-center gap-2">
            <span v-if="rastreo?.ubicacion?.simulada" class="chip bg-amber-100 text-amber-900">Simulada</span>
            <span v-if="rastreo?.ubicacion?.signal_loss" class="chip bg-red-100 text-red-800">Sin seal</span>
            <span v-if="rastreo?.velocidad_estimada" class="chip bg-teal-100 text-teal-900">~{{ rastreo.ubicacion?.velocidad }} km/h</span>
            <button v-if="rastreo?.viaje?.id" class="btn btn-secondary text-xs" :disabled="refreshing" @click="recargarRastreo">
              <RefreshCcw :size="14" :class="{ 'animate-spin': refreshing }" />
            </button>
          </div>
        </div>

        <MapaGps
          v-if="waypoints.length"
          :waypoints="waypoints"
          :bus-position="busPos"
          :signal-loss="!!rastreo?.ubicacion?.signal_loss"
          :progreso="rastreo?.progreso || 0"
          :velocidad="rastreo?.velocidad_estimada || rastreo?.ubicacion?.velocidad || 0"
        />

        <div v-else-if="rastreo" class="grid h-[620px] place-items-center rounded-lg border border-dashed border-slate-300 bg-slate-50 text-center">
          <div>
            <MapPinned class="mx-auto text-teal-700" :size="48" />
            <p class="mt-3 font-black text-slate-800">{{ rastreo.mensaje_estado || 'Viaje no iniciado' }}</p>
          </div>
        </div>

        <div v-else class="grid h-[620px] place-items-center rounded-lg border border-dashed border-slate-300 bg-slate-50 text-center">
          <div>
            <MapPinned class="mx-auto text-teal-700" :size="48" />
            <p class="mt-3 font-black text-slate-800">Busca un boleto o bus para ver el mapa.</p>
          </div>
        </div>
      </section>
    </main>
  </section>
</template>

<script setup>
import { MapPinned, RefreshCcw, Search } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { publicRastrear } from '../../api/public';
import MapaGps from '../../components/MapaGps.vue';

const route = useRoute();
const mapRef = ref(null);
const codigo = ref('');
const rastreo = ref(null);
const loading = ref(false);
const refreshing = ref(false);
const error = ref('');
let refreshInterval = null;

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

const waypoints = computed(() => {
  if (rastreo.value?.waypoints?.length) {
    return rastreo.value.waypoints;
  }
  if (!rastreo.value?.viaje?.ruta?.codigo) return [];
  const codigo = rastreo.value.viaje.ruta.codigo;
  return RUTAS_WAYPOINTS[codigo] || RUTAS_WAYPOINTS['CBB-LPZ'];
});

const routePoints = computed(() =>
  waypoints.value.map((w) => [w.lat, w.lng])
);

const busPos = computed(() => {
  if (!rastreo.value?.ubicacion?.latitud) return null;
  return {
    lat: Number(rastreo.value.ubicacion.latitud),
    lng: Number(rastreo.value.ubicacion.longitud),
  };
});

const center = computed(() => {
  if (waypoints.value.length >= 2) {
    const first = waypoints.value[0];
    const last = waypoints.value[waypoints.value.length - 1];
    return [(first.lat + last.lat) / 2, (first.lng + last.lng) / 2];
  }
  if (busPos.value) return [busPos.value.lat, busPos.value.lng];
  return [-17.3895, -66.1568];
});

const zoom = computed(() => {
  if (waypoints.value.length > 2) return 7;
  return 8;
});

const historial = computed(() => (rastreo.value?.historial || []).map((item) => [Number(item.latitud), Number(item.longitud)]));

const estadoClass = computed(() => {
  const estado = rastreo.value?.estado_operativo || '';
  if (estado.includes('ruta')) return 'bg-emerald-100 text-emerald-800';
  if (estado.includes('abordaje')) return 'bg-sky-100 text-sky-900';
  if (estado.includes('cancelado')) return 'bg-red-100 text-red-800';
  return 'bg-amber-100 text-amber-900';
});

onMounted(() => {
  if (route.query.codigo) {
    codigo.value = String(route.query.codigo);
    rastrear();
  }
});

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
    refreshInterval = null;
  }
});

async function rastrear() {
  error.value = '';
  rastreo.value = null;
  if (!codigo.value) {
    error.value = 'Ingresa un codigo para rastrear.';
    return;
  }

  loading.value = true;
  try {
    rastreo.value = (await publicRastrear(codigo.value)).data;
    iniciarAutoRefresh();
  } catch (err) {
    error.value = err.message || 'No se encontro rastreo para ese codigo.';
  } finally {
    loading.value = false;
  }
}

function iniciarAutoRefresh() {
  if (refreshInterval) {
    clearInterval(refreshInterval);
    refreshInterval = null;
  }
  // Siempre hacer polling si hay un viaje activo (no completado/cancelado)
  if (rastreo.value?.viaje?.id && rastreo.value?.estado_operativo !== 'Viaje finalizado' && rastreo.value?.estado_operativo !== 'Viaje cancelado') {
    refreshInterval = setInterval(recargarRastreo, 5000);
  }
}

async function recargarRastreo() {
  if (!codigo.value || refreshing.value) return;
  refreshing.value = true;
  try {
    const res = await publicRastrear(codigo.value);
    rastreo.value = res.data;
    // Solo parar polling si el viaje finalizado o cancelado
    const estado = rastreo.value?.estado_operativo || '';
    if ((estado === 'Viaje finalizado' || estado === 'Viaje cancelado') && refreshInterval) {
      clearInterval(refreshInterval);
      refreshInterval = null;
    }
  } catch {
  } finally {
    refreshing.value = false;
  }
}

function fecha(value) {
  return value ? new Date(value).toLocaleString('es-BO', { dateStyle: 'medium', timeStyle: 'short' }) : '-';
}
</script>