<template>
  <div class="panel rounded-lg p-4">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <p class="text-xs font-bold uppercase text-slate-500">Boleto</p>
        <h3 class="text-xl font-black text-slate-900">{{ boleto?.codigo_boleto || 'Sin emitir' }}</h3>
        <p class="mt-1 text-sm font-black text-teal-700">Viaje {{ boleto?.viaje?.codigo_viaje }}</p>
        <p class="text-sm text-slate-600">{{ boleto?.viaje?.ruta?.origen }} - {{ boleto?.viaje?.ruta?.destino }}</p>
        <p class="text-sm text-slate-500">
          <span v-if="boleto?.viaje?.fecha_salida">{{ formatDate(boleto.viaje.fecha_salida) }} - {{ formatTime(boleto.viaje.fecha_salida) }}</span>
          <span v-else>Fecha no disponible</span>
        </p>
      </div>
      <div class="flex flex-col items-center gap-2">
        <div class="rounded-md border border-slate-200 bg-white p-2">
          <img v-if="boleto?.qr_imagen" class="h-32 w-32" :src="`data:image/png;base64,${boleto.qr_imagen}`" alt="QR boleto" />
          <div v-else class="grid h-32 w-32 place-items-center bg-slate-100 text-xs font-bold text-slate-500">QR</div>
        </div>
        <span v-if="boleto?.estado" class="chip" :class="estadoChipClass">
          {{ estadoLabel }}
        </span>
      </div>
    </div>
    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
      <div>
        <dt class="font-bold text-slate-500">Pasajero</dt>
        <dd>{{ boleto?.pasajero?.nombres }} {{ boleto?.pasajero?.apellidos }}</dd>
      </div>
      <div>
        <dt class="font-bold text-slate-500">CI Pasajero</dt>
        <dd>{{ boleto?.pasajero?.numero_ci || '-' }} {{ boleto?.pasajero?.expedido_en ? `(${boleto.pasajero.expedido_en})` : '' }}</dd>
      </div>
      <div>
        <dt class="font-bold text-slate-500">Asiento</dt>
        <dd>{{ boleto?.asiento?.numero || '-' }}</dd>
      </div>
      <div>
        <dt class="font-bold text-slate-500">Bus</dt>
        <dd>{{ boleto?.viaje?.bus?.placa || '-' }}</dd>
      </div>
      <div>
        <dt class="font-bold text-slate-500">Metodo de pago</dt>
        <dd class="capitalize">{{ boleto?.metodo_pago || '-' }}</dd>
      </div>
      <div>
        <dt class="font-bold text-slate-500">Total</dt>
        <dd>Bs {{ boleto?.precio_final }}</dd>
      </div>
    </dl>
    <div class="mt-4 flex flex-wrap gap-2">
      <RouterLink
        v-if="boleto?.codigo_boleto"
        class="btn btn-secondary flex items-center gap-2"
        :to="{ name: 'consulta-boleto', query: { codigo: boleto.codigo_boleto } }"
      >
        <Download :size="16" />
        Descargar comprobante
      </RouterLink>
      <RouterLink
        v-if="boleto?.codigo_boleto"
        class="btn btn-primary flex items-center gap-2"
        :to="{ name: 'rastrear-bus', query: { codigo: boleto.codigo_boleto } }"
      >
        <MapPin :size="16" />
        Rastrear bus
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { Download, MapPin } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
  boleto: { type: Object, default: null }
});

const estadoLabel = computed(() => {
  const estado = props.boleto?.estado;
  if (estado === 'pendiente_verificacion') return 'Huella pendiente';
  if (estado === 'pagado') return 'Pagado';
  if (estado === 'abordado') return 'Abordado';
  if (estado === 'cancelado') return 'Cancelado';
  if (estado === 'reembolsado') return 'Reembolsado';
  return estado || '-';
});

const estadoChipClass = computed(() => {
  const estado = props.boleto?.estado;
  if (estado === 'pendiente_verificacion') return 'bg-amber-100 text-amber-900';
  if (estado === 'pagado') return 'bg-emerald-100 text-emerald-800';
  if (estado === 'abordado') return 'bg-blue-100 text-blue-800';
  if (estado === 'cancelado') return 'bg-red-100 text-red-800';
  if (estado === 'reembolsado') return 'bg-gray-100 text-gray-800';
  return 'bg-slate-100 text-slate-700';
});

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatTime(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit', hour12: true });
}
</script>