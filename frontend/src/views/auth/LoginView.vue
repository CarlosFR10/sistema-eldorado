<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 flex items-center justify-center p-4">
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-600/20 rounded-full blur-[128px]"></div>
      <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-blue-500/20 rounded-full blur-[128px]"></div>
    </div>

    <div class="relative w-full max-w-md">
      <div class="absolute inset-0 bg-white/5 backdrop-blur-xl rounded-3xl border border-white/10"></div>

      <div class="relative p-8 rounded-3xl">
        <div class="flex flex-col items-center mb-8">
          <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 mb-4 shadow-lg shadow-blue-500/30">
            <BusFront :size="28" class="text-white" />
          </div>
          <h1 class="text-2xl font-bold text-white mb-1">Terminal El Dorado</h1>
          <p class="text-slate-400 text-sm">Ingresa tus credenciales para continuar</p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-5">
          <div class="space-y-2">
            <label class="text-sm font-medium text-slate-300">Correo electronico</label>
            <div class="relative">
              <Mail :size="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500" />
              <input
                v-model="email"
                type="email"
                placeholder="admin@eldorado.bo"
                class="w-full pl-11 pr-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                required
              />
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-slate-300">Contrasena</label>
            <div class="relative">
              <Lock :size="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500" />
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                class="w-full pl-11 pr-12 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                required
              />
              <button
                type="button"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300"
                @click="showPassword = !showPassword"
              >
                <Eye v-if="showPassword" :size="18" />
                <EyeOff v-else :size="18" />
              </button>
            </div>
          </div>

          <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-slate-300 cursor-pointer">
              <input type="checkbox" class="w-4 h-4 rounded border-slate-600 bg-white/5 text-blue-500 focus:ring-blue-500 focus:ring-offset-0" />
              Recordarme
            </label>
            <a href="#" class="text-blue-400 hover:text-blue-300 transition-colors">Olvidaste tu contrasena?</a>
          </div>

          <button
            type="submit"
            class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold hover:from-blue-500 hover:to-blue-400 transition-all shadow-lg shadow-blue-500/30 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="loading"
          >
            <Loader2 v-if="loading" :size="18" class="animate-spin" />
            <span>{{ loading ? 'Ingresando...' : 'Iniciar sesion' }}</span>
          </button>

          <div v-if="error" class="p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm text-center">
            {{ error }}
          </div>
        </form>

        <div class="mt-8 pt-6 border-t border-white/10">
          <p class="text-center text-sm text-slate-400">
            Boleteria publica
            <RouterLink to="/" class="text-blue-400 hover:text-blue-300 ml-1">
              Ir a la tienda
            </RouterLink>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { BusFront, Eye, EyeOff, Loader2, Lock, Mail } from 'lucide-vue-next';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const email = ref('admin@eldorado.bo');
const password = ref('Eldorado2026!');
const showPassword = ref(false);
const loading = ref(false);
const error = ref('');

async function handleLogin() {
  error.value = '';
  loading.value = true;

  try {
    await new Promise((resolve) => setTimeout(resolve, 1000));
    router.push('/dashboard');
  } catch (e) {
    error.value = 'Credenciales invalidas';
  } finally {
    loading.value = false;
  }
}
</script>
