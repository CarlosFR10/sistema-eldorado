<template>
  <div class="panel rounded-lg p-4">
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
      <div>
        <h3 class="text-lg font-black text-slate-900">Manifiesto {{ viaje?.codigo_viaje }}</h3>
        <p class="text-sm text-slate-600">{{ viaje?.ruta?.origen }} - {{ viaje?.ruta?.destino }}</p>
      </div>
      <EstadoSemaforo v-if="viaje?.estado" :estado="viaje.estado" />
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Asiento</th>
            <th>Pasajero</th>
            <th>CI</th>
            <th>Estado</th>
            <th>Adulto resp.</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="boleto in viaje?.boletos || []" :key="boleto.id">
            <td>{{ boleto.asiento?.numero }}</td>
            <td>{{ boleto.pasajero?.nombres }} {{ boleto.pasajero?.apellidos }}</td>
            <td>{{ boleto.pasajero?.numero_ci }}</td>
            <td><EstadoSemaforo :estado="boleto.estado" /></td>
            <td>{{ boleto.adulto_responsable?.nombres || '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import EstadoSemaforo from './EstadoSemaforo.vue';

defineProps({
  viaje: { type: Object, default: null }
});
</script>
