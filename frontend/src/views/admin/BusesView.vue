<template>
  <section class="space-y-4">
    <div class="flex items-center justify-between gap-3">
      <h1 class="text-2xl font-black text-slate-900">Buses</h1>
      <button class="btn btn-secondary" @click="viajeStore.cargarCatalogos">
        <RefreshCcw :size="18" />
        Actualizar
      </button>
    </div>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <article v-for="bus in viajeStore.buses" :key="bus.id" class="panel rounded-lg p-4">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-lg font-black text-slate-900">{{ bus.placa }}</h2>
            <p class="text-sm text-slate-600">{{ bus.marca }} {{ bus.modelo }} - {{ bus.anio }}</p>
          </div>
          <span class="chip bg-teal-100 text-teal-800">{{ bus.tipo_bus }}</span>
        </div>
        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
          <div><dt class="font-bold text-slate-500">Capacidad</dt><dd>{{ bus.capacidad }}</dd></div>
          <div><dt class="font-bold text-slate-500">Codigo bus</dt><dd class="font-mono font-black">{{ codigoBus(bus) }}</dd></div>
        </dl>
        <div class="mt-4 grid gap-2 sm:grid-cols-2">
          <RouterLink class="btn btn-secondary" :to="{ name: 'consulta-autoridad', query: { codigo: codigoBus(bus) } }">
            Ver QR/manifiesto
          </RouterLink>
          <RouterLink class="btn btn-secondary" :to="{ name: 'rastrear-bus', query: { codigo: codigoBus(bus) } }">
            Rastrear
          </RouterLink>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
import { RefreshCcw } from 'lucide-vue-next';
import { onMounted } from 'vue';
import { useViajeStore } from '../../stores/viaje';

const viajeStore = useViajeStore();
onMounted(viajeStore.cargarCatalogos);

function codigoBus(bus) {
  return bus.gps_imei || bus.placa;
}
</script>
