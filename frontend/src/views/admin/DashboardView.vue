<template>
  <section class="space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <p class="text-sm font-black uppercase text-eldorado-teal">Operacion diaria</p>
        <h1 class="text-2xl font-black text-slate-900">Dashboard</h1>
      </div>
      <button class="btn btn-secondary" @click="cargar">
        <RefreshCcw :size="18" />
        Actualizar
      </button>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
      <article v-for="item in kpis" :key="item.label" class="panel rounded-lg p-4">
        <p class="text-sm font-bold text-slate-500">{{ item.label }}</p>
        <p class="mt-2 text-3xl font-black text-slate-900">{{ item.value }}</p>
      </article>
    </div>

    <div class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
      <section class="panel rounded-lg p-4">
        <h2 class="mb-3 font-black text-slate-800">Ingresos por fecha</h2>
        <div class="space-y-3">
          <div v-for="row in ingresos" :key="row.fecha" class="grid grid-cols-[120px_1fr_90px] items-center gap-3 text-sm">
            <span class="font-bold text-slate-600">{{ row.fecha }}</span>
            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
              <div class="h-full bg-eldorado-teal" :style="{ width: barWidth(row.ingresos) }"></div>
            </div>
            <span class="text-right font-black">Bs {{ row.ingresos || 0 }}</span>
          </div>
        </div>
      </section>

      <section class="panel rounded-lg p-4">
        <h2 class="mb-3 font-black text-slate-800">Viajes de hoy</h2>
        <div class="space-y-2">
          <div v-for="viaje in viajes" :key="viaje.id" class="rounded-md border border-slate-200 bg-white p-3">
            <div class="flex items-center justify-between gap-2">
              <strong>{{ viaje.ruta?.codigo }}</strong>
              <EstadoSemaforo :estado="viaje.estado" />
            </div>
            <p class="text-sm text-slate-600">{{ viaje.bus?.placa }} - {{ formatDate(viaje.fecha_salida) }}</p>
          </div>
        </div>
      </section>
    </div>
  </section>
</template>

<script setup>
import { RefreshCcw } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { ingresos as ingresosApi, ventasDiarias } from '../../api/admin';
import { listarViajes } from '../../api/viajes';
import EstadoSemaforo from '../../components/EstadoSemaforo.vue';

const ventas = ref([]);
const ingresos = ref([]);
const viajes = ref([]);

const kpis = computed(() => {
  const boletos = ventas.value.reduce((sum, row) => sum + Number(row.boletos || 0), 0);
  const total = ventas.value.reduce((sum, row) => sum + Number(row.ingresos || 0), 0);
  return [
    { label: 'Boletos vendidos', value: boletos },
    { label: 'Ingresos', value: `Bs ${total.toFixed(2)}` },
    { label: 'Buses programados', value: viajes.value.length },
    { label: 'Viajes abordando', value: viajes.value.filter((v) => v.estado === 'abordando').length }
  ];
});

onMounted(cargar);

async function cargar() {
  const [ventasResp, ingresosResp, viajesResp] = await Promise.all([
    ventasDiarias(),
    ingresosApi(),
    listarViajes({ fecha: new Date().toISOString().slice(0, 10) })
  ]);
  ventas.value = ventasResp.data || [];
  ingresos.value = ingresosResp.data || [];
  viajes.value = viajesResp.data?.data || viajesResp.data || [];
}

function barWidth(value) {
  const max = Math.max(...ingresos.value.map((row) => Number(row.ingresos || 0)), 1);
  return `${Math.max(5, (Number(value || 0) / max) * 100)}%`;
}

function formatDate(value) {
  return value ? new Date(value).toLocaleString('es-BO') : '-';
}
</script>
