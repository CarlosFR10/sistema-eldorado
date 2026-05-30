<template>
  <section class="relative min-h-screen overflow-hidden bg-[#0a0a0f] text-white">
    <div class="absolute inset-0">
      <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/20 rounded-full blur-[128px]"></div>
      <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-teal-500/20 rounded-full blur-[128px]"></div>
    </div>

    <div class="absolute inset-0 opacity-40">
      <div class="moving-bus bus-a"><BusFront :size="32" /></div>
      <div class="moving-bus bus-b"><BusFront :size="24" /></div>
    </div>

    <header class="relative z-10 mx-auto max-w-7xl px-6 py-6">
      <nav class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 backdrop-blur-sm border border-white/10">
            <BusFront :size="22" class="text-blue-400" />
          </div>
          <span class="text-lg font-bold tracking-tight">Terminal El Dorado</span>
        </div>
        <RouterLink to="/login" class="group flex items-center gap-2 px-5 py-2.5 rounded-full bg-white/5 border border-white/10 text-sm font-medium hover:bg-white/10 transition-all duration-300">
          <span>Iniciar sesion</span>
          <ArrowRight :size="16" class="transition-transform group-hover:translate-x-0.5" />
        </RouterLink>
      </nav>
    </header>

    <main class="relative z-10 mx-auto max-w-7xl px-6 pt-12 pb-20">
      <div class="max-w-3xl">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-300 text-sm font-medium mb-6">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
          </span>
          Pasajes interdepartamentales
        </div>

        <h1 class="text-5xl md:text-7xl font-bold tracking-tight leading-[1.1] mb-6">
          Viaja comodo.
          <br />
          <span class="bg-gradient-to-r from-blue-400 to-teal-400 bg-clip-text text-transparent">Llega seguro.</span>
        </h1>

        <p class="text-lg md:text-xl text-white/60 leading-relaxed max-w-xl mb-10">
          Compra pasajes, elige tu asiento y rastrea tu bus en tiempo real. Todo desde Cochabamba hacia cualquier destino.
        </p>

        <div class="flex flex-wrap gap-4">
          <RouterLink to="/boleteria" class="group flex items-center gap-2.5 px-6 py-3.5 rounded-xl bg-white text-slate-900 font-semibold hover:bg-blue-50 transition-colors duration-300">
            <Ticket :size="20" />
            Comprar boletos
            <ArrowRight :size="18" class="transition-transform group-hover:translate-x-0.5" />
          </RouterLink>
          <RouterLink to="/registro" class="flex items-center gap-2.5 px-6 py-3.5 rounded-xl bg-white/5 border border-white/10 font-medium hover:bg-white/10 transition-all duration-300">
            <UserPlus :size="20" />
            Registrarse
          </RouterLink>
          <RouterLink to="/rastrear" class="flex items-center gap-2.5 px-6 py-3.5 rounded-xl bg-white/5 border border-white/10 font-medium hover:bg-white/10 transition-all duration-300">
            <MapPinned :size="20" />
            Rastrear bus
          </RouterLink>
        </div>
      </div>

      <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl">
        <div class="group p-6 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all duration-300 cursor-default">
          <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center mb-4">
            <MapPin :size="20" class="text-blue-400" />
          </div>
          <h3 class="font-semibold text-lg mb-1">8+ Destinos</h3>
          <p class="text-sm text-white/50">La Paz, Santa Cruz, Oruro, Sucre, Potosi, Tarija, Trinidad, Cobija</p>
        </div>

        <div class="group p-6 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all duration-300 cursor-default">
          <div class="w-10 h-10 rounded-xl bg-teal-500/20 flex items-center justify-center mb-4">
            <ShieldCheck :size="20" class="text-teal-400" />
          </div>
          <h3 class="font-semibold text-lg mb-1">Huella Digital</h3>
          <p class="text-sm text-white/50">Registro biometrico para mayor seguridad en cada viaje</p>
        </div>

        <div class="group p-6 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all duration-300 cursor-default">
          <div class="w-10 h-10 rounded-xl bg-purple-500/20 flex items-center justify-center mb-4">
            <Navigation :size="20" class="text-purple-400" />
          </div>
          <h3 class="font-semibold text-lg mb-1">GPS en Vivo</h3>
          <p class="text-sm text-white/50">Rastrea la ubicacion de tu bus en tiempo real</p>
        </div>
      </div>

      <div class="mt-20 max-w-2xl">
        <div class="flex items-center gap-3 mb-6">
          <div class="h-px flex-1 bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
          <span class="text-sm text-white/40 font-medium">Proximas salidas</span>
          <div class="h-px flex-1 bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
        </div>

        <div class="space-y-3">
          <div class="flex items-center justify-between p-4 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all">
            <div class="flex items-center gap-4">
              <div class="w-16 text-center">
                <span class="text-2xl font-bold">07:30</span>
              </div>
              <div>
                <p class="font-medium">Cochabamba → La Paz</p>
                <p class="text-sm text-white/50">Bus ejecutivo | 20 asientos disponibles</p>
              </div>
            </div>
            <div class="text-right">
              <p class="font-bold text-lg">Bs 120</p>
              <RouterLink to="/boleteria" class="text-sm text-blue-400 hover:text-blue-300">Comprar</RouterLink>
            </div>
          </div>

          <div class="flex items-center justify-between p-4 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all">
            <div class="flex items-center gap-4">
              <div class="w-16 text-center">
                <span class="text-2xl font-bold">09:00</span>
              </div>
              <div>
                <p class="font-medium">Cochabamba → Santa Cruz</p>
                <p class="text-sm text-white/50">Bus semisleeper | 15 asientos disponibles</p>
              </div>
            </div>
            <div class="text-right">
              <p class="font-bold text-lg">Bs 180</p>
              <RouterLink to="/boleteria" class="text-sm text-blue-400 hover:text-blue-300">Comprar</RouterLink>
            </div>
          </div>

          <div class="flex items-center justify-between p-4 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all">
            <div class="flex items-center gap-4">
              <div class="w-16 text-center">
                <span class="text-2xl font-bold">12:00</span>
              </div>
              <div>
                <p class="font-medium">Cochabamba → Oruro</p>
                <p class="text-sm text-white/50">Bus economico | 25 asientos disponibles</p>
              </div>
            </div>
            <div class="text-right">
              <p class="font-bold text-lg">Bs 50</p>
              <RouterLink to="/boleteria" class="text-sm text-blue-400 hover:text-blue-300">Comprar</RouterLink>
            </div>
          </div>
        </div>
      </div>
    </main>

    <footer class="relative z-10 border-t border-white/10 py-8">
      <div class="mx-auto max-w-7xl px-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-sm text-white/40">Terminal El Dorado, Cochabamba, Bolivia</p>
        <div class="flex items-center gap-6 text-sm text-white/40">
          <RouterLink to="/boleteria" class="hover:text-white/70 transition-colors">Boleteria</RouterLink>
          <RouterLink to="/registro" class="hover:text-white/70 transition-colors">Registro</RouterLink>
          <RouterLink to="/rastrear" class="hover:text-white/70 transition-colors">Rastreo</RouterLink>
          <RouterLink to="/login" class="hover:text-white/70 transition-colors">Panel</RouterLink>
        </div>
      </div>
    </footer>
  </section>
</template>

<script setup>
import { ArrowRight, BusFront, MapPinned, MapPin, Navigation, ShieldCheck, Ticket, UserPlus } from 'lucide-vue-next';
</script>

<style scoped>
.moving-bus {
  position: absolute;
  display: grid;
  place-items: center;
  color: rgba(255, 255, 255, 0.6);
}

.bus-a {
  top: 28%;
  animation: busMove 20s linear infinite;
}

.bus-b {
  bottom: 22%;
  animation: busMove 28s linear infinite reverse;
}

@keyframes busMove {
  from { left: -80px; }
  to { left: calc(100% + 80px); }
}

@keyframes pulse-glow {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 1; }
}
</style>
