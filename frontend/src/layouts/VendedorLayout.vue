<template>
  <div class="app-shell dark:bg-slate-900 dark:text-white min-h-screen">
    <header class="border-b border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-800/90">
      <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3">
        <RouterLink to="/venta" class="text-lg font-black text-eldorado-ink dark:text-white">Venta de Pasajes</RouterLink>
        <nav class="flex flex-wrap gap-2 text-sm font-bold">
          <RouterLink class="rounded-md px-3 py-2 text-slate-600 dark:text-slate-300 hover:bg-teal-50 dark:hover:bg-slate-700" to="/venta">Emitir</RouterLink>
          <RouterLink class="rounded-md px-3 py-2 text-slate-600 dark:text-slate-300 hover:bg-teal-50 dark:hover:bg-slate-700" to="/venta/pasajeros">Pasajeros</RouterLink>
          <RouterLink class="rounded-md px-3 py-2 text-slate-600 dark:text-slate-300 hover:bg-teal-50 dark:hover:bg-slate-700" to="/venta/viajes">Viajes</RouterLink>
          <RouterLink class="rounded-md px-3 py-2 text-slate-600 dark:text-slate-300 hover:bg-teal-50 dark:hover:bg-slate-700" to="/venta/viajes/nuevo">Agregar viaje</RouterLink>
          <RouterLink v-if="auth.role !== 'vendedor'" class="rounded-md px-3 py-2 text-slate-600 dark:text-slate-300 hover:bg-teal-50 dark:hover:bg-slate-700" to="/dashboard">Admin</RouterLink>
        </nav>
        <div class="flex items-center gap-3">
          <button class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300" @click="toggleDark">
            <Sun v-if="isDark" :size="20" />
            <Moon v-else :size="20" />
          </button>
          <button class="btn btn-secondary dark:bg-slate-700 dark:text-white dark:border-slate-600" @click="salir">
            <LogOut :size="18" />
            Salir
          </button>
        </div>
      </div>
    </header>
    <main class="mx-auto max-w-7xl px-4 py-5">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { LogOut, Moon, Sun } from 'lucide-vue-next';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();

const isDark = ref(localStorage.getItem('theme') === 'dark' || document.documentElement.classList.contains('dark'));

function toggleDark() {
  isDark.value = !isDark.value;
  document.documentElement.classList.toggle('dark', isDark.value);
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
}

async function salir() {
  await auth.logout();
  router.push({ name: 'login' });
}
</script>
