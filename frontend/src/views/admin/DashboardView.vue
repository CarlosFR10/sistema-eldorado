<template>
  <section class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Resumen operativo</p>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Dashboard</h1>
      </div>
      <button class="btn-refresh" @click="cargar">
        <RefreshCw :size="18" />
        Actualizar
      </button>
    </div>

    <!-- KPI Cards -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <div v-for="item in kpis" :key="item.label" class="card-stats">
        <div class="card-stats-icon" :class="item.color">
          <component :is="item.icon" :size="24" />
        </div>
        <div class="card-stats-content">
          <p class="card-stats-label">{{ item.label }}</p>
          <p class="card-stats-value">{{ item.value }}</p>
        </div>
      </div>
    </div>

    <!-- Charts & Lists -->
    <div class="grid gap-6 lg:grid-cols-3">
      <!-- Ingresos Chart -->
      <div class="card lg:col-span-2">
        <div class="card-header">
          <h2 class="card-title">Ingresos por fecha</h2>
        </div>
        <div class="card-body">
          <div class="space-y-4">
            <div v-for="row in ingresos" :key="row.fecha" class="chart-row">
              <span class="chart-label">{{ row.fecha }}</span>
              <div class="chart-bar-container">
                <div class="chart-bar" :style="{ width: barWidth(row.ingresos) }"></div>
              </div>
              <span class="chart-value">Bs {{ Number(row.ingresos || 0).toFixed(0) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Viajes de Hoy -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">Viajes de hoy</h2>
          <span class="badge">{{ viajes.length }} viajes</span>
        </div>
        <div class="card-body">
          <div class="space-y-3">
            <div v-for="viaje in viajes.slice(0, 6)" :key="viaje.id" class="trip-item">
              <div class="trip-info">
                <span class="trip-code">{{ viaje.ruta?.codigo }}</span>
                <EstadoSemaforo :estado="viaje.estado" />
              </div>
              <p class="trip-details">{{ viaje.bus?.placa }} - {{ formatDate(viaje.fecha_salida) }}</p>
            </div>
            <p v-if="viajes.length === 0" class="text-center text-slate-400 py-4">
              No hay viajes programados
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref, h } from 'vue';
import { RefreshCw, Ticket, DollarSign, Bus, Users } from 'lucide-vue-next';
import { ingresos as ingresosApi, ventasDiarias } from '../../api/admin';
import { listarViajes } from '../../api/viajes';
import EstadoSemaforo from '../../components/EstadoSemaforo.vue';

const TicketIcon = h(Ticket);
const DollarIcon = h(DollarSign);
const BusIcon = h(Bus);
const UsersIcon = h(Users);

const ventas = ref([]);
const ingresos = ref([]);
const viajes = ref([]);

const kpis = computed(() => {
  const boletos = ventas.value.reduce((sum, row) => sum + Number(row.boletos || 0), 0);
  const total = ventas.value.reduce((sum, row) => sum + Number(row.ingresos || 0), 0);
  return [
    { label: 'Boletos vendidos', value: boletos, icon: TicketIcon, color: 'bg-blue-100 text-blue-600' },
    { label: 'Ingresos', value: `Bs ${total.toFixed(0)}`, icon: DollarIcon, color: 'bg-green-100 text-green-600' },
    { label: 'Buses programados', value: viajes.value.length, icon: BusIcon, color: 'bg-purple-100 text-purple-600' },
    { label: 'Viajes abordando', value: viajes.value.filter((v) => v.estado === 'abordando').length, icon: UsersIcon, color: 'bg-orange-100 text-orange-600' }
  ];
});

onMounted(cargar);

async function cargar() {
  const [ventasResp, ingresosResp, viajesResp] = await Promise.all([
    ventasDiarias(),
    ingresosApi(),
    listarViajes({ fecha: new Date().toISOString().slice(0, 10) })
  ]);
  ventas.value = ventasResp.data || [];
  ingresos.value = ingresosResp.data || [];
  viajes.value = viajesResp.data?.data || viajesResp.data || [];
}

function barWidth(value) {
  const max = Math.max(...ingresos.value.map((row) => Number(row.ingresos || 0)), 1);
  return `${Math.max(5, (Number(value || 0) / max) * 100)}%`;
}

function formatDate(value) {
  return value ? new Date(value).toLocaleString('es-BO', { hour: '2-digit', minute: '2-digit' }) : '-';
}
</script>

<style scoped>
.btn-refresh {
  @apply flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors;
}

.card-stats {
  @apply flex items-center gap-4 p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700;
}

.card-stats-icon {
  @apply flex items-center justify-center w-12 h-12 rounded-xl;
}

.card-stats-content {
  @apply flex-1;
}

.card-stats-label {
  @apply text-sm text-slate-500 dark:text-slate-400;
}

.card-stats-value {
  @apply text-xl font-bold text-slate-800 dark:text-white;
}

.card {
  @apply bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden;
}

.card-header {
  @apply flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700;
}

.card-title {
  @apply text-base font-semibold text-slate-800 dark:text-white;
}

.card-body {
  @apply p-5;
}

.badge {
  @apply px-2.5 py-0.5 text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full;
}

.chart-row {
  @apply grid grid-cols-[80px_1fr_80px] items-center gap-4 text-sm;
}

.chart-label {
  @apply font-medium text-slate-600 dark:text-slate-400;
}

.chart-bar-container {
  @apply h-3 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden;
}

.chart-bar {
  @apply h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full transition-all duration-500;
}

.chart-value {
  @apply text-right font-semibold text-slate-800 dark:text-white;
}

.trip-item {
  @apply p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg;
}

.trip-info {
  @apply flex items-center justify-between gap-2 mb-1;
}

.trip-code {
  @apply font-semibold text-slate-800 dark:text-white;
}

.trip-details {
  @apply text-xs text-slate-500 dark:text-slate-400;
}
</style>
