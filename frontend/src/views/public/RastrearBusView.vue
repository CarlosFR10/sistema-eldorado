<template>
  <section class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white">
      <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3">
        <RouterLink class="text-lg font-black text-slate-800" to="/">Terminal El Dorado</RouterLink>
        <nav class="flex flex-wrap items-center gap-2 text-sm font-bold">
          <RouterLink class="rounded-lg px-4 py-2 text-slate-600 hover:bg-slate-100" to="/registro">Registrarse</RouterLink>
          <RouterLink class="rounded-lg px-4 py-2 text-slate-600 hover:bg-slate-100" to="/boleteria">Comprar boletos</RouterLink>
          <RouterLink class="btn btn-secondary" to="/login">Iniciar sesion</RouterLink>
        </nav>
      </div>
    </header>

    <main class="mx-auto grid max-w-7xl gap-6 px-4 py-6 lg:grid-cols-[400px_1fr]">
      <aside class="space-y-4">
        <section class="card p-5">
          <p class="text-sm font-black uppercase text-blue-600">Rastreo publico</p>
          <h1 class="mt-2 text-2xl font-black text-slate-800">Rastrear bus</h1>
          <p class="mt-2 text-sm text-slate-600">
            Ingresa codigo de boleto, codigo de viaje, placa o codigo GPS del bus.
          </p>
          <div class="mt-4 flex gap-2">
            <input v-model.trim="codigo" class="form-input" placeholder="Ej: BLT-... o 1234ABC" @keyup.enter="rastrear" />
            <button class="btn-primary" type="button" :disabled="loading" @click="rastrear">
              <Search :size="18" />
            </button>
          </div>
          <div v-if="error" class="alert alert-error">{{ error }}</div>
        </section>

        <section v-if="rastreo" class="card p-5">
          <h2 class="mb-3 font-bold text-lg text-slate-800">Estado del viaje</h2>
          <span class="badge" :class="estadoClass">{{ rastreo.estado_operativo }}</span>
          <p class="mt-3 text-sm font-medium text-slate-700">{{ rastreo.mensaje_estado }}</p>
          <dl class="mt-4 space-y-3 text-sm">
            <div>
              <dt class="font-medium text-slate-500">Bus</dt>
              <dd class="font-bold text-slate-900">{{ rastreo.bus?.placa }} | {{ rastreo.bus?.marca }} {{ rastreo.bus?.modelo }}</dd>
            </div>
            <div>
              <dt class="font-medium text-slate-500">Ruta</dt>
              <dd class="text-slate-700">{{ rastreo.viaje?.ruta?.origen || '-' }} - {{ rastreo.viaje?.ruta?.destino || '-' }}</dd>
            </div>
            <div>
              <dt class="font-medium text-slate-500">Progreso</dt>
              <dd>
                <div class="flex items-center gap-2">
                  <div class="h-2 flex-1 rounded-full bg-slate-200">
                    <div
                      class="h-2 rounded-full bg-blue-600 transition-all"
                      :style="{ width: `${rastreo.progreso || 0}%` }"
                    />
                  </div>
                  <span class="text-xs font-bold text-slate-600">{{ Math.round(rastreo.progreso || 0) }}%</span>
                </div>
              </dd>
            </div>
            <div>
              <dt class="font-medium text-slate-500">Velocidad</dt>
              <dd class="font-bold text-slate-900">{{ rastreo.ubicacion?.velocidad || 0 }} km/h</dd>
            </div>
            <div>
              <dt class="font-medium text-slate-500">Tiempo estimado</dt>
              <dd class="text-lg font-black text-blue-600">{{ rastreo.eta_minutos ? `${rastreo.eta_minutos} minutos` : 'Sin estimacion' }}</dd>
            </div>
            <div v-if="rastreo.ubicacion?.signal_loss" class="rounded-xl border border-red-200 bg-red-50 p-2 text-sm text-red-800">
              <strong>Perdida de seal GPS:</strong> El bus esta fuera de cobertura.
            </div>
            <div>
              <dt class="font-medium text-slate-500">Waypoint actual</dt>
              <dd class="text-blue-700">{{ rastreo.waypoint_actual || '-' }}</dd>
            </div>
            <div>
              <dt class="font-medium text-slate-500">Siguiente</dt>
              <dd class="text-blue-700">{{ rastreo.waypoint_siguiente || '-' }}</dd>
            </div>
            <div>
              <dt class="font-medium text-slate-500">Ultimo reporte</dt>
              <dd class="text-slate-700">{{ fecha(rastreo.ubicacion?.timestamp) }}</dd>
            </div>
            <div v-if="rastreo.ubicacion?.simulada" class="rounded-xl border border-amber-200 bg-amber-50 p-2 text-xs text-amber-800">
              <strong>Simulacion activa:</strong> Los datos son simulados para demo.
            </div>
          </dl>
        </section>
      </aside>

      <section class="card p-5">
        <div class="mb-4 flex items-center justify-between gap-3">
          <h2 class="font-bold text-lg text-slate-800">Ubicacion actual</h2>
          <div class="flex items-center gap-2">
            <span v-if="rastreo?.ubicacion?.simulada" class="badge badge-amber">Simulada</span>
            <span v-if="rastreo?.ubicacion?.signal_loss" class="badge badge-red">Sin seal</span>
            <span v-if="rastreo?.velocidad_estimada" class="badge badge-blue">{{ rastreo.ubicacion?.velocidad }} km/h</span>
            <button v-if="rastreo?.viaje?.id" class="btn-icon" :disabled="refreshing" @click="recargarRastreo">
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

        <div v-else-if="rastreo" class="grid h-[580px] place-items-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 text-center">
          <div>
            <MapPinned class="mx-auto text-blue-600" :size="48" />
            <p class="mt-3 font-bold text-slate-800">{{ rastreo.mensaje_estado || 'Viaje no iniciado' }}</p>
          </div>
        </div>

        <div v-else class="grid h-[580px] place-items-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 text-center">
          <div>
            <MapPinned class="mx-auto text-slate-400" :size="48" />
            <p class="mt-3 font-bold text-slate-600">Busca un boleto o bus para ver el mapa.</p>
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

const busPos = computed(() => {
  if (!rastreo.value?.ubicacion?.latitud) return null;
  return {
    lat: Number(rastreo.value.ubicacion.latitud),
    lng: Number(rastreo.value.ubicacion.longitud),
  };
});

const estadoClass = computed(() => {
  const estado = rastreo.value?.estado_operativo || '';
  if (estado.includes('ruta')) return 'badge-green';
  if (estado.includes('abordaje')) return 'badge-blue';
  if (estado.includes('cancelado')) return 'badge-red';
  return 'badge-amber';
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

<style scoped>
.card {
  @apply bg-white rounded-xl border border-slate-200;
}

.form-input {
  @apply w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.btn-primary {
  @apply flex items-center justify-center gap-2 p-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors;
}

.btn-secondary {
  @apply flex items-center justify-center gap-2 py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-colors text-sm;
}

.btn-icon {
  @apply p-2 rounded-lg hover:bg-slate-100 text-slate-500;
}

.alert {
  @apply mt-3 p-3 rounded-xl text-sm font-medium;
}

.alert-error {
  @apply bg-red-50 text-red-700;
}

.badge {
  @apply inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full;
}

.badge-green {
  @apply bg-green-100 text-green-700;
}

.badge-blue {
  @apply bg-blue-100 text-blue-700;
}

.badge-amber {
  @apply bg-amber-100 text-amber-700;
}

.badge-red {
  @apply bg-red-100 text-red-700;
}
</style>
