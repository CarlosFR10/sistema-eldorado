<template>
  <section class="space-y-4">
    <div class="flex items-center justify-between gap-3">
      <h1 class="text-2xl font-black text-slate-900">Rutas</h1>
      <button class="btn btn-secondary" @click="viajeStore.cargarCatalogos">
        <RefreshCcw :size="18" />
        Actualizar
      </button>
    </div>
    <div class="panel rounded-lg p-4">
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Codigo</th><th>Origen</th><th>Destino</th><th>Distancia</th><th>Precio</th></tr>
          </thead>
          <tbody>
            <tr v-for="ruta in viajeStore.rutas" :key="ruta.id">
              <td class="font-black">{{ ruta.codigo }}</td>
              <td>{{ ruta.origen }}</td>
              <td>{{ ruta.destino }}</td>
              <td>{{ ruta.distancia_km }} km</td>
              <td>Bs {{ ruta.precio_base }}</td>
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
