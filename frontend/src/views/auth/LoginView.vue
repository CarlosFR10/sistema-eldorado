<template>
  <div class="login-page">
    <div class="login-card">
      <!-- Logo & Title -->
      <div class="login-header">
        <div class="login-logo">
          <Bus :size="40" class="text-blue-600" />
        </div>
        <h1 class="login-title">Sistema El Dorado</h1>
        <p class="login-subtitle">Ingrese sus credenciales para continuar</p>
      </div>

      <!-- Login Form -->
      <form v-if="!auth.pending2fa" class="login-form" @submit.prevent="submit">
        <div class="form-group">
          <label class="form-label">Email</label>
          <div class="input-wrapper">
            <Mail :size="18" class="input-icon" />
            <input
              v-model="form.email"
              type="email"
              class="form-input"
              placeholder="admin@eldorado.bo"
              required
            />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Contrasena</label>
          <div class="input-wrapper">
            <Lock :size="18" class="input-icon" />
            <input
              v-model="form.password"
              type="password"
              class="form-input"
              placeholder="********"
              required
            />
          </div>
        </div>

        <button type="submit" class="btn-login" :disabled="auth.loading">
          <LogIn v-if="!auth.loading" :size="18" />
          <span v-if="auth.loading" class="spinner"></span>
          {{ auth.loading ? 'Ingresando...' : 'Ingresar' }}
        </button>
      </form>

      <!-- 2FA Form -->
      <form v-else class="login-form" @submit.prevent="submitOtp">
        <div class="otp-section">
          <p class="otp-info">Se envio un codigo de verificacion a su email</p>
          <div class="form-group">
            <label class="form-label">Codigo OTP</label>
            <div class="input-wrapper">
              <ShieldCheck :size="18" class="input-icon" />
              <input
                v-model="otp"
                type="text"
                class="form-input otp-input"
                placeholder="000000"
                maxlength="6"
                required
              />
            </div>
          </div>
          <p v-if="auth.pending2fa?.otp_dev" class="otp-dev">
            Desarrollo: {{ auth.pending2fa.otp_dev }}
          </p>
        </div>

        <button type="submit" class="btn-login">
          <ShieldCheck :size="18" />
          Verificar
        </button>
      </form>

      <!-- Error Message -->
      <div v-if="error" class="login-error">
        <AlertCircle :size="16" />
        {{ error }}
      </div>
    </div>

    <!-- Footer -->
    <p class="login-footer">
      Terminal de Buses El Dorado &copy; 2026
    </p>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { Bus, Mail, Lock, LogIn, ShieldCheck, AlertCircle } from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const form = reactive({
  email: 'admin@eldorado.bo',
  password: 'Eldorado2026!'
})

const otp = ref('')
const error = ref('')

async function submit() {
  error.value = ''
  try {
    const result = await auth.login(form)
    if (!result?.requires_2fa) redirectByRole()
  } catch (err) {
    error.value = err.message || 'No se pudo iniciar sesion.'
  }
}

async function submitOtp() {
  error.value = ''
  try {
    await auth.verify2fa(otp.value)
    redirectByRole()
  } catch (err) {
    error.value = err.message || 'Codigo OTP invalido.'
  }
}

function redirectByRole() {
  if (route.query.redirect) {
    router.push(String(route.query.redirect))
    return
  }
  router.push(auth.role === 'vendedor' ? '/venta' : '/dashboard')
}
</script>

<style scoped>
.login-page {
  @apply min-h-screen bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-900 dark:to-slate-800 flex flex-col items-center justify-center p-4;
}

.login-card {
  @apply w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8;
}

.login-header {
  @apply text-center mb-8;
}

.login-logo {
  @apply inline-flex items-center justify-center w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-2xl mb-4;
}

.login-title {
  @apply text-2xl font-bold text-slate-800 dark:text-white mb-2;
}

.login-subtitle {
  @apply text-slate-500 dark:text-slate-400 text-sm;
}

.login-form {
  @apply space-y-5;
}

.form-group {
  @apply space-y-2;
}

.form-label {
  @apply block text-sm font-semibold text-slate-700 dark:text-slate-300;
}

.input-wrapper {
  @apply relative;
}

.input-icon {
  @apply absolute left-3 top-1/2 -translate-y-1/2 text-slate-400;
}

.form-input {
  @apply w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all;
}

.form-input.otp-input {
  @apply text-center text-2xl font-bold tracking-widest;
}

.btn-login {
  @apply w-full flex items-center justify-center gap-2 py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed;
}

.otp-section {
  @apply p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl;
}

.otp-info {
  @apply text-sm text-slate-600 dark:text-slate-300 mb-4;
}

.otp-dev {
  @apply mt-3 p-2 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-sm font-mono rounded-lg text-center;
}

.login-error {
  @apply mt-4 p-3 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm rounded-xl flex items-center gap-2;
}

.login-footer {
  @apply mt-6 text-sm text-slate-400 dark:text-slate-500;
}
</style>
