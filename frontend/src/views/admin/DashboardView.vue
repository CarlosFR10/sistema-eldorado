<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Dashboard</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Resumen de la terminal</p>
      </div>
      <div class="flex items-center gap-3">
        <select v-model="selectedPeriod" class="px-4 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
          <option value="today">Hoy</option>
          <option value="week">Esta semana</option>
          <option value="month">Este mes</option>
        </select>
        <button class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm hover:bg-slate-50 dark:hover:bg-slate-700">
          <Download :size="16" />
          Exportar
        </button>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <div class="group p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-600 transition-all hover:shadow-lg hover:shadow-blue-500/10">
        <div class="flex items-start justify-between">
          <div class="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/30">
            <Ticket :size="20" class="text-blue-600 dark:text-blue-400" />
          </div>
          <span class="flex items-center gap-1 text-xs font-medium text-green-600 dark:text-green-400">
            <TrendingUp :size="14" />
            +12%
          </span>
        </div>
        <div class="mt-4">
          <p class="text-3xl font-bold text-slate-800 dark:text-white">2,847</p>
          <p class="text-sm text-slate-500 dark:text-slate-400">Boletos vendidos</p>
        </div>
        <div class="mt-3 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
          <div class="h-full bg-blue-500 rounded-full" style="width: 75%"></div>
        </div>
      </div>

      <div class="group p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-teal-300 dark:hover:border-teal-600 transition-all hover:shadow-lg hover:shadow-teal-500/10">
        <div class="flex items-start justify-between">
          <div class="p-2 rounded-xl bg-teal-50 dark:bg-teal-900/30">
            <Users :size="20" class="text-teal-600 dark:text-teal-400" />
          </div>
          <span class="flex items-center gap-1 text-xs font-medium text-green-600 dark:text-green-400">
            <TrendingUp :size="14" />
            +8%
          </span>
        </div>
        <div class="mt-4">
          <p class="text-3xl font-bold text-slate-800 dark:text-white">1,294</p>
          <p class="text-sm text-slate-500 dark:text-slate-400">Pasajeros registrados</p>
        </div>
        <div class="mt-3 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
          <div class="h-full bg-teal-500 rounded-full" style="width: 60%"></div>
        </div>
      </div>

      <div class="group p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-purple-300 dark:hover:border-purple-600 transition-all hover:shadow-lg hover:shadow-purple-500/10">
        <div class="flex items-start justify-between">
          <div class="p-2 rounded-xl bg-purple-50 dark:bg-purple-900/30">
            <Bus :size="20" class="text-purple-600 dark:text-purple-400" />
          </div>
          <span class="flex items-center gap-1 text-xs font-medium text-green-600 dark:text-green-400">
            <TrendingUp :size="14" />
            +5%
          </span>
        </div>
        <div class="mt-4">
          <p class="text-3xl font-bold text-slate-800 dark:text-white">18</p>
          <p class="text-sm text-slate-500 dark:text-slate-400">Viajes realizados</p>
        </div>
        <div class="mt-3 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
          <div class="h-full bg-purple-500 rounded-full" style="width: 45%"></div>
        </div>
      </div>

      <div class="group p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-amber-300 dark:hover:border-amber-600 transition-all hover:shadow-lg hover:shadow-amber-500/10">
        <div class="flex items-start justify-between">
          <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-900/30">
            <DollarSign :size="20" class="text-amber-600 dark:text-amber-400" />
          </div>
          <span class="flex items-center gap-1 text-xs font-medium text-green-600 dark:text-green-400">
            <TrendingUp :size="14" />
            +15%
          </span>
        </div>
        <div class="mt-4">
          <p class="text-3xl font-bold text-slate-800 dark:text-white">Bs 45,280</p>
          <p class="text-sm text-slate-500 dark:text-slate-400">Ingresos del dia</p>
        </div>
        <div class="mt-3 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
          <div class="h-full bg-amber-500 rounded-full" style="width: 85%"></div>
        </div>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
      <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-5">
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Ventas ultimos 7 dias</h2>
          <div class="flex gap-1">
            <button
              v-for="period in ['Dia', 'Semana', 'Mes']"
              :key="period"
              class="px-3 py-1 text-xs font-medium rounded-lg transition-colors"
              :class="selectedChart === period ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700'"
              @click="selectedChart = period"
            >
              {{ period }}
            </button>
          </div>
        </div>
        <div class="h-64 flex items-end justify-between gap-2">
          <div v-for="(day, index) in chartData" :key="index" class="flex-1 flex flex-col items-center gap-2">
            <div
              class="w-full rounded-t-lg transition-all duration-500"
              :class="index === chartData.length - 1 ? 'bg-blue-500' : 'bg-blue-200 dark:bg-blue-900/50'"
              :style="{ height: `${(day.value / maxChartValue) * 100}%`, minHeight: '8px' }"
            ></div>
            <span class="text-xs text-slate-500 dark:text-slate-400">{{ day.label }}</span>
          </div>
        </div>
      </div>

      <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-5">
        <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-5">Destinos populares</h2>
        <div class="space-y-4">
          <div v-for="(dest, index) in topDestinations" :key="index" class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg text-xs font-bold"
              :class="index === 0 ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' : index === 1 ? 'bg-teal-100 text-teal-600 dark:bg-teal-900/30 dark:text-teal-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'">
              {{ index + 1 }}
            </div>
            <div class="flex-1">
              <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ dest.name }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ dest.tickets }} pasajes</p>
            </div>
            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ dest.percentage }}%</span>
          </div>
        </div>
        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700">
          <div class="flex items-center justify-between text-sm">
            <span class="text-slate-500 dark:text-slate-400">Total rutas</span>
            <span class="font-semibold text-slate-700 dark:text-slate-200">8 destinos</span>
          </div>
        </div>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
      <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-5">
        <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Actividad reciente</h2>
        <div class="space-y-4">
          <div v-for="(activity, index) in recentActivity" :key="index" class="flex items-start gap-3">
            <div class="mt-0.5 p-1.5 rounded-lg" :class="activity.iconBg">
              <component :is="activity.icon" :size="16" :class="activity.iconColor" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-slate-700 dark:text-slate-200">{{ activity.text }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ activity.time }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-5">
        <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Proximos viajes</h2>
        <div class="space-y-3">
          <div v-for="(trip, index) in upcomingTrips" :key="index" class="flex items-center gap-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
            <div class="w-12 text-center">
              <p class="text-lg font-bold text-slate-700 dark:text-white">{{ trip.time }}</p>
              <p class="text-xs text-slate-500">{{ trip.date }}</p>
            </div>
            <div class="h-10 w-px bg-slate-200 dark:bg-slate-600"></div>
            <div class="flex-1">
              <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ trip.route }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ trip.bus }} - {{ trip.seats }} asientos</p>
            </div>
            <span class="px-2.5 py-1 text-xs font-medium rounded-full" :class="trip.statusClass">
              {{ trip.status }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  Bus,
  DollarSign,
  Download,
  Ticket,
  TrendingUp,
  Users,
  CheckCircle,
  Clock,
  AlertCircle,
  MapPin,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const selectedPeriod = ref('today');
const selectedChart = ref('Dia');

const chartData = [
  { label: 'Lun', value: 45 },
  { label: 'Mar', value: 62 },
  { label: 'Mie', value: 38 },
  { label: 'Jue', value: 78 },
  { label: 'Vie', value: 95 },
  { label: 'Sab', value: 88 },
  { label: 'Dom', value: 72 },
];

const maxChartValue = computed(() => Math.max(...chartData.map((d) => d.value)));

const topDestinations = [
  { name: 'La Paz', tickets: 892, percentage: 31 },
  { name: 'Santa Cruz', tickets: 654, percentage: 23 },
  { name: 'Oruro', tickets: 421, percentage: 15 },
  { name: 'Sucre', tickets: 312, percentage: 11 },
  { name: 'Potosi', tickets: 245, percentage: 9 },
];

const recentActivity = [
  {
    icon: Ticket,
    iconBg: 'bg-blue-100 dark:bg-blue-900/30',
    iconColor: 'text-blue-600 dark:text-blue-400',
    text: 'Boleto BLT-20260529-001 vendido',
    time: 'Hace 2 minutos',
  },
  {
    icon: Users,
    iconBg: 'bg-teal-100 dark:bg-teal-900/30',
    iconColor: 'text-teal-600 dark:text-teal-400',
    text: 'Nuevo pasajero registrado',
    time: 'Hace 15 minutos',
  },
  {
    icon: Bus,
    iconBg: 'bg-purple-100 dark:bg-purple-900/30',
    iconColor: 'text-purple-600 dark:text-purple-400',
    text: 'Viaje CBB-LPZ iniciado',
    time: 'Hace 30 minutos',
  },
  {
    icon: CheckCircle,
    iconBg: 'bg-green-100 dark:bg-green-900/30',
    iconColor: 'text-green-600 dark:text-green-400',
    text: 'Abordaje completado - 18 pasajeros',
    time: 'Hace 1 hora',
  },
];

const upcomingTrips = [
  {
    time: '14:30',
    date: 'Hoy',
    route: 'Cochabamba → La Paz',
    bus: 'Bus 001',
    seats: 40,
    status: 'En venta',
    statusClass: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
  },
  {
    time: '16:00',
    date: 'Hoy',
    route: 'Cochabamba → Santa Cruz',
    bus: 'Bus 002',
    seats: 35,
    status: 'En venta',
    statusClass: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
  },
  {
    time: '18:30',
    date: 'Hoy',
    route: 'Cochabamba → Oruro',
    bus: 'Bus 003',
    seats: 45,
    status: 'Por partir',
    statusClass: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
  },
];
</script>
