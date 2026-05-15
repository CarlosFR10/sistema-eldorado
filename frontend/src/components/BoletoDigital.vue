<template>
  <div class="panel rounded-lg p-4">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <p class="text-xs font-bold uppercase text-slate-500">Boleto</p>
        <h3 class="text-xl font-black text-slate-900">{{ boleto?.codigo_boleto || 'Sin emitir' }}</h3>
        <p class="mt-1 text-sm font-black text-teal-700">Viaje {{ boleto?.viaje?.codigo_viaje }}</p>
        <p class="text-sm text-slate-600">{{ boleto?.viaje?.ruta?.origen }} - {{ boleto?.viaje?.ruta?.destino }}</p>
      </div>
      <div class="rounded-md border border-slate-200 bg-white p-2">
        <img v-if="boleto?.qr_imagen" class="h-32 w-32" :src="`data:image/png;base64,${boleto.qr_imagen}`" alt="QR boleto" />
        <div v-else class="grid h-32 w-32 place-items-center bg-slate-100 text-xs font-bold text-slate-500">QR</div>
      </div>
    </div>
    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
      <div>
        <dt class="font-bold text-slate-500">Pasajero</dt>
        <dd>{{ boleto?.pasajero?.nombres }} {{ boleto?.pasajero?.apellidos }}</dd>
      </div>
      <div>
        <dt class="font-bold text-slate-500">Asiento</dt>
        <dd>{{ boleto?.asiento?.numero }}</dd>
      </div>
      <div>
        <dt class="font-bold text-slate-500">Total</dt>
        <dd>Bs {{ boleto?.precio_final }}</dd>
      </div>
    </dl>
    <RouterLink
      v-if="boleto?.codigo_boleto"
      class="btn btn-secondary mt-4 w-full justify-center"
      :to="{ name: 'rastrear-bus', query: { codigo: boleto.codigo_boleto } }"
    >
      Rastrear bus con este boleto
    </RouterLink>
  </div>
</template>

<script setup>
defineProps({
  boleto: { type: Object, default: null }
});
</script>
