<template>
  <div class="space-y-5">
    <!-- Page header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Dashboard</h1>
        <p class="text-gray-500 mt-1">Resumen de la terminal</p>
      </div>
      <div class="flex items-center gap-3">
        <select
          v-model="selectedPeriod"
          class="px-4 py-2 rounded-lg bg-white dark:bg-[#161618] border border-[--art-card-border] text-sm"
        >
          <option value="today">Hoy</option>
          <option value="week">Esta semana</option>
          <option value="month">Este mes</option>
        </select>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="(stat, index) in statsData"
        :key="index"
        class="art-stats-card group"
      >
        <div
          class="size-12 rounded-xl flex items-center justify-center text-xl text-white mr-4"
          :class="stat.iconBg"
        >
          <component :is="stat.icon" :size="22" />
        </div>
        <div class="flex-1">
          <p class="text-lg font-medium text-gray-700 dark:text-gray-200">
            {{ stat.title }}
          </p>
          <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">
            {{ stat.value }}
          </p>
          <div class="flex items-center gap-2 mt-1">
            <span
              class="text-sm font-medium"
              :class="stat.change.includes('+') ? 'text-green-600' : 'text-red-500'"
            >
              {{ stat.change }}
            </span>
            <span class="text-xs text-gray-500">vs ayer</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Sales Chart -->
      <div class="lg:col-span-2 art-card p-5">
        <div class="art-card-header">
          <div class="title">
            <h4>Ventas semanales</h4>
            <p>Resumen de pasajes vendidos</p>
          </div>
          <div class="flex gap-1">
            <button
              v-for="period in ['Dia', 'Semana', 'Mes']"
              :key="period"
              class="px-3 py-1 text-xs rounded-lg transition-colors"
              :class="
                selectedChart === period
                  ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400'
                  : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800'
              "
              @click="selectedChart = period"
            >
              {{ period }}
            </button>
          </div>
        </div>
        <div class="flex items-end justify-between gap-2 h-48 mt-4">
          <div
            v-for="(day, index) in chartData"
            :key="index"
            class="flex-1 flex flex-col items-center gap-2"
          >
            <div
              class="w-full rounded-t-lg transition-all duration-500"
              :class="index === chartData.length - 1 ? 'bg-purple-500' : 'bg-purple-200 dark:bg-purple-900/50'"
              :style="{ height: `${(day.value / maxChartValue) * 100}%`, minHeight: '8px' }"
            ></div>
            <span class="text-xs text-gray-500">{{ day.label }}</span>
          </div>
        </div>
      </div>

      <!-- Top Destinations -->
      <div class="art-card p-5">
        <div class="art-card-header">
          <div class="title">
            <h4>Destinos populares</h4>
            <p>Mas vendidos esta semana</p>
          </div>
        </div>
        <div class="mt-4 space-y-4">
          <div
            v-for="(dest, index) in topDestinations"
            :key="index"
            class="flex items-center gap-3"
          >
            <div
              class="size-8 rounded-lg flex items-center justify-center text-xs font-bold"
              :class="index === 0 ? 'bg-purple-100 text-purple-600' : index === 1 ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'"
            >
              {{ index + 1 }}
            </div>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ dest.name }}</p>
              <p class="text-xs text-gray-500">{{ dest.tickets }} pasajes</p>
            </div>
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ dest.percentage }}%</span>
          </div>
        </div>
        <div class="mt-6 pt-4 border-t border-[--art-card-border]">
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500">Total rutas</span>
            <span class="font-semibold text-gray-700 dark:text-gray-200">8 destinos</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <!-- Recent Activity -->
      <div class="art-card p-5">
        <div class="art-card-header">
          <div class="title">
            <h4>Actividad reciente</h4>
            <p>Ultimas acciones en el sistema</p>
          </div>
        </div>
        <div class="mt-4 space-y-4">
          <div
            v-for="(activity, index) in recentActivity"
            :key="index"
            class="flex items-start gap-3 p-3 rounded-lg hover:bg-[--art-hover-color)] transition-colors"
          >
            <div
              class="size-8 rounded-lg flex items-center justify-center"
              :class="activity.iconBg"
            >
              <component :is="activity.icon" :size="16" :class="activity.iconColor" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-gray-700 dark:text-gray-200">{{ activity.text }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ activity.time }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Upcoming Trips -->
      <div class="art-card p-5">
        <div class="art-card-header">
          <div class="title">
            <h4>Proximos viajes</h4>
            <p>Salidas programadas para hoy</p>
          </div>
        </div>
        <div class="mt-4 space-y-3">
          <div
            v-for="(trip, index) in upcomingTrips"
            :key="index"
            class="flex items-center gap-4 p-3 rounded-xl bg-[--art-hover-color)] hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
          >
            <div class="text-center w-14">
              <p class="text-lg font-bold text-gray-700 dark:text-white">{{ trip.time }}</p>
              <p class="text-xs text-gray-500">{{ trip.date }}</p>
            </div>
            <div class="h-10 w-px bg-[--art-card-border]"></div>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ trip.route }}</p>
              <p class="text-xs text-gray-500">{{ trip.bus }} - {{ trip.seats }} asientos</p>
            </div>
            <span
              class="px-2.5 py-1 text-xs font-medium rounded-full"
              :class="trip.statusClass"
            >
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
  CheckCircle,
  DollarSign,
  Ticket,
  TrendingUp,
  Users,
  Clock,
  AlertCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const selectedPeriod = ref('today');
const selectedChart = ref('Dia');

const statsData = [
  {
    title: 'Boletos vendidos',
    value: '2,847',
    change: '+12%',
    icon: Ticket,
    iconBg: 'bg-purple-500',
  },
  {
    title: 'Pasajeros',
    value: '1,294',
    change: '+8%',
    icon: Users,
    iconBg: 'bg-blue-500',
  },
  {
    title: 'Viajes',
    value: '18',
    change: '+5%',
    icon: Bus,
    iconBg: 'bg-green-500',
  },
  {
    title: 'Ingresos',
    value: 'Bs 45,280',
    change: '+15%',
    icon: DollarSign,
    iconBg: 'bg-amber-500',
  },
];

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
];

const recentActivity = [
  {
    icon: Ticket,
    iconBg: 'bg-purple-100 dark:bg-purple-900/30',
    iconColor: 'text-purple-600 dark:text-purple-400',
    text: 'Boleto BLT-20260529-001 vendido',
    time: 'Hace 2 minutos',
  },
  {
    icon: Users,
    iconBg: 'bg-blue-100 dark:bg-blue-900/30',
    iconColor: 'text-blue-600 dark:text-blue-400',
    text: 'Nuevo pasajero registrado',
    time: 'Hace 15 minutos',
  },
  {
    icon: Bus,
    iconBg: 'bg-green-100 dark:bg-green-900/30',
    iconColor: 'text-green-600 dark:text-green-400',
    text: 'Viaje CBB-LPZ iniciado',
    time: 'Hace 30 minutos',
  },
  {
    icon: CheckCircle,
    iconBg: 'bg-emerald-100 dark:bg-emerald-900/30',
    iconColor: 'text-emerald-600 dark:text-emerald-400',
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
    statusClass: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
  },
  {
    time: '16:00',
    date: 'Hoy',
    route: 'Cochabamba → Santa Cruz',
    bus: 'Bus 002',
    seats: 35,
    status: 'En venta',
    statusClass: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
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
