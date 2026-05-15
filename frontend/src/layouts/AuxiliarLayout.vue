<template>
  <div class="app-shell">
    <header class="border-b border-slate-200 bg-white/90">
      <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3">
        <RouterLink to="/abordaje" class="text-lg font-black text-eldorado-ink">Control de Abordaje</RouterLink>
        <nav class="flex flex-wrap gap-2 text-sm font-bold text-slate-600">
          <RouterLink class="rounded-md px-3 py-2 hover:bg-teal-50" to="/abordaje">Control</RouterLink>
          <RouterLink class="rounded-md px-3 py-2 hover:bg-teal-50" to="/abordaje/validar-qr">QR</RouterLink>
          <RouterLink v-if="auth.role !== 'auxiliar'" class="rounded-md px-3 py-2 hover:bg-teal-50" to="/dashboard">Admin</RouterLink>
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
