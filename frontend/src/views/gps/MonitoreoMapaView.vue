<template>
  <section class="grid gap-6 lg:grid-cols-[380px_1fr]">
    <aside class="space-y-4">
      <section class="card p-5">
        <div class="mb-3 flex items-center justify-between gap-3">
          <h1 class="text-xl font-bold text-slate-800">Monitoreo GPS</h1>
          <button class="btn-icon" @click="gps.cargar">
            <RefreshCcw :size="18" />
          </button>
        </div>
        <p class="text-sm text-slate-500">Actualizado: {{ gps.lastUpdated?.toLocaleTimeString('es-BO') || '-' }}</p>
      </section>

      <section class="card p-5">
        <h2 class="mb-3 font-bold text-lg text-slate-800">Buses en ruta</h2>
        <div class="space-y-2">
          <button
            v-for="item in gps.buses"
            :key="item.bus?.id"
            class="w-full rounded-xl border p-4 text-left transition"
            :class="Number(item.ubicacion?.velocidad || 0) > 100 ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-white hover:border-blue-300'"
            @click="seleccionar(item)"
          >
            <div class="flex justify-between gap-2">
              <strong class="text-slate-800">{{ item.bus?.placa }}</strong>
              <span class="font-bold" :class="Number(item.ubicacion?.velocidad || 0) > 100 ? 'text-red-600' : 'text-blue-600'">{{ item.ubicacion?.velocidad || 0 }} km/h</span>
            </div>
            <p class="text-sm text-slate-500 mt-1">{{ item.viaje?.ruta?.codigo || 'Sin viaje activo' }}</p>
          </button>
        </div>
      </section>
    </aside>

    <MapaGps :buses="gps.buses" :historial="historial" />
  </section>
</template>

<script setup>
import { RefreshCcw } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import { rutaBus } from '../../api/gps';
import MapaGps from '../../components/MapaGps.vue';
import { useGpsStore } from '../../stores/gps';

const gps = useGpsStore();
const historial = ref([]);
let interval = null;

onMounted(async () => {
  await gps.cargar();
  gps.suscribir();
  interval = window.setInterval(gps.cargar, 10000);
});

onUnmounted(() => window.clearInterval(interval));

async function seleccionar(item) {
  if (!item.bus?.id) return;
  const response = await rutaBus(item.bus.id);
  historial.value = (response.data || []).map((punto) => [Number(punto.latitud), Number(punto.longitud)]);
}
</script>

<style scoped>
.card {
  @apply bg-white rounded-xl border border-slate-200;
}

.btn-icon {
  @apply p-2 rounded-lg hover:bg-slate-100 text-slate-500;
}
</style>
