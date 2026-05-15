<template>
  <main class="grid min-h-screen place-items-center bg-[#eef3f1] px-4 py-10">
    <section class="panel w-full max-w-md rounded-lg p-6">
      <div class="mb-6">
        <p class="text-sm font-black uppercase text-eldorado-teal">Terminal El Dorado</p>
        <h1 class="mt-1 text-2xl font-black text-slate-900">Ingreso operativo</h1>
      </div>

      <form v-if="!auth.pending2fa" class="space-y-4" @submit.prevent="submit">
        <label class="block">
          <span class="mb-1 block text-sm font-bold text-slate-700">Email</span>
          <input v-model="form.email" class="field" type="email" autocomplete="email" required />
        </label>
        <label class="block">
          <span class="mb-1 block text-sm font-bold text-slate-700">Contrasena</span>
          <input v-model="form.password" class="field" type="password" autocomplete="current-password" required />
        </label>
        <button class="btn btn-primary w-full" :disabled="auth.loading">
          <LogIn :size="18" />
          Ingresar
        </button>
      </form>

      <form v-else class="space-y-4" @submit.prevent="submitOtp">
        <label class="block">
          <span class="mb-1 block text-sm font-bold text-slate-700">Codigo OTP</span>
          <input v-model="otp" class="field text-center text-xl font-black tracking-widest" maxlength="6" required />
        </label>
        <p v-if="auth.pending2fa?.otp_dev" class="rounded-md bg-amber-50 p-3 text-sm font-bold text-amber-800">
          Desarrollo: {{ auth.pending2fa.otp_dev }}
        </p>
        <button class="btn btn-primary w-full">
          <ShieldCheck :size="18" />
          Verificar
        </button>
      </form>

      <p v-if="error" class="mt-4 rounded-md bg-red-50 p-3 text-sm font-bold text-red-800">{{ error }}</p>
    </section>
  </main>
</template>

<script setup>
import { LogIn, ShieldCheck } from 'lucide-vue-next';
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const form = reactive({ email: 'admin@eldorado.bo', password: 'Eldorado2026!' });
const otp = ref('');
const error = ref('');

async function submit() {
  error.value = '';
  try {
    const result = await auth.login(form);
    if (!result?.requires_2fa) redirectByRole();
  } catch (err) {
    error.value = err.message || 'No se pudo iniciar sesion.';
  }
}

async function submitOtp() {
  error.value = '';
  try {
    await auth.verify2fa(otp.value);
    redirectByRole();
  } catch (err) {
    error.value = err.message || 'Codigo OTP invalido.';
  }
}

function redirectByRole() {
  if (route.query.redirect) {
    router.push(String(route.query.redirect));
    return;
  }
  router.push(auth.role === 'vendedor' ? '/venta' : '/dashboard');
}
</script>
