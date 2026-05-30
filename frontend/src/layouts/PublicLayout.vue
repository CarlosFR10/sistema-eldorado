<template>
  <div class="public-layout">
    <!-- Header -->
    <header class="public-header">
      <div class="header-container">
        <RouterLink to="/" class="brand">
          <Bus :size="28" class="brand-icon" />
          <span class="brand-text">Terminal El Dorado</span>
        </RouterLink>
        <nav class="header-nav">
          <RouterLink to="/boleteria" class="nav-link">Boleteria</RouterLink>
          <RouterLink to="/rastrear" class="nav-link">Rastrear</RouterLink>
          <RouterLink to="/registro" class="nav-link">Registro</RouterLink>
          <RouterLink to="/consulta" class="nav-link">Autoridad</RouterLink>
          <RouterLink to="/login" class="btn-login">
            <User :size="18" />
            Ingresar
          </RouterLink>
        </nav>
        <button class="mobile-menu-btn" @click="mobileMenuOpen = !mobileMenuOpen">
          <Menu :size="24" />
        </button>
      </div>
    </header>

    <!-- Mobile Menu -->
    <div v-if="mobileMenuOpen" class="mobile-menu">
      <RouterLink to="/boleteria" class="mobile-link" @click="mobileMenuOpen = false">Boleteria</RouterLink>
      <RouterLink to="/rastrear" class="mobile-link" @click="mobileMenuOpen = false">Rastrear</RouterLink>
      <RouterLink to="/registro" class="mobile-link" @click="mobileMenuOpen = false">Registro</RouterLink>
      <RouterLink to="/consulta" class="mobile-link" @click="mobileMenuOpen = false">Autoridad</RouterLink>
      <RouterLink to="/login" class="mobile-link" @click="mobileMenuOpen = false">Ingresar</RouterLink>
    </div>

    <!-- Main Content -->
    <main class="public-main">
      <RouterView />
    </main>

    <!-- Footer -->
    <footer class="public-footer">
      <div class="footer-container">
        <div class="footer-brand">
          <Bus :size="24" />
          <span>Terminal de Buses El Dorado</span>
        </div>
        <div class="flex justify-center gap-6 text-sm mb-4">
          <RouterLink to="/boleteria" class="hover:text-white/70">Boleteria</RouterLink>
          <RouterLink to="/rastrear" class="hover:text-white/70">Rastreo</RouterLink>
          <RouterLink to="/consulta" class="hover:text-white/70">Autoridad</RouterLink>
          <RouterLink to="/login" class="hover:text-white/70">Panel</RouterLink>
        </div>
        <p class="footer-copy">&copy; 2026 Todos los derechos reservados</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Bus, User, Menu } from 'lucide-vue-next';

const mobileMenuOpen = ref(false);
</script>

<style scoped>
.public-layout {
  @apply min-h-screen flex flex-col bg-slate-50;
}

.public-header {
  @apply sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-slate-200 shadow-sm;
}

.header-container {
  @apply max-w-7xl mx-auto px-4 h-16 flex items-center justify-between;
}

.brand {
  @apply flex items-center gap-2 text-slate-800 hover:text-blue-600 transition-colors;
}

.brand-icon {
  @apply text-blue-600;
}

.brand-text {
  @apply font-bold text-lg;
}

.header-nav {
  @apply hidden md:flex items-center gap-6;
}

.nav-link {
  @apply text-slate-600 hover:text-blue-600 font-medium transition-colors;
}

.btn-login {
  @apply flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors;
}

.mobile-menu-btn {
  @apply md:hidden p-2 text-slate-600 hover:text-blue-600;
}

.mobile-menu {
  @apply md:hidden bg-white border-b border-slate-200 px-4 py-4 space-y-3;
}

.mobile-link {
  @apply block py-2 text-slate-600 hover:text-blue-600 font-medium;
}

.public-main {
  @apply flex-1;
}

.public-footer {
  @apply bg-slate-800 text-white py-8 mt-auto;
}

.footer-container {
  @apply max-w-7xl mx-auto px-4 text-center;
}

.footer-brand {
  @apply flex items-center justify-center gap-2 mb-4 text-xl font-bold;
}

.footer-copy {
  @apply text-slate-400 text-sm;
}
</style>
