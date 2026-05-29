<template>
  <section class="space-y-6">
    <div class="flex items-center justify-between gap-3">
      <h1 class="text-2xl font-bold text-slate-800">Rutas</h1>
      <button class="btn-secondary" @click="viajeStore.cargarCatalogos">
        <RefreshCcw :size="18" />
        Actualizar
      </button>
    </div>
    <div class="card">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr>
              <th>Codigo</th>
              <th>Origen</th>
              <th>Destino</th>
              <th>Distancia</th>
              <th>Precio</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ruta in viajeStore.rutas" :key="ruta.id">
              <td class="font-bold text-blue-600">{{ ruta.codigo }}</td>
              <td class="text-slate-700">{{ ruta.origen }}</td>
              <td class="text-slate-700">{{ ruta.destino }}</td>
              <td class="text-slate-700">{{ ruta.distancia_km }} km</td>
              <td class="font-semibold text-slate-800">Bs {{ ruta.precio_base }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</template>

<script setup>
import { RefreshCcw } from 'lucide-vue-next';
import { onMounted } from 'vue';
import { useViajeStore } from '../../stores/viaje';

const viajeStore = useViajeStore();
onMounted(viajeStore.cargarCatalogos);
</script>

<style scoped>
.card {
  @apply bg-white rounded-xl border border-slate-200 overflow-hidden;
}

.btn-secondary {
  @apply flex items-center justify-center gap-2 py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition-colors text-sm;
}

table {
  @apply w-full text-sm;
}

thead {
  @apply bg-slate-50 border-b border-slate-200;
}

th {
  @apply px-4 py-3 text-left font-semibold text-slate-600;
}

tbody tr {
  @apply border-b border-slate-100 last:border-0 hover:bg-slate-50;
}

td {
  @apply px-4 py-3;
}
</style>
