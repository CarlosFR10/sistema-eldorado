<template>
  <div
    class="min-h-screen bg-[--default-bg-color] dark:bg-[#070707]"
    :class="{ 'sidebar-open': sidebarOpen }"
  >
    <!-- Sidebar -->
    <aside
      class="fixed top-0 left-0 z-50 h-screen w-[260px] bg-[--default-box-color] dark:bg-[#161618] border-r border-[--art-card-border] transform transition-transform duration-300"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <!-- Logo -->
      <div class="flex items-center h-16 px-5 border-b border-[--art-card-border]">
        <div class="flex items-center gap-3 cursor-pointer" @click="$router.push('/dashboard')">
          <div class="size-10 rounded-lg flex items-center justify-center bg-gradient-to-br from-purple-600 to-indigo-600 text-white">
            <BusFront :size="22" />
          </div>
          <div>
            <h1 class="text-base font-bold text-gray-800 dark:text-white">Terminal El Dorado</h1>
            <p class="text-xs text-gray-500">Sistema de boletos</p>
          </div>
        </div>
      </div>

      <!-- Menu -->
      <nav class="p-3 space-y-1 overflow-y-auto h-[calc(100vh-64px)]">
        <template v-for="item in menuItems" :key="item.path">
          <!-- Item sin hijos -->
          <RouterLink
            v-if="!item.children"
            :to="item.path"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all"
            :class="isActive(item.path)
              ? 'bg-[var(--art-primary)] text-white shadow-lg shadow-purple-500/20'
              : 'text-gray-600 dark:text-gray-300 hover:bg-[--art-hover-color)]'"
            @click="closeSidebarOnMobile"
          >
            <component :is="item.icon" :size="18" />
            <span>{{ item.label }}</span>
          </RouterLink>

          <!-- Item con hijos (submenu) -->
          <div v-else class="space-y-1">
            <button
              class="flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-[--art-hover-color)] transition-all"
              @click="toggleSubmenu(item.label)"
            >
              <span class="flex items-center gap-3">
                <component :is="item.icon" :size="18" />
                <span>{{ item.label }}</span>
              </span>
              <ChevronDown
                :size="16"
                class="transition-transform duration-200"
                :class="openSubmenus.includes(item.label) ? 'rotate-180' : ''"
              />
            </button>

            <div
              v-if="openSubmenus.includes(item.label)"
              class="ml-4 pl-3 border-l-2 border-[--art-card-border] space-y-1"
            >
              <RouterLink
                v-for="child in item.children"
                :key="child.path"
                :to="child.path"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all"
                :class="isActive(child.path)
                  ? 'bg-[var(--art-primary)] text-white shadow-lg shadow-purple-500/20'
                  : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                @click="closeSidebarOnMobile"
              >
                <component :is="child.icon" :size="16" />
                <span>{{ child.label }}</span>
              </RouterLink>
            </div>
          </div>
        </template>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="lg:pl-[260px]">
      <!-- Header -->
      <header class="sticky top-0 z-30 h-16 bg-[--default-bg-color] dark:bg-[#070707] border-b border-[--art-card-border]">
        <div class="flex items-center justify-between h-full px-4 lg:px-6">
          <!-- Left side -->
          <div class="flex items-center gap-4">
            <button
              class="lg:hidden p-2 rounded-lg hover:bg-[--art-hover-color)] text-gray-600 dark:text-gray-300"
              @click="toggleSidebar"
            >
              <Menu :size="20" />
            </button>

            <nav class="hidden md:flex items-center gap-1 text-sm text-gray-500">
              <RouterLink to="/dashboard" class="hover:text-gray-700 dark:hover:text-gray-200">
                Inicio
              </RouterLink>
              <ChevronRight :size="14" />
              <span class="text-gray-700 dark:text-gray-200">{{ currentPageTitle }}</span>
            </nav>
          </div>

          <!-- Right side -->
          <div class="flex items-center gap-2">
            <!-- Search -->
            <button class="p-2 rounded-lg hover:bg-[--art-hover-color)] text-gray-500">
              <Search :size="20" />
            </button>

            <!-- Fullscreen -->
            <button class="p-2 rounded-lg hover:bg-[--art-hover-color)] text-gray-500">
              <Maximize v-if="!isFullscreen" :size="20" @click="toggleFullscreen" />
              <Minimize v-else :size="20" @click="toggleFullscreen" />
            </button>

            <!-- Notifications -->
            <button class="relative p-2 rounded-lg hover:bg-[--art-hover-color)] text-gray-500">
              <Bell :size="20" />
              <span class="absolute top-2 right-2 size-2 rounded-full bg-red-500"></span>
            </button>

            <!-- Theme toggle -->
            <button
              class="p-2 rounded-lg hover:bg-[--art-hover-color)] text-gray-500"
              @click="toggleTheme"
            >
              <Moon v-if="!isDark" :size="20" />
              <Sun v-else :size="20" />
            </button>

            <!-- User -->
            <div class="relative ml-2 pl-4 border-l border-[--art-card-border]">
              <button
                class="flex items-center gap-3"
                @click="showUserMenu = !showUserMenu"
              >
                <div class="hidden md:block text-right">
                  <p class="text-sm font-medium text-gray-700 dark:text-white">{{ userName }}</p>
                  <p class="text-xs text-gray-500">{{ userRole }}</p>
                </div>
                <div class="size-9 rounded-full bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center text-white font-medium">
                  {{ userInitials }}
                </div>
              </button>

              <!-- User dropdown -->
              <div
                v-if="showUserMenu"
                class="absolute right-0 top-full mt-2 w-48 rounded-xl bg-[--default-box-color] dark:bg-[#161618] border border-[--art-card-border] shadow-xl py-2 z-50"
              >
                <RouterLink
                  to="/perfil"
                  class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-[--art-hover-color)]"
                  @click="showUserMenu = false"
                >
                  <User :size="16" />
                  Perfil
                </RouterLink>
                <button
                  class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                  @click="logout"
                >
                  <LogOut :size="16" />
                  Cerrar sesion
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main class="p-4 lg:p-6">
        <slot />
      </main>
    </div>

    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 bg-black/50 lg:hidden"
      @click="closeSidebarOnMobile"
    ></div>
  </div>
