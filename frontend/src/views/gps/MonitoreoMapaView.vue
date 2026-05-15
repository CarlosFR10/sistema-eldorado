<template>
  <section class="grid gap-4 lg:grid-cols-[360px_1fr]">
    <aside class="space-y-4">
      <section class="panel rounded-lg p-4">
        <div class="mb-3 flex items-center justify-between gap-3">
          <h1 class="text-xl font-black text-slate-900">Monitoreo GPS</h1>
          <button class="btn btn-secondary" @click="gps.cargar">
            <RefreshCcw :size="18" />
          </button>
        </div>
        <p class="text-sm text-slate-500">Actualizado: {{ gps.lastUpdated?.toLocaleTimeString('es-BO') || '-' }}</p>
      </section>

      <section class="panel rounded-lg p-4">
        <h2 class="mb-3 font-black text-slate-800">Buses en ruta</h2>
        <div class="space-y-2">
          <button
            v-for="item in gps.buses"
            :key="item.bus?.id"
            class="w-full rounded-md border p-3 text-left"
            :class="Number(item.ubicacion?.velocidad || 0) > 100 ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-white'"
            @click="seleccionar(item)"
          >
            <div class="flex justify-between gap-2">
              <strong>{{ item.bus?.placa }}</strong>
              <span class="font-black">{{ item.ubicacion?.velocidad || 0 }} km/h</span>
            </div>
            <p class="text-sm text-slate-600">{{ item.viaje?.ruta?.codigo || 'Sin viaje activo' }}</p>
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
