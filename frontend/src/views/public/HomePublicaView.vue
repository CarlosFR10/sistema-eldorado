<template>
  <section class="relative min-h-screen overflow-hidden bg-slate-950 text-white">
    <div class="absolute inset-0 bg-[linear-gradient(120deg,#0f766e,#0f172a_45%,#1e293b)]"></div>
    <div class="route-line route-line-a"></div>
    <div class="route-line route-line-b"></div>
    <div class="absolute inset-0 opacity-30">
      <div class="moving-bus bus-a"><BusFront :size="34" /></div>
      <div class="moving-bus bus-b"><BusFront :size="28" /></div>
    </div>

    <header class="relative z-10 mx-auto flex max-w-7xl items-center justify-between px-5 py-5">
      <div class="flex items-center gap-3">
        <div class="grid h-12 w-12 place-items-center rounded-lg border border-white/20 bg-white/10">
          <BusFront :size="26" />
        </div>
        <div>
          <p class="text-xs font-black uppercase text-teal-200">Logo / nombre de bus</p>
          <h1 class="text-xl font-black">Terminal El Dorado</h1>
        </div>
      </div>
      <RouterLink class="rounded-lg border border-white/20 px-4 py-2 text-sm font-black hover:bg-white/10" to="/login">
        Iniciar sesion
      </RouterLink>
    </header>

    <main class="relative z-10 mx-auto grid min-h-[calc(100vh-88px)] max-w-7xl items-center gap-8 px-5 py-8 lg:grid-cols-[1.1fr_0.9fr]">
      <section>
        <p class="text-sm font-black uppercase tracking-wide text-teal-200">Pasajes interdepartamentales</p>
        <h2 class="mt-4 max-w-3xl text-5xl font-black leading-tight md:text-7xl">
          Compra y rastrea tu bus desde Cochabamba.
        </h2>
        <p class="mt-5 max-w-2xl text-lg font-semibold leading-8 text-slate-200">
          Elige destino, horario y asiento con croquis visual. Luego usa el codigo del boleto o del bus para ver su ubicacion y estado.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
          <RouterLink class="btn bg-white px-5 py-3 font-black text-slate-950 hover:bg-teal-50" to="/boleteria">
            <Ticket :size="20" />
            Comprar boletos
          </RouterLink>
          <RouterLink class="btn border border-white/30 bg-white/10 px-5 py-3 font-black text-white hover:bg-white/20" to="/registro">
            Registrarse
          </RouterLink>
          <RouterLink class="btn border border-white/30 bg-white/10 px-5 py-3 font-black text-white hover:bg-white/20" to="/rastrear">
            <MapPinned :size="20" />
            Rastrear bus
          </RouterLink>
        </div>
      </section>

      <section class="rounded-xl border border-white/15 bg-white/10 p-5 shadow-2xl backdrop-blur">
        <div class="grid gap-3">
          <div class="rounded-lg bg-white p-4 text-slate-950">
            <p class="text-sm font-black text-teal-700">Siguiente salida</p>
            <h3 class="mt-1 text-2xl font-black">Cochabamba - La Paz</h3>
            <p class="font-semibold text-slate-600">Bus ejecutivo | asiento a eleccion | QR de rastreo</p>
          </div>
          <div class="grid grid-cols-3 gap-3 text-center">
            <div class="rounded-lg bg-white/10 p-3">
              <p class="text-2xl font-black">16</p>
              <p class="text-xs font-bold text-slate-200">rutas ida/vuelta</p>
            </div>
            <div class="rounded-lg bg-white/10 p-3">
              <p class="text-2xl font-black">20</p>
              <p class="text-xs font-bold text-slate-200">buses</p>
            </div>
            <div class="rounded-lg bg-white/10 p-3">
              <p class="text-2xl font-black">GPS</p>
              <p class="text-xs font-bold text-slate-200">en tiempo real</p>
            </div>
          </div>
        </div>
      </section>
    </main>
  </section>
</template>

<script setup>
import { BusFront, MapPinned, Ticket } from 'lucide-vue-next';
</script>

<style scoped>
.route-line {
  position: absolute;
  left: -15%;
  right: -15%;
  height: 2px;
  background: repeating-linear-gradient(90deg, rgba(255, 255, 255, 0.2) 0 26px, transparent 26px 46px);
  transform: rotate(-10deg);
}

.route-line-a {
  top: 34%;
  animation: drift 16s linear infinite;
}

.route-line-b {
  bottom: 24%;
  animation: drift 22s linear infinite reverse;
}

.moving-bus {
  position: absolute;
  display: grid;
  place-items: center;
  color: white;
}

.bus-a {
  top: 31%;
  animation: busMove 18s linear infinite;
}

.bus-b {
  bottom: 21%;
  animation: busMove 24s linear infinite reverse;
}

@keyframes drift {
  from { transform: translateX(0) rotate(-10deg); }
  to { transform: translateX(120px) rotate(-10deg); }
}

@keyframes busMove {
  from { left: -80px; transform: rotate(-10deg); }
  to { left: calc(100% + 80px); transform: rotate(-10deg); }
}
</style>