</template>

<script setup>
import {
  Bell,
  Bus,
  BusFront,
  ChevronDown,
  ChevronRight,
  Gauge,
  LogOut,
  Maximize,
  Menu,
  Minimize,
  Moon,
  Monitor,
  QrCode,
  Route,
  Search,
  Settings,
  Sun,
  Ticket,
  User,
  Users,
  FileBarChart,
  CheckSquare,
  MapPinned,
  UserPlus,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const sidebarOpen = ref(false);
const isDark = ref(false);
const isFullscreen = ref(false);
const showUserMenu = ref(false);
const openSubmenus = ref([]);

const menuItems = [
  { label: 'Dashboard', icon: Gauge, path: '/dashboard' },
  {
    label: 'Venta',
    icon: Ticket,
    children: [
      { label: 'Vender boleto', icon: Ticket, path: '/venta' },
      { label: 'Viajes activos', icon: Bus, path: '/venta/viajes' },
      { label: 'Crear viaje', icon: Route, path: '/venta/viajes/nuevo' },
      { label: 'Registro pasajero', icon: UserPlus, path: '/venta/pasajeros' },
    ],
  },
  {
    label: 'Abordaje',
    icon: CheckSquare,
    children: [
      { label: 'Control abordaje', icon: CheckSquare, path: '/abordaje' },
      { label: 'Validar QR', icon: QrCode, path: '/abordaje/validar' },
    ],
  },
  { label: 'GPS', icon: Monitor, path: '/gps/monitoreo' },
  {
    label: 'Reportes',
    icon: FileBarChart,
    children: [
      { label: 'Buses', icon: Bus, path: '/buses' },
      { label: 'Rutas', icon: Route, path: '/rutas' },
      { label: 'Reportes', icon: FileBarChart, path: '/reportes' },
    ],
  },
  { label: 'Usuarios', icon: Users, path: '/usuarios' },
];

const userName = 'Administrador';
const userRole = 'Administrador';
const userInitials = 'AD';

const currentPageTitle = computed(() => {
  const item = findMenuItem(menuItems, route.path);
  return item?.label || 'Dashboard';
});

function findMenuItem(items, path) {
  for (const item of items) {
    if (item.path === path) return item;
    if (item.children) {
      const child = item.children.find((c) => c.path === path);
      if (child) return child;
    }
  }
  return null;
}

function isActive(path) {
  return route.path === path;
}

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value;
}

function closeSidebarOnMobile() {
  sidebarOpen.value = false;
}

function toggleSubmenu(label) {
  const index = openSubmenus.value.indexOf(label);
  if (index === -1) {
    openSubmenus.value.push(label);
  } else {
    openSubmenus.value.splice(index, 1);
  }
}

function toggleTheme() {
  isDark.value = !isDark.value;
  document.documentElement.classList.toggle('dark', isDark.value);
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
}

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
    isFullscreen.value = true;
  } else {
    document.exitFullscreen();
    isFullscreen.value = false;
  }
}

function logout() {
  router.push('/login');
}
</script>
