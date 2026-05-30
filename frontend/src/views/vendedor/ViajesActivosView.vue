<template>
  <section class="space-y-6 dark:text-white">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-sm font-bold uppercase text-blue-600">Salidas disponibles</p>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Viajes activos</h1>
      </div>
      <div class="flex flex-wrap gap-2">
        <RouterLink class="btn-primary" to="/venta/viajes/nuevo">
          <Plus :size="18" />
          Agregar viaje
        </RouterLink>
        <button class="btn-secondary" @click="cargar">
          <RefreshCcw :size="18" />
          Actualizar
        </button>
      </div>
    </div>

    <section class="card p-5">
      <div class="flex gap-3 items-center">
        <input v-model="filters.fecha" class="form-input dark:text-white" type="date" @change="cargar" />
        <select v-model="filters.estado" class="form-input dark:text-white" @change="cargar">
          <option value="en_venta">En venta</option>
          <option value="en_ruta">En ruta</option>
          <option value="completado">Finalizados</option>
        </select>
      </div>
      <div v-if="message" class="alert alert-success mt-3">{{ message }}</div>
      <div v-if="error" class="alert alert-error mt-3">{{ error }}</div>
    </section>

    <div v-if="viajeStore.viajes.length" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <article
        v-for="viaje in viajeStore.viajes"
        :key="viaje.id"
        class="card p-5 cursor-pointer hover:border-blue-300 dark:hover:border-teal-500 transition-colors"
        @click="abrirModalMapa(viaje)"
      >
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-xs font-bold uppercase text-blue-600">{{ viaje.codigo_viaje }}</p>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ viaje.ruta?.destino }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ viaje.ruta?.origen }} - {{ viaje.ruta?.codigo }}</p>
          </div>
          <div class="flex flex-col items-end gap-1">
            <EstadoSemaforo :estado="viaje.estado" />
            <span v-if="viaje.estado === 'en_venta'" class="text-xs font-medium text-slate-400 dark:text-slate-500">No ha partido</span>
            <span v-else-if="viaje.estado === 'completado'" class="text-xs font-medium text-green-600 dark:text-green-400">Finalizado</span>
          </div>
        </div>
        <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
          <div>
            <dt class="font-medium text-slate-500 dark:text-slate-400">Salida</dt>
            <dd class="font-semibold text-slate-800 dark:text-white">{{ hora(viaje.fecha_salida) }}</dd>
          </div>
          <div>
            <dt class="font-medium text-slate-500 dark:text-slate-400">Precio</dt>
            <dd class="font-semibold text-slate-800 dark:text-white">Bs {{ viaje.precio_final }}</dd>
          </div>
          <div>
            <dt class="font-medium text-slate-500 dark:text-slate-400">Bus</dt>
            <dd class="text-slate-700 dark:text-slate-300">{{ viaje.bus?.placa }}</dd>
          </div>
          <div>
            <dt class="font-medium text-slate-500 dark:text-slate-400">Libres</dt>
            <dd class="text-slate-700 dark:text-slate-300">{{ viaje.asientos_disponibles_count ?? '-' }}</dd>
          </div>
        </dl>
        <div class="mt-4 grid gap-2" @click.stop>
          <RouterLink
            v-if="viaje.estado === 'en_venta'"
            class="btn-secondary text-center"
            :to="{ name: 'venta', query: { viaje: viaje.id, fecha: filters.fecha } }"
          >
            Vender boleto
          </RouterLink>
          <button
            v-if="viaje.estado === 'en_venta'"
            class="btn-primary"
            type="button"
            :disabled="procesandoId === viaje.id"
            @click="iniciarViaje(viaje)"
          >
            <Navigation :size="18" />
            Iniciar viaje
          </button>
        </div>
      </article>
    </div>

    <div v-else class="card p-5 text-sm font-medium text-slate-600 dark:text-slate-300 text-center">
      No hay viajes registrados para la fecha seleccionada.
    </div>

    <div v-if="mostrarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="cerrarModal">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between p-5 border-b border-slate-200 dark:border-slate-700">
          <div>
            <p class="text-xs font-bold uppercase text-blue-600">{{ viajeSeleccionado?.codigo_viaje }}</p>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">{{ viajeSeleccionado?.ruta?.origen }} → {{ viajeSeleccionado?.ruta?.destino }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ viajeSeleccionado?.bus?.placa }} | {{ viajeSeleccionado?.estado }}</p>
          </div>
          <button class="btn-secondary p-2" @click="cerrarModal">
            <X :size="18" />
          </button>
        </div>

        <div class="p-5 flex-1 overflow-auto" style="min-height: 500px;">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div class="bg-slate-50 dark:bg-slate-700 rounded-xl p-3 text-center">
              <p class="text-xs text-slate-500 dark:text-slate-400">Progreso</p>
              <p class="text-2xl font-bold text-blue-600">{{ Math.round(modalProgreso) }}%</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-700 rounded-xl p-3 text-center">
              <p class="text-xs text-slate-500 dark:text-slate-400">Velocidad</p>
              <p class="text-2xl font-bold text-slate-700 dark:text-white">{{ modalVelocidad }} km/h</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-700 rounded-xl p-3 text-center">
              <p class="text-xs text-slate-500 dark:text-slate-400">ETA</p>
              <p class="text-2xl font-bold text-slate-700 dark:text-white">{{ modalEta }} min</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-700 rounded-xl p-3 text-center">
              <p class="text-xs text-slate-500 dark:text-slate-400">Estado GPS</p>
              <p class="text-lg font-bold" :class="modalSignalLoss ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                {{ modalSignalLoss ? 'Sin seal' : 'En linea' }}
              </p>
            </div>
          </div>

          <div class="rounded-xl overflow-hidden border border-slate-200 relative" style="height: 450px;">
            <LMap
              v-if="mostrarModal"
              ref="modalMapRef"
              :zoom="modalZoom"
              :center="modalCenter"
              :use-global-leaflet="false"
              style="height: 100%; width: 100%;"
            >
              <LTileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" attribution="&copy; OpenStreetMap contributors" />

              <LMarker
                v-for="(wp, i) in modalWaypoints"
                :key="'wp-' + i"
                :lat-lng="[wp.lat, wp.lng]"
                :icon="getModalWaypointIcon(i)"
              />

              <LPolyline
                v-if="modalRoutePoints.length"
                :lat-lngs="modalRoutePoints"
                :color="modalSignalLoss ? '#94a3b8' : '#2563eb'"
                :weight="4"
                :opacity="0.8"
                :dash-array="modalSignalLoss ? '15, 10' : undefined"
              />

              <LMarker
                v-if="modalBusPosition"
                :lat-lng="[modalBusPosition.lat, modalBusPosition.lng]"
                :icon="modalBusIcon"
              />

              <div class="absolute bottom-3 left-3 right-3 bg-white/95 dark:bg-slate-800/95 backdrop-blur rounded-xl p-3 shadow-lg border border-slate-200 dark:border-slate-700 z-[1000]">
                <div class="flex items-center justify-between gap-4">
                  <div class="flex-1">
                    <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Progreso del viaje</div>
                    <div class="h-3 bg-slate-200 dark:bg-slate-600 rounded-full overflow-hidden">
                      <div
                        class="h-full transition-all duration-500 ease-out rounded-full bg-blue-600"
                        :style="{ width: Math.round(modalProgreso) + '%' }"
                      ></div>
                    </div>
                  </div>
                  <div class="text-center px-4">
                    <div class="text-2xl font-bold" :class="modalSignalLoss ? 'text-slate-400 dark:text-slate-500' : 'text-blue-600'">{{ Math.round(modalProgreso) }}%</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ Math.round(modalProgreso * 30 / 100) }}/30 pasos</div>
                  </div>
                  <div class="text-center">
                    <div class="text-xl font-bold text-slate-700 dark:text-white">{{ modalVelocidad }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">km/h</div>
                  </div>
                  <div class="text-center">
                    <div class="text-xl font-bold text-slate-700 dark:text-white">{{ modalEta }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">min ETA</div>
                  </div>
                </div>
              </div>

              <div v-if="modalSignalLoss" class="absolute top-3 left-1/2 transform -translate-x-1/2 bg-red-500 text-white px-4 py-2 rounded-full text-sm font-bold shadow-lg animate-pulse z-[1000]">
                Senal GPS perdida
              </div>
            </LMap>
          </div>

          <div class="mt-4 flex flex-wrap gap-2">
            <button
              v-if="['en_venta', 'abordando'].includes(viajeSeleccionado?.estado)"
              class="btn-primary"
              :disabled="procesandoId === viajeSeleccionado?.id"
              @click="iniciarViaje(viajeSeleccionado)"
            >
              <Navigation :size="18" />
              Iniciar viaje
            </button>
            <RouterLink
              v-if="viajeSeleccionado?.estado === 'en_ruta'"
              class="btn-primary"
              :to="{ name: 'monitoreo-gps', query: { viaje: viajeSeleccionado.id } }"
            >
              <Maximize2 :size="18" />
              Ver en pantalla completa
            </RouterLink>
            <button
              v-if="!['completado', 'cancelado'].includes(viajeSeleccionado?.estado)"
              class="btn-secondary"
              @click="finalizarViaje(viajeSeleccionado); cerrarModal()"
            >
              <CheckCircle2 :size="18" />
              Finalizar viaje
            </button>
            <button
              v-if="viajeSeleccionado?.estado !== 'cancelado' && viajeSeleccionado?.estado !== 'en_ruta'"
              class="btn-secondary"
              @click="cancelarViaje(viajeSeleccionado); cerrarModal()"
            >
              <Trash2 :size="18" />
              Cancelar viaje
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { CheckCircle2, Maximize2, Navigation, Plus, RefreshCcw, Trash2, X } from 'lucide-vue-next';
import { LMap, LMarker, LPolyline, LTileLayer } from '@vue-leaflet/vue-leaflet';
import L from 'leaflet';
import { onMounted, onUnmounted, reactive, ref, computed } from 'vue';
import EstadoSemaforo from '../../components/EstadoSemaforo.vue';
import { cambiarEstadoViaje } from '../../api/viajes';
import { useViajeStore } from '../../stores/viaje';
import { estadoSimulacionGps, iniciarViajeGps } from '../../api/gps';

const viajeStore = useViajeStore();
const filters = reactive({ fecha: new Date().toISOString().slice(0, 10), estado: 'en_venta' });
const procesandoId = ref(null);
const message = ref('');
const error = ref('');
const waypointsPorViaje = ref({});
const routePointsPorViaje = ref({});
const busPositions = ref({});
const mapCenters = ref({});

const mostrarModal = ref(false);
const viajeSeleccionado = ref(null);
const modalMapRef = ref(null);
const modalBusPosition = ref(null);
const modalWaypoints = ref([]);
const modalRoutePoints = ref([]);
const modalProgreso = ref(0);
const modalVelocidad = ref(0);
const modalEta = ref(0);
const modalSignalLoss = ref(false);
const modalCenter = ref([-17.3895, -66.1568]);
const modalZoom = ref(7);
let modalRefreshInterval = null;

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

function getModalWaypointIcon(index) {
  const isStart = index === 0;
  const isEnd = index === modalWaypoints.value.length - 1;

  let svg;
  if (isStart) {
    svg = `<svg width="32" height="40" viewBox="0 0 32 40" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M16 0L32 10V30L16 40L0 30V10L16 0Z" fill="#10B981" stroke="#059669" stroke-width="2"/>
      <circle cx="16" cy="18" r="7" fill="white"/>
      <path d="M16 11V25M11 18H21" stroke="#10B981" stroke-width="2.5" stroke-linecap="round"/>
    </svg>`;
  } else if (isEnd) {
    svg = `<svg width="32" height="40" viewBox="0 0 32 40" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M16 0L32 10V30L16 40L0 30V10L16 0Z" fill="#EF4444" stroke="#DC2626" stroke-width="2"/>
      <rect x="8" y="12" width="16" height="16" rx="2" fill="white"/>
      <path d="M12 16H20M12 20H20M12 24H20" stroke="#EF4444" stroke-width="1.5"/>
    </svg>`;
  } else {
    svg = `<svg width="28" height="36" viewBox="0 0 28 36" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="14" cy="14" r="12" fill="#94A3B8" stroke="#64748B" stroke-width="2"/>
      <circle cx="14" cy="14" r="5" fill="white"/>
    </svg>`;
  }

  const wpName = modalWaypoints.value[index]?.nombre || '';
  const html = `<div style="display:flex;flex-direction:column;align-items:center;">
    ${svg}
    <span style="margin-top:4px;font-size:11px;font-weight:700;background:rgba(255,255,255,0.95);padding:2px 6px;border-radius:4px;color:#334155;white-space:nowrap;box-shadow:0 1px 3px rgba(0,0,0,0.15);">${wpName}</span>
  </div>`;

  return L.divIcon({ html, className: 'waypoint-icon', iconSize: [40, 50], iconAnchor: [20, 38] });
}

const modalBusIcon = computed(() => {
  const color = modalSignalLoss.value ? '#94A3B8' : '#2563eb';
  const bgBadge = modalSignalLoss.value ? '#EF4444' : '#2563eb';
  const badgeText = modalSignalLoss.value ? 'Sin seal' : `${Math.round(modalProgreso.value)}%`;

  const html = `<div style="display:flex;flex-direction:column;align-items:center;">
    <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
      <rect x="2" y="8" width="40" height="28" rx="6" fill="${color}" stroke="#2563eb" stroke-width="2"/>
      <rect x="6" y="12" width="8" height="8" rx="2" fill="white" opacity="0.9"/>
      <rect x="18" y="12" width="8" height="8" rx="2" fill="white" opacity="0.9"/>
      <rect x="30" y="12" width="8" height="8" rx="2" fill="white" opacity="0.9"/>
      <rect x="0" y="32" width="44" height="6" rx="3" fill="#6B7280"/>
      <circle cx="12" cy="38" r="4" fill="#1F2937"/>
      <circle cx="12" cy="38" r="1.5" fill="#9CA3AF"/>
      <circle cx="32" cy="38" r="4" fill="#1F2937"/>
      <circle cx="32" cy="38" r="1.5" fill="#9CA3AF"/>
      <text x="22" y="26" text-anchor="middle" fill="white" font-size="10" font-weight="bold" font-family="Arial">${Math.round(modalProgreso.value)}%</text>
    </svg>
    <span style="margin-top:2px;font-size:10px;font-weight:700;background:${bgBadge};color:white;padding:2px 6px;border-radius:10px;white-space:nowrap;box-shadow:0 1px 3px rgba(0,0,0,0.3);">${badgeText}</span>
  </div>`;

  return L.divIcon({ html, className: 'bus-icon', iconSize: [50, 58], iconAnchor: [25, 28] });
});

onMounted(async () => {
  await viajeStore.cargarCatalogos();
  await cargar();
  setInterval(async () => { await cargarEstadosGps(); }, 8000);
});

async function cargar() {
  await viajeStore.cargarViajes({ fecha: filters.fecha, estado: filters.estado || undefined, per_page: 50 });
  if (filters.estado === 'en_ruta' || filters.estado === '') {
    await cargarEstadosGps();
  }
}

async function cargarEstadosGps() {
  const enRuta = viajeStore.viajes.filter(v => v.estado === 'en_ruta');
  for (const viaje of enRuta) {
    try {
      const data = await estadoSimulacionGps(viaje.id);

      if (data.success === false) {
        const codigo = viaje.ruta?.codigo || 'CBB-LPZ';
        const wps = RUTAS_WAYPOINTS[codigo] || RUTAS_WAYPOINTS['CBB-LPZ'];
        waypointsPorViaje.value[viaje.id] = wps;
        routePointsPorViaje.value[viaje.id] = wps.map(w => [w.lat, w.lng]);
        busPositions.value[viaje.id] = { lat: wps[0].lat, lng: wps[0].lng };
        if (wps.length >= 2) {
          const first = wps[0];
          const last = wps[wps.length - 1];
          mapCenters.value[viaje.id] = [(first.lat + last.lat) / 2, (first.lng + last.lng) / 2];
        }
        continue;
      }

      const codigo = viaje.ruta?.codigo || 'CBB-LPZ';
      const wps = data.waypoints || RUTAS_WAYPOINTS[codigo] || RUTAS_WAYPOINTS['CBB-LPZ'];

      waypointsPorViaje.value[viaje.id] = wps;
      routePointsPorViaje.value[viaje.id] = wps.map(w => [w.lat, w.lng]);

      if (data.latitud && data.longitud) {
        busPositions.value[viaje.id] = { lat: data.latitud, lng: data.longitud };
      } else if (wps.length) {
        busPositions.value[viaje.id] = { lat: wps[0].lat, lng: wps[0].lng };
      }

      if (wps.length >= 2) {
        const first = wps[0];
        const last = wps[wps.length - 1];
        mapCenters.value[viaje.id] = [(first.lat + last.lat) / 2, (first.lng + last.lng) / 2];
      }
    } catch (e) {
      const codigo = viaje.ruta?.codigo || 'CBB-LPZ';
      const wps = RUTAS_WAYPOINTS[codigo] || RUTAS_WAYPOINTS['CBB-LPZ'];
      waypointsPorViaje.value[viaje.id] = wps;
      routePointsPorViaje.value[viaje.id] = wps.map(w => [w.lat, w.lng]);
      busPositions.value[viaje.id] = { lat: wps[0].lat, lng: wps[0].lng };
      if (wps.length >= 2) {
        const first = wps[0];
        const last = wps[wps.length - 1];
        mapCenters.value[viaje.id] = [(first.lat + last.lat) / 2, (first.lng + last.lng) / 2];
      }
    }
  }
}

async function iniciarViaje(viaje) {
  error.value = '';
  message.value = '';
  procesandoId.value = viaje.id;
  try {
    const data = await iniciarViajeGps(viaje.id);
    if (data.success === false) {
      throw new Error(data.message || 'Error al iniciar viaje');
    }
    message.value = data.mensaje || 'Simulacion iniciada!';

    viaje.estado = 'en_ruta';
    if (data.waypoints) {
      waypointsPorViaje.value[viaje.id] = data.waypoints;
      routePointsPorViaje.value[viaje.id] = data.waypoints.map(w => [w.lat, w.lng]);
      if (data.waypoints.length >= 2) {
        const first = data.waypoints[0];
        const last = data.waypoints[data.waypoints.length - 1];
        mapCenters.value[viaje.id] = [(first.lat + last.lat) / 2, (first.lng + last.lng) / 2];
      }
    }

    viajeSeleccionado.value = { ...viaje };
    abrirModalMapa(viaje);
  } catch (err) {
    error.value = err.message || 'No se pudo iniciar el viaje.';
  } finally {
    procesandoId.value = null;
  }
}

async function finalizarViaje(viaje) {
  await cambiarEstado(viaje, 'completado', 'Viaje finalizado correctamente.');
}

async function cancelarViaje(viaje) {
  await cambiarEstado(viaje, 'cancelado', 'Quitar de activos correctamente.');
}

async function cambiarEstado(viaje, estado, texto) {
  error.value = '';
  message.value = '';
  procesandoId.value = viaje.id;

  try {
    await cambiarEstadoViaje(viaje.id, { estado });
    message.value = texto;
    await cargar();
  } catch (err) {
    error.value = err.message || 'No se pudo actualizar el viaje.';
  } finally {
    procesandoId.value = null;
  }
}

function hora(value) {
  return value ? new Date(value).toLocaleString('es-BO', { dateStyle: 'medium', timeStyle: 'short', hour12: true }) : '--:--';
}

async function abrirModalMapa(viaje) {
  viajeSeleccionado.value = viaje;
  mostrarModal.value = true;

  const codigo = viaje.ruta?.codigo || 'CBB-LPZ';
  const wps = RUTAS_WAYPOINTS[codigo] || RUTAS_WAYPOINTS['CBB-LPZ'];
  modalWaypoints.value = wps;
  modalRoutePoints.value = wps.map(w => [w.lat, w.lng]);

  if (wps.length >= 2) {
    const first = wps[0];
    const last = wps[wps.length - 1];
    modalCenter.value = [(first.lat + last.lat) / 2, (first.lng + last.lng) / 2];
    modalZoom.value = wps.length > 2 ? 7 : 8;
  }

  if (viaje.estado === 'en_ruta') {
    modalProgreso.value = 0;
    modalVelocidad.value = 0;
    modalEta.value = 0;
    modalSignalLoss.value = false;
    await cargarDatosModal(viaje.id);
    iniciarModalRefresh();
  } else {
    modalBusPosition.value = { lat: wps[0].lat, lng: wps[0].lng };
    modalProgreso.value = 0;
    modalVelocidad.value = 0;
    modalEta.value = 0;
    modalSignalLoss.value = false;
  }
}

async function cargarDatosModal(viajeId) {
  try {
    const res = await estadoSimulacionGps(viajeId);
    const data = res?.data ?? res;

    if (!res || res.success === false) {
      return;
    }

    if (data.waypoints?.length) {
      modalWaypoints.value = data.waypoints;
      modalRoutePoints.value = data.waypoints.map(w => [w.lat, w.lng]);
      if (data.waypoints.length >= 2) {
        const first = data.waypoints[0];
        const last = data.waypoints[data.waypoints.length - 1];
        modalCenter.value = [(first.lat + last.lat) / 2, (first.lng + last.lng) / 2];
      }
    }

    if (data.latitud !== undefined && data.latitud !== null) {
      modalBusPosition.value = { lat: data.latitud, lng: data.longitud };
    }

    if (data.progreso !== undefined && data.progreso !== null) {
      modalProgreso.value = data.progreso;
    } else if (data.llamada_actual !== undefined && data.llamadas_totales !== undefined) {
      modalProgreso.value = (data.llamada_actual / data.llamadas_totales) * 100;
    }
    if (data.velocidad !== undefined && data.velocidad !== null) {
      modalVelocidad.value = data.velocidad;
    }
    if (data.eta_minutos !== undefined && data.eta_minutos !== null) {
      modalEta.value = data.eta_minutos;
    }
    if (data.signal_loss !== undefined && data.signal_loss !== null) {
      modalSignalLoss.value = data.signal_loss;
    }
  } catch (e) {
    console.warn('Error cargando datos modal:', e?.message || e);
  }
}

async function pollAvanzar() {
  if (!viajeSeleccionado.value?.id) return;
  try {
    const res = await estadoSimulacionGps(viajeSeleccionado.value.id);
    const data = res?.data ?? res;

    if (!res || res.success === false) {
      return;
    }

    if (data.waypoints?.length) {
      modalWaypoints.value = data.waypoints;
      modalRoutePoints.value = data.waypoints.map(w => [w.lat, w.lng]);
    }

    if (data.latitud !== undefined && data.latitud !== null) {
      modalBusPosition.value = { lat: data.latitud, lng: data.longitud };
    }

    const rawProgreso = data.progreso ?? (data.llamada_actual && data.llamadas_totales ? (data.llamada_actual / data.llamadas_totales) * 100 : 0);
    const progresoNum = parseFloat(rawProgreso);
    modalProgreso.value = isNaN(progresoNum) ? 0 : progresoNum;

    if (data.velocidad !== undefined && data.velocidad !== null) {
      modalVelocidad.value = Number(data.velocidad) || 0;
    }
    if (data.eta_minutos !== undefined && data.eta_minutos !== null) {
      modalEta.value = Number(data.eta_minutos) || 0;
    }
    if (data.signal_loss !== undefined && data.signal_loss !== null) {
      modalSignalLoss.value = Boolean(data.signal_loss);
    }

    if (data.fin) {
      viajeSeleccionado.value.estado = 'completado';
      modalProgreso.value = 100;
      detenerModalRefresh();
    }
  } catch (e) {
    console.warn('Error poll avanzar:', e?.message || e);
  }
}

function iniciarModalRefresh() {
  detenerModalRefresh();
  if (viajeSeleccionado.value?.estado === 'en_ruta') {
    modalRefreshInterval = setInterval(pollAvanzar, 6000);
  }
}

function detenerModalRefresh() {
  if (modalRefreshInterval) {
    clearInterval(modalRefreshInterval);
    modalRefreshInterval = null;
  }
}

function cerrarModal() {
  mostrarModal.value = false;
  viajeSeleccionado.value = null;
  detenerModalRefresh();
}

onUnmounted(() => {
  detenerModalRefresh();
});
</script>

<style scoped>
.card {
  @apply bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700;
}

.form-input {
  @apply w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.btn-primary {
  @apply flex items-center justify-center gap-2 py-3 px-5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors;
}

.dark .btn-primary {
  @apply bg-teal-600 hover:bg-teal-700;
}

.btn-secondary {
  @apply flex items-center justify-center gap-2 py-3 px-5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors;
}

.dark .btn-secondary {
  @apply bg-slate-700 hover:bg-slate-600 text-slate-200;
}

.alert {
  @apply p-3 rounded-xl text-sm font-medium;
}

.alert-success {
  @apply bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400;
}

.alert-error {
  @apply bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400;
}
</style>

<style>
.waypoint-icon, .bus-icon {
  background: transparent !important;
  border: none !important;
}
</style>
