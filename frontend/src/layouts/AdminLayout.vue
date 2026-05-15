<template>
  <div class="app-shell">
    <header class="border-b border-slate-200 bg-white/90">
      <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3">
        <RouterLink to="/dashboard" class="text-lg font-black text-eldorado-ink">Sistema El Dorado</RouterLink>
        <nav class="flex flex-wrap gap-2 text-sm font-bold text-slate-600">
          <RouterLink class="rounded-md px-3 py-2 hover:bg-teal-50" to="/dashboard">Dashboard</RouterLink>
          <RouterLink class="rounded-md px-3 py-2 hover:bg-teal-50" to="/venta">Venta</RouterLink>
          <RouterLink class="rounded-md px-3 py-2 hover:bg-teal-50" to="/abordaje">Abordaje</RouterLink>
          <RouterLink class="rounded-md px-3 py-2 hover:bg-teal-50" to="/gps">GPS</RouterLink>
          <RouterLink class="rounded-md px-3 py-2 hover:bg-teal-50" to="/admin/reportes">Reportes</RouterLink>
          <RouterLink v-if="auth.role === 'administrador'" class="rounded-md px-3 py-2 hover:bg-teal-50" to="/admin/usuarios">Usuarios</RouterLink>
        </nav>
        <button class="btn btn-secondary" @click="salir">
          <LogOut :size="18" />
          Salir
        </button>
      </div>
    </header>
    <main class="mx-auto max-w-7xl px-4 py-5">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { LogOut } from 'lucide-vue-next';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();

async function salir() {
  await auth.logout();
  router.push({ name: 'login' });
}
</script>
