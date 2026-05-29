<template>
  <div class="app" :class="{ 'dark': isDark }">
    <!-- Sidebar -->
    <aside
      class="sidebar"
      :class="{
        'is-active': isSidebarActive,
        'is-collapsed': isSidebarCollapsed
      }"
    >
      <!-- Logo -->
      <div class="sidebar-header">
        <RouterLink to="/dashboard" class="sidebar-logo">
          <span class="sidebar-logo-text">Eldorado</span>
        </RouterLink>
      </div>

      <!-- Navigation -->
      <nav class="sidebar-nav">
        <!-- Admin Menu -->
        <template v-if="isAdmin || isSupervisor">
          <div class="nav-section">
            <span class="nav-section-title">Principal</span>
            <RouterLink to="/dashboard" class="nav-item" :class="{ 'is-active': route.name === 'dashboard' }">
              <LayoutDashboard :size="20" />
              <span>Dashboard</span>
            </RouterLink>
          </div>

          <div class="nav-section">
            <span class="nav-section-title">Administracion</span>
            <RouterLink to="/admin/usuarios" v-if="isAdmin" class="nav-item" :class="{ 'is-active': route.name === 'usuarios' }">
              <Users :size="20" />
              <span>Usuarios</span>
            </RouterLink>
            <RouterLink to="/admin/buses" class="nav-item" :class="{ 'is-active': route.name === 'buses' }">
              <Bus :size="20" />
              <span>Buses</span>
            </RouterLink>
            <RouterLink to="/admin/rutas" class="nav-item" :class="{ 'is-active': route.name === 'rutas' }">
              <MapPin :size="20" />
              <span>Rutas</span>
            </RouterLink>
            <RouterLink to="/admin/reportes" class="nav-item" :class="{ 'is-active': route.name === 'reportes' }">
              <FileText :size="20" />
              <span>Reportes</span>
            </RouterLink>
          </div>
        </template>

        <!-- Vendedor Menu -->
        <template v-if="isVendedor || isSupervisor || isAdmin">
          <div class="nav-section">
            <span class="nav-section-title">Venta</span>
            <RouterLink to="/venta" class="nav-item" :class="{ 'is-active': route.path === '/venta' }">
              <ShoppingCart :size="20" />
              <span>Emitir Boleto</span>
            </RouterLink>
            <RouterLink to="/venta/pasajeros" class="nav-item" :class="{ 'is-active': route.name === 'registro-pasajero' }">
              <UserPlus :size="20" />
              <span>Pasajeros</span>
            </RouterLink>
            <RouterLink to="/venta/viajes" class="nav-item" :class="{ 'is-active': route.name === 'viajes-activos' }">
              <Bus :size="20" />
              <span>Viajes</span>
            </RouterLink>
          </div>
        </template>

        <!-- Abordaje Menu -->
        <template v-if="isAuxiliar || isSupervisor || isAdmin">
          <div class="nav-section">
            <span class="nav-section-title">Abordaje</span>
            <RouterLink to="/abordaje" class="nav-item" :class="{ 'is-active': route.name === 'control-abordaje' }">
              <Scan :size="20" />
              <span>Control</span>
            </RouterLink>
            <RouterLink to="/abordaje/validar-qr" class="nav-item" :class="{ 'is-active': route.name === 'validar-qr' }">
              <QrCode :size="20" />
              <span>Validar QR</span>
            </RouterLink>
          </div>
        </template>

        <!-- GPS Menu -->
        <template v-if="isSupervisor || isAdmin">
          <div class="nav-section">
            <span class="nav-section-title">Monitoreo</span>
            <RouterLink to="/gps" class="nav-item" :class="{ 'is-active': route.name === 'gps' }">
              <Map :size="20" />
              <span>GPS</span>
            </RouterLink>
          </div>
        </template>
      </nav>

      <!-- Sidebar Footer -->
      <div class="sidebar-footer">
        <button class="nav-item" @click="salir">
          <LogOut :size="20" />
          <span>Salir</span>
        </button>
      </div>
    </aside>

    <!-- Overlay for mobile -->
    <div class="sidebar-overlay" :class="{ 'is-active': isSidebarActive }" @click="isSidebarActive = false"></div>

    <!-- Main -->
    <div class="main">
      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left">
          <button class="topbar-btn" @click="toggleSidebar">
            <Menu :size="20" />
          </button>
          <h1 class="topbar-title">{{ pageTitle }}</h1>
        </div>
        <div class="topbar-right">
          <button class="topbar-btn" @click="toggleDarkMode">
            <Sun v-if="isDark" :size="20" />
            <Moon v-else :size="20" />
          </button>
          <div class="topbar-profile">
            <span class="topbar-username">{{ auth.nombre || auth.name }}</span>
            <span class="topbar-role">{{ auth.roleLabel }}</span>
          </div>
        </div>
      </header>

      <!-- Content -->
      <main class="main-content">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import {
  LayoutDashboard,
  Users,
  Bus,
  MapPin,
  FileText,
  ShoppingCart,
  UserPlus,
  Scan,
  QrCode,
  Map,
  Menu,
  Sun,
  Moon,
  LogOut
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const isSidebarActive = ref(false)
const isSidebarCollapsed = ref(false)
const isDark = ref(false)

const isAdmin = computed(() => auth.role === 'administrador')
const isSupervisor = computed(() => auth.role === 'supervisor')
const isVendedor = computed(() => auth.role === 'vendedor')
const isAuxiliar = computed(() => auth.role === 'auxiliar')

const pageTitle = computed(() => {
  const titles = {
    dashboard: 'Dashboard',
    usuarios: 'Usuarios',
    buses: 'Buses',
    rutas: 'Rutas',
    reportes: 'Reportes',
    venta: 'Venta de Pasajes',
    'registro-pasajero': 'Registro Pasajeros',
    'viajes-activos': 'Viajes Activos',
    'crear-viaje': 'Crear Viaje',
    'control-abordaje': 'Control Abordaje',
    'validar-qr': 'Validar QR',
    gps: 'Monitoreo GPS',
    consulta: 'Consulta'
  }
  return titles[route.name] || 'Sistema Eldorado'
})

function toggleSidebar() {
  isSidebarActive.value = !isSidebarActive.value
}

function toggleDarkMode() {
  isDark.value = !isDark.value
  localStorage.setItem('darkMode', isDark.value)
  document.documentElement.classList.toggle('dark', isDark.value)
}

async function salir() {
  await auth.logout()
  router.push({ name: 'login' })
}

onMounted(() => {
  const savedDark = localStorage.getItem('darkMode')
  if (savedDark === 'true') {
    isDark.value = true
    document.documentElement.classList.add('dark')
  }
})
</script>

<style>
/* Admin One Style Variables */
:root {
  --sidebar-width: 260px;
  --sidebar-collapsed-width: 72px;
  --topbar-height: 64px;
}

/* Base Styles */
.app {
  @apply bg-slate-100 text-slate-700 min-h-screen;
}

.dark .app {
  @apply bg-slate-900 text-slate-200;
}

/* Sidebar */
.sidebar {
  @apply fixed top-0 left-0 z-40 h-screen w-[var(--sidebar-width)] bg-white border-r border-slate-200 transition-all duration-300;
}

.dark .sidebar {
  @apply bg-slate-800 border-slate-700;
}

.sidebar.is-collapsed {
  @apply w-[var(--sidebar-collapsed-width)];
}

.sidebar-header {
  @apply h-[var(--topbar-height)] flex items-center px-4 border-b border-slate-200;
}

.dark .sidebar-header {
  @apply border-slate-700;
}

.sidebar-logo {
  @apply flex items-center gap-3;
}

.sidebar-logo-text {
  @apply text-xl font-black text-slate-800 dark:text-white;
}

.sidebar-nav {
  @apply py-4 px-3 space-y-6 overflow-y-auto h-[calc(100vh-var(--topbar-height)-140px)];
}

.nav-section {
  @apply space-y-1;
}

.nav-section-title {
  @apply px-3 text-xs font-semibold uppercase text-slate-400 tracking-wider;
}

.nav-item {
  @apply flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 font-medium transition-colors;
}

.dark .nav-item {
  @apply text-slate-300;
}

.nav-item:hover {
  @apply bg-slate-100 text-slate-900;
}

.dark .nav-item:hover {
  @apply bg-slate-700 text-white;
}

.nav-item.is-active {
  @apply bg-blue-50 text-blue-600;
}

.dark .nav-item.is-active {
  @apply bg-blue-900/30 text-blue-400;
}

.sidebar-footer {
  @apply absolute bottom-0 left-0 right-0 p-3 border-t border-slate-200;
}

.dark .sidebar-footer {
  @apply border-slate-700;
}

/* Overlay */
.sidebar-overlay {
  @apply fixed inset-0 z-30 bg-black/50 opacity-0 invisible transition-all duration-300;
}

.sidebar-overlay.is-active {
  @apply opacity-100 visible;
}

/* Main */
.main {
  @apply ml-[var(--sidebar-width)] transition-all duration-300;
}

.sidebar.is-collapsed + .sidebar-overlay + .main,
.sidebar.is-collapsed ~ .main {
  @apply ml-[var(--sidebar-collapsed-width)];
}

/* Topbar */
.topbar {
  @apply sticky top-0 z-20 h-[var(--topbar-height)] bg-white border-b border-slate-200 flex items-center justify-between px-6;
}

.dark .topbar {
  @apply bg-slate-800 border-slate-700;
}

.topbar-left {
  @apply flex items-center gap-4;
}

.topbar-btn {
  @apply p-2 rounded-lg hover:bg-slate-100 text-slate-600 transition-colors;
}

.dark .topbar-btn {
  @apply hover:bg-slate-700 text-slate-300;
}

.topbar-title {
  @apply text-lg font-semibold text-slate-800 dark:text-white;
}

.topbar-right {
  @apply flex items-center gap-4;
}

.topbar-profile {
  @apply flex flex-col items-end;
}

.topbar-username {
  @apply text-sm font-semibold text-slate-800 dark:text-white;
}

.topbar-role {
  @apply text-xs text-slate-500 dark:text-slate-400;
}

/* Main Content */
.main-content {
  @apply p-6;
}

/* Responsive */
@media (max-width: 1024px) {
  .sidebar {
    @apply -translate-x-full;
  }

  .sidebar.is-active {
    @apply translate-x-0;
  }

  .main {
    @apply ml-0;
  }
}
</style>
