<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900">
    <aside
      class="fixed top-0 left-0 z-40 h-screen w-[260px] bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 transition-transform duration-300"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <div class="flex items-center gap-3 px-5 h-16 border-b border-slate-200 dark:border-slate-700">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-blue-600 to-blue-700">
          <BusFront :size="20" class="text-white" />
        </div>
        <div>
          <h1 class="text-sm font-bold text-slate-800 dark:text-white">Terminal El Dorado</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400">Sistema de boletos</p>
        </div>
      </div>

      <nav class="p-3 space-y-1 overflow-y-auto h-[calc(100vh-64px)]">
        <div v-for="item in menuItems" :key="item.path" class="space-y-1">
          <RouterLink
            v-if="!item.children"
            :to="item.path"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all"
            :class="isActive(item.path)
              ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'"
            @click="closeSidebarOnMobile"
          >
            <component :is="item.icon" :size="18" />
            <span>{{ item.label }}</span>
          </RouterLink>

          <div v-else>
            <button
              class="flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all"
              @click="toggleSubmenu(item.label)"
            >
              <span class="flex items-center gap-3">
                <component :is="item.icon" :size="18" />
                <span>{{ item.label }}</span>
              </span>
              <ChevronDown :size="16" class="transition-transform" :class="openSubmenus.includes(item.label) ? 'rotate-180' : ''" />
            </button>

            <div v-if="openSubmenus.includes(item.label)" class="ml-4 mt-1 space-y-1">
              <RouterLink
                v-for="child in item.children"
                :key="child.path"
                :to="child.path"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all"
                :class="isActive(child.path)
                  ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
                  : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
                @click="closeSidebarOnMobile"
              >
                <component :is="child.icon" :size="16" />
                <span>{{ child.label }}</span>
              </RouterLink>
            </div>
          </div>
        </div>
      </nav>
    </aside>

    <div class="lg:pl-[260px]">
      <header class="sticky top-0 z-30 h-16 bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl border-b border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between h-full px-4 lg:px-6">
          <div class="flex items-center gap-4">
            <button
              class="lg:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700"
              @click="toggleSidebar"
            >
              <Menu :size="20" />
            </button>
            <nav class="hidden md:flex items-center gap-1 text-sm text-slate-500 dark:text-slate-400">
              <RouterLink to="/" class="hover:text-slate-700 dark:hover:text-slate-200">Inicio</RouterLink>
              <ChevronRight :size="14" />
              <span class="text-slate-700 dark:text-slate-200">{{ currentPageTitle }}</span>
            </nav>
          </div>

          <div class="flex items-center gap-2">
            <button
              class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400"
              @click="toggleTheme"
            >
              <Moon v-if="isDark" :size="20" />
              <Sun v-else :size="20" />
            </button>

            <button
              class="relative p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400"
            >
              <Bell :size="20" />
              <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-red-500"></span>
            </button>

            <div class="flex items-center gap-3 pl-2 ml-2 border-l border-slate-200 dark:border-slate-700">
              <div class="hidden md:block text-right">
                <p class="text-sm font-medium text-slate-700 dark:text-white">{{ userName }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ userRole }}</p>
              </div>
              <button
                class="relative group"
                @click="showUserMenu = !showUserMenu"
              >
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-white font-medium">
                  {{ userInitials }}
                </div>
                <div
                  v-if="showUserMenu"
                  class="absolute right-0 top-full mt-2 w-48 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl py-1 z-50"
                >
                  <RouterLink
                    to="/perfil"
                    class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700"
                  >
                    <User :size="16" />
                    Perfil
                  </RouterLink>
                  <button
                    class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                    @click="logout"
                  >
                    <LogOut :size="16" />
                    Cerrar sesion
                  </button>
                </div>
              </button>
            </div>
          </div>
        </div>
      </header>

      <main class="p-4 lg:p-6">
        <slot />
      </main>
    </div>

    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-30 bg-black/50 lg:hidden"
      @click="closeSidebarOnMobile"
    ></div>
  </div>
</template>

<script setup>
import {
  Bell,
  BusFront,
  ChevronDown,
  ChevronRight,
  Home,
  LayoutDashboard,
  LogOut,
  Menu,
  Moon,
  Settings,
  Ticket,
  Sun,
  MapPin,
  Users,
  User,
  Gauge,
  Bus,
  Route,
  FileBarChart,
  UserPlus,
  MapPinned,
  QrCode,
  CheckSquare,
  Monitor,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const sidebarOpen = ref(false);
const isDark = ref(localStorage.getItem('theme') === 'dark' || document.documentElement.classList.contains('dark'));
const showUserMenu = ref(false);
const openSubmenus = ref([]);

const menuItems = [
  { label: 'Dashboard', icon: LayoutDashboard, path: '/dashboard' },
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
  {
    label: 'GPS',
    icon: Monitor,
    path: '/gps/monitoreo',
  },
  {
    label: 'Autoridad',
    icon: Gauge,
    path: '/autoridad/consulta',
  },
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
const userRole = 'Admin';
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

function toggleTheme() {
  isDark.value = !isDark.value;
  document.documentElement.classList.toggle('dark', isDark.value);
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
}

function toggleSubmenu(label) {
  const index = openSubmenus.value.indexOf(label);
  if (index === -1) {
    openSubmenus.value.push(label);
  } else {
    openSubmenus.value.splice(index, 1);
  }
}

function logout() {
  router.push('/login');
}
</script>
