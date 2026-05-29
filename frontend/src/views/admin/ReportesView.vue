<template>
  <section class="space-y-6">
    <div class="flex items-center justify-between gap-3">
      <h1 class="text-2xl font-bold text-slate-800">Reportes</h1>
      <button class="btn-secondary" @click="cargar">
        <RefreshCcw :size="18" />
        Actualizar
      </button>
    </div>
    <div class="grid gap-6 lg:grid-cols-2">
      <article class="card p-5">
        <h2 class="mb-4 font-bold text-lg text-slate-800">Ventas diarias</h2>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr><th>Vendedor</th><th>Boletos</th><th>Ingresos</th></tr>
            </thead>
            <tbody>
              <tr v-for="row in ventas" :key="row.vendedor_id">
                <td class="text-slate-700">{{ row.vendedor?.nombre || row.vendedor_id }}</td>
                <td class="text-slate-700">{{ row.boletos }}</td>
                <td class="font-semibold text-slate-800">Bs {{ row.ingresos }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>
      <article class="card p-5">
        <h2 class="mb-4 font-bold text-lg text-slate-800">Ocupacion por ruta</h2>
        <div class="space-y-4">
          <div v-for="row in ocupacion" :key="row.codigo">
            <div class="mb-1.5 flex justify-between text-sm font-medium">
              <span class="text-slate-700">{{ row.codigo }}</span>
              <span class="text-blue-600">{{ row.ocupacion_porcentaje }}%</span>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
              <div class="h-full bg-blue-600 rounded-full transition-all" :style="{ width: `${Math.min(row.ocupacion_porcentaje, 100)}%` }"></div>
            </div>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
import { RefreshCcw } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { ocupacionPorRuta, ventasDiarias } from '../../api/admin';

const ventas = ref([]);
const ocupacion = ref([]);

onMounted(cargar);

async function cargar() {
  const [ventasResp, ocupacionResp] = await Promise.all([ventasDiarias(), ocupacionPorRuta()]);
  ventas.value = ventasResp.data || [];
  ocupacion.value = ocupacionResp.data || [];
}
</script>

<style scoped>
.card {
  @apply bg-white rounded-xl border border-slate-200;
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
