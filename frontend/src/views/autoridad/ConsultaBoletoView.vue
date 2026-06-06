<template>
  <section class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900">
    <div class="relative z-10 max-w-3xl mx-auto px-6 py-8 space-y-6">
      <header class="flex items-center justify-between">
        <div>
          <RouterLink class="text-sm text-white/60 hover:text-white mb-2 inline-flex items-center gap-1" to="/consulta">
            <ArrowLeft :size="16" /> Volver a consulta
          </RouterLink>
          <h1 class="text-2xl font-bold text-white">Comprobante de boleto</h1>
        </div>
        <button v-if="boleto" class="btn-primary flex items-center gap-2" @click="imprimir">
          <Printer :size="18" />
          Imprimir
        </button>
      </header>

      <div v-if="cargando" class="card p-8 text-center">
        <LoaderCircle class="animate-spin mx-auto mb-3 text-white" :size="32" />
        <p class="text-white/60">Consultando boleto...</p>
      </div>

      <div v-else-if="error" class="card p-6 bg-red-500/20 border-red-500/30">
        <p class="text-red-300 font-bold">{{ error }}</p>
      </div>

      <div v-else-if="boleto" class="space-y-4">
        <div class="ticket bg-white rounded-2xl overflow-hidden shadow-2xl">
          <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-6 text-white">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-xs font-bold uppercase text-blue-200">Terminal El Dorado</p>
                <h2 class="text-2xl font-black mt-1">BOLETO DE VIAJE</h2>
                <p class="text-blue-200 text-sm mt-1">{{ boleto.viaje?.codigo_viaje }}</p>
              </div>
              <div class="text-right">
                <p class="text-xs text-blue-200">Codigo de boleto</p>
                <p class="font-mono font-bold text-xl">{{ boleto.codigo_boleto }}</p>
              </div>
            </div>
          </div>

          <div class="p-6">
            <div class="flex gap-6">
              <div class="border rounded-xl p-3 bg-slate-50">
                <img v-if="boleto.qr_imagen" class="w-36 h-36" :src="`data:image/png;base64,${boleto.qr_imagen}`" alt="QR" />
                <div v-else class="w-36 h-36 bg-slate-200 grid place-items-center text-slate-400">QR</div>
              </div>

              <div class="flex-1 grid grid-cols-2 gap-4">
                <div>
                  <p class="text-xs font-bold uppercase text-slate-500">Pasajero</p>
                  <p class="font-bold text-slate-900">{{ boleto.pasajero?.nombres }} {{ boleto.pasajero?.apellidos }}</p>
                </div>
                <div>
                  <p class="text-xs font-bold uppercase text-slate-500">CI</p>
                  <p class="font-bold text-slate-900">{{ boleto.pasajero?.numero_ci }} {{ boleto.pasajero?.expedido_en ? `(${boleto.pasajero.expedido_en})` : '' }}</p>
                </div>
                <div>
                  <p class="text-xs font-bold uppercase text-slate-500">Ruta</p>
                  <p class="font-bold text-slate-900">{{ boleto.viaje?.ruta?.origen }} - {{ boleto.viaje?.ruta?.destino }}</p>
                </div>
                <div>
                  <p class="text-xs font-bold uppercase text-slate-500">Fecha</p>
                  <p class="font-bold text-slate-900">{{ formatDate(boleto.viaje?.fecha_salida) }} {{ formatTime(boleto.viaje?.fecha_salida) }}</p>
                </div>
                <div>
                  <p class="text-xs font-bold uppercase text-slate-500">Bus</p>
                  <p class="font-bold text-slate-900">{{ boleto.viaje?.bus?.placa || '-' }}</p>
                </div>
                <div>
                  <p class="text-xs font-bold uppercase text-slate-500">Asiento</p>
                  <p class="font-bold text-slate-900 text-2xl">{{ boleto.asiento?.numero }}</p>
                </div>
              </div>
            </div>

            <div class="mt-6 pt-4 border-t grid grid-cols-3 gap-4 text-center">
              <div>
                <p class="text-xs text-slate-500">Metodo de pago</p>
                <p class="font-bold text-slate-900 capitalize">{{ boleto.metodo_pago }}</p>
              </div>
              <div>
                <p class="text-xs text-slate-500">Precio</p>
                <p class="font-bold text-slate-900">Bs {{ boleto.precio }}</p>
              </div>
              <div>
                <p class="text-xs text-slate-500">Total</p>
                <p class="font-black text-2xl text-teal-700">Bs {{ boleto.precio_final }}</p>
              </div>
            </div>

            <div v-if="boleto.descuento > 0" class="mt-4 p-3 bg-emerald-50 rounded-lg">
              <p class="text-sm text-emerald-800">
                <CheckCircle :size="16" class="inline mr-1" />
                Descuento aplicado: Bs {{ boleto.descuento }}
              </p>
            </div>

            <div class="mt-4 flex items-center justify-between">
              <span class="chip" :class="estadoChipClass">{{ estadoLabel }}</span>
              <p class="text-xs text-slate-500">
                Emitido: {{ formatDate(boleto.fecha_emision) }} {{ formatTime(boleto.fecha_emision) }}
              </p>
            </div>
          </div>

          <div class="bg-slate-100 p-4 text-center">
            <p class="text-xs text-slate-500">
              Este comprobante es valido para abordaje. Presenta el codigo QR o este documento.
            </p>
          </div>
        </div>

        <div class="flex gap-3">
          <RouterLink class="btn btn-primary flex-1 justify-center items-center gap-2" :to="{ name: 'rastrear-bus', query: { codigo: boleto.codigo_boleto } }">
            <MapPin :size="18" />
            Rastrear bus
          </RouterLink>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ArrowLeft, CheckCircle, LoaderCircle, MapPin, Printer } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { obtenerBoletoPorCodigo } from '../../api/boletos';

const route = useRoute();
const boleto = ref(null);
const cargando = ref(false);
const error = ref('');

const estadoLabel = computed(() => {
  const estado = boleto.value?.estado;
  if (estado === 'pendiente_verificacion') return 'Huella pendiente';
  if (estado === 'pagado') return 'Pagado';
  if (estado === 'abordado') return 'Abordado';
  if (estado === 'cancelado') return 'Cancelado';
  return estado || '-';
});

const estadoChipClass = computed(() => {
  const estado = boleto.value?.estado;
  if (estado === 'pendiente_verificacion') return 'bg-amber-100 text-amber-900';
  if (estado === 'pagado') return 'bg-emerald-100 text-emerald-800';
  if (estado === 'abordado') return 'bg-blue-100 text-blue-800';
  if (estado === 'cancelado') return 'bg-red-100 text-red-800';
  return 'bg-slate-100 text-slate-700';
});

onMounted(async () => {
  const codigo = route.query.codigo;
  if (!codigo) {
    error.value = 'No se proporciono codigo de boleto.';
    return;
  }

  cargando.value = true;
  try {
    const response = await obtenerBoletoPorCodigo(codigo);
    boleto.value = response.data;
  } catch (err) {
    error.value = err.message || 'Boleto no encontrado.';
  } finally {
    cargando.value = false;
  }
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

function imprimir() {
  window.print();
}
</script>

<style scoped>
.ticket {
  font-family: Arial, sans-serif;
}

@media print {
  .ticket {
    box-shadow: none;
    border-radius: 0;
  }
}
</style>