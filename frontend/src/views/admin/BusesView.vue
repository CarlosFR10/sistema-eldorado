<template>
  <section class="space-y-6">
    <div class="flex items-center justify-between gap-3">
      <h1 class="text-2xl font-bold text-slate-800">Buses</h1>
      <button class="btn-secondary" @click="viajeStore.cargarCatalogos">
        <RefreshCcw :size="18" />
        Actualizar
      </button>
    </div>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <article v-for="bus in viajeStore.buses" :key="bus.id" class="card p-5">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-lg font-bold text-slate-800">{{ bus.placa }}</h2>
            <p class="text-sm text-slate-500">{{ bus.marca }} {{ bus.modelo }} - {{ bus.anio }}</p>
          </div>
          <span class="badge badge-blue">{{ bus.tipo_bus }}</span>
        </div>
        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
          <div>
            <dt class="font-medium text-slate-500">Capacidad</dt>
            <dd class="font-semibold text-slate-800">{{ bus.capacidad }}</dd>
          </div>
          <div>
            <dt class="font-medium text-slate-500">Codigo bus</dt>
            <dd class="font-mono font-bold text-blue-600">{{ codigoBus(bus) }}</dd>
          </div>
        </dl>
        <div class="mt-4 grid gap-2 sm:grid-cols-2">
          <RouterLink class="btn-secondary text-center" :to="{ name: 'consulta-autoridad', query: { codigo: codigoBus(bus) } }">
            Ver QR/manifiesto
          </RouterLink>
          <RouterLink class="btn-secondary text-center" :to="{ name: 'rastrear-bus', query: { codigo: codigoBus(bus) } }">
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

<style scoped>
.card {
  @apply bg-white rounded-xl border border-slate-200;
}

.btn-secondary {
  @apply flex items-center justify-center gap-2 py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition-colors text-sm;
}

.badge {
  @apply inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full;
}

.badge-blue {
  @apply bg-blue-100 text-blue-700;
}
</style>
