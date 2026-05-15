<template>
  <section class="space-y-4">
    <div class="flex items-center justify-between gap-3">
      <h1 class="text-2xl font-black text-slate-900">Reportes</h1>
      <button class="btn btn-secondary" @click="cargar">
        <RefreshCcw :size="18" />
        Actualizar
      </button>
    </div>
    <div class="grid gap-4 lg:grid-cols-2">
      <article class="panel rounded-lg p-4">
        <h2 class="mb-3 font-black text-slate-800">Ventas diarias</h2>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Vendedor</th><th>Boletos</th><th>Ingresos</th></tr></thead>
            <tbody>
              <tr v-for="row in ventas" :key="row.vendedor_id">
                <td>{{ row.vendedor?.nombre || row.vendedor_id }}</td>
                <td>{{ row.boletos }}</td>
                <td>Bs {{ row.ingresos }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>
      <article class="panel rounded-lg p-4">
        <h2 class="mb-3 font-black text-slate-800">Ocupacion por ruta</h2>
        <div class="space-y-3">
          <div v-for="row in ocupacion" :key="row.codigo">
            <div class="mb-1 flex justify-between text-sm font-bold">
              <span>{{ row.codigo }}</span>
              <span>{{ row.ocupacion_porcentaje }}%</span>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
              <div class="h-full bg-eldorado-teal" :style="{ width: `${Math.min(row.ocupacion_porcentaje, 100)}%` }"></div>
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
