<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Venta de boletos</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Emitir boletos en terminal</p>
      </div>
    </div>

    <div class="grid lg:grid-cols-[420px_1fr] gap-6">
      <div class="space-y-4">
        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-5">
          <h3 class="font-semibold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-bold flex items-center justify-center">1</span>
            Seleccionar viaje
          </h3>
          <input v-model="filters.fecha" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white mb-3" type="date" @change="cargarViajes" />
          <select v-model="selectedViajeId" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white" @change="seleccionarViaje">
            <option :value="null">Seleccionar viaje</option>
            <option v-for="viaje in viajeStore.viajes" :key="viaje.id" :value="viaje.id">
              {{ viaje.codigo_viaje }} | {{ viaje.ruta?.origen }} - {{ viaje.ruta?.destino }} | {{ hora(viaje.fecha_salida) }} | Bs {{ viaje.precio_final }}
            </option>
          </select>
          <div v-if="selectedViaje" class="mt-3 p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
            <p class="font-bold text-blue-900 dark:text-blue-300">{{ selectedViaje.codigo_viaje }}</p>
            <p class="text-sm text-blue-700 dark:text-blue-400">{{ selectedViaje.ruta?.origen }} - {{ selectedViaje.ruta?.destino }} | Bus {{ selectedViaje.bus?.placa }}</p>
          </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-5">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2">
              <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-bold flex items-center justify-center">2</span>
              Pasajeros
            </h3>
            <span class="text-sm text-slate-500">{{ pasajerosListos }}/{{ cantidadPasajeros }} listos</span>
          </div>
          <select v-model.number="cantidadPasajeros" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white mb-4" @change="ajustarCantidad">
            <option v-for="numero in 10" :key="numero" :value="numero">{{ numero }} pasajero{{ numero > 1 ? 's' : '' }}</option>
          </select>
          <div class="space-y-3">
            <div v-for="(item, index) in pasajeros" :key="item.uid" class="p-4 rounded-xl border transition-all" :class="index === pasajeroActivoIndex ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700'">
              <div class="flex items-center justify-between mb-3">
                <button class="font-semibold text-slate-800 dark:text-white" @click="pasajeroActivoIndex = index">Pasajero {{ index + 1 }}</button>
                <span v-if="item.asiento" class="px-2.5 py-1 text-xs font-medium rounded-full bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400">Asiento {{ item.asiento.numero }}</span>
              </div>
              <div class="flex gap-2">
                <input v-model.trim="item.ci" class="flex-1 px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white" placeholder="CI del pasajero" @focus="pasajeroActivoIndex = index" @keyup.enter="buscarPasajeroVenta(index)" />
                <button class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600" @click="buscarPasajeroVenta(index)">
                  <Search :size="18" />
                </button>
              </div>
              <div v-if="item.pasajero" class="mt-3 p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                <p class="font-semibold text-slate-800 dark:text-white">{{ nombreCompleto(item.pasajero) }}</p>
                <p class="text-sm text-slate-600 dark:text-slate-400">CI {{ item.pasajero.numero_ci }} {{ item.pasajero.expedido_en }} | {{ edad(item.pasajero.fecha_nacimiento) }} anos</p>
                <p class="text-sm mt-1" :class="item.pasajero.tiene_huella ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400'">
                  <Fingerprint :size="14" class="inline mr-1" />
                  {{ item.pasajero.tiene_huella ? 'Huella verificada' : 'Huella no verificada' }}
                </p>
              </div>
              <div v-if="item.pasajero && esMenor(item.pasajero)" class="mt-3 p-3 rounded-lg border border-yellow-200 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20">
                <p class="flex items-center gap-2 font-semibold text-yellow-800 dark:text-yellow-300 text-sm">
                  <Link2 :size="14" />
                  Menor de edad: enlazar adulto responsable
                </p>
                <select v-model="item.adulto_resp_id" class="w-full mt-2 px-3 py-2 rounded-lg border border-yellow-300 dark:border-yellow-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-sm">
                  <option value="">Seleccionar adulto responsable</option>
                  <option v-for="adulto in adultosDisponibles(index)" :key="adulto.id" :value="adulto.id">
                    {{ nombreCompleto(adulto) }}
                  </option>
                </select>
                <p v-if="adultoNombre(item)" class="mt-2 text-xs font-semibold text-yellow-800 dark:text-yellow-300">
                  {{ adultoNombre(item) }} viajara con {{ nombreCompleto(item.pasajero) }}.
                </p>
                <p v-else class="mt-2 text-xs text-yellow-700 dark:text-yellow-400">
                  Agrega al adulto como otro pasajero para habilitar este pasaje.
                </p>
              </div>
              <p v-if="item.error" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ item.error }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-5">
          <h3 class="font-semibold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-bold flex items-center justify-center">3</span>
            Metodo de pago
          </h3>
          <select v-model="metodoPago" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white">
            <option value="efectivo">Efectivo</option>
            <option value="qr_bancario">QR bancario</option>
            <option value="tarjeta">Tarjeta</option>
          </select>
        </div>
      </div>

      <div class="space-y-4">
        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-5">
          <h3 class="font-semibold text-slate-800 dark:text-white mb-4">Seleccion de asientos</h3>
          <CroquisBus
            :asientos="asientosStore.asientos"
            :selected-id="pasajeroActivo?.asiento?.id"
            :selected-ids="asientosSeleccionados"
            :selected-labels="asientosLabels"
            :selected-kinds="asientosKinds"
            @select="seleccionarAsiento"
          />
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-5">
          <h3 class="font-semibold text-slate-800 dark:text-white mb-4">Confirmar compra</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 text-center">
              <p class="text-xs text-slate-500 mb-1">Viaje</p>
              <p class="font-bold text-slate-800 dark:text-white">{{ selectedViaje?.ruta?.codigo || '-' }}</p>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 text-center">
              <p class="text-xs text-slate-500 mb-1">Pasajeros</p>
              <p class="font-bold text-slate-800 dark:text-white">{{ pasajerosListos }}/{{ cantidadPasajeros }}</p>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 text-center">
              <p class="text-xs text-slate-500 mb-1">Asientos</p>
              <p class="font-bold text-slate-800 dark:text-white">{{ resumenAsientos }}</p>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 text-center">
              <p class="text-xs text-slate-500 mb-1">Total</p>
              <p class="font-bold text-blue-600 dark:text-blue-400">Bs {{ total }}</p>
            </div>
          </div>
          <button class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition-colors flex items-center justify-center gap-2" :disabled="emitiendo" @click="emitir">
            <Ticket :size="18" />
            {{ emitiendo ? 'Emitiendo...' : 'Confirmar compra' }}
          </button>
          <div v-if="error" class="mt-3 p-3 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-sm">{{ error }}</div>
          <div v-if="message" class="mt-3 p-3 rounded-xl bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-sm">{{ message }}</div>
        </div>

        <div v-if="boletos.length" class="grid md:grid-cols-2 gap-4">
          <BoletoDigital v-for="item in boletos" :key="item.id" :boleto="item" />
        </div>
      </div>
    </div>

    <div v-if="mostrarPago" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-800 p-6 shadow-2xl">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4">Confirmacion de pago</h3>
        <PaymentSimulator :amount="total" :method="metodoPago" @paid-change="finalizarPagoSimulado" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { Fingerprint, Link2, Search, Ticket } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { emitirBoleto } from '../../api/boletos';
import { buscarPasajero, vincularAdulto } from '../../api/pasajeros';
import BoletoDigital from '../../components/BoletoDigital.vue';
import CroquisBus from '../../components/CroquisBus.vue';
import PaymentSimulator from '../../components/PaymentSimulator.vue';
import { useAsientosStore } from '../../stores/asientos';
import { useViajeStore } from '../../stores/viaje';

const viajeStore = useViajeStore();
const asientosStore = useAsientosStore();
const route = useRoute();
const filters = ref({ fecha: new Date().toISOString().slice(0, 10) });
const selectedViajeId = ref(null);
const cantidadPasajeros = ref(1);
const pasajeros = ref([nuevoPasajero(1)]);
const pasajeroActivoIndex = ref(0);
const metodoPago = ref('efectivo');
const boletos = ref([]);
const error = ref('');
const message = ref('');
const emitiendo = ref(false);
const pagoConfirmado = ref(true);
const mostrarPago = ref(false);

const selectedViaje = computed(() => viajeStore.viajes.find((viaje) => Number(viaje.id) === Number(selectedViajeId.value)));
const pasajeroActivo = computed(() => pasajeros.value[pasajeroActivoIndex.value] || null);
const pasajerosListos = computed(() => pasajeros.value.filter((item) => item.pasajero?.id && item.asiento?.id && (!esMenor(item.pasajero) || item.adulto_resp_id)).length);
const asientosSeleccionados = computed(() => pasajeros.value.map((item) => item.asiento?.id).filter(Boolean));
const asientosLabels = computed(() => Object.fromEntries(pasajeros.value.filter((item) => item.asiento).map((item, index) => [item.asiento.id, String(index + 1)])));
const asientosKinds = computed(() => Object.fromEntries(pasajeros.value.filter((item) => item.asiento).map((item) => [item.asiento.id, kindPasajero(item)])));
const resumenAsientos = computed(() => pasajeros.value.map((item, index) => item.asiento ? `P${index + 1}: ${item.asiento.numero}` : null).filter(Boolean).join(' / ') || '-');
const total = computed(() => {
  const precio = Number(selectedViaje.value?.precio_final || 0);
  return pasajeros.value.reduce((sum, item) => {
    if (!item.pasajero?.id) return sum + precio;
    return sum + (edad(item.pasajero.fecha_nacimiento) >= 60 ? precio * 0.8 : precio);
  }, 0).toFixed(2);
});

onMounted(async () => {
  await viajeStore.cargarCatalogos();
  if (route.query.fecha) filters.value.fecha = String(route.query.fecha);
  await cargarViajes();
  if (route.query.viaje) { selectedViajeId.value = Number(route.query.viaje); await seleccionarViaje(); }
});

watch([metodoPago, total], () => { pagoConfirmado.value = metodoPago.value === 'efectivo'; mostrarPago.value = false; });

async function cargarViajes() { await viajeStore.cargarViajes({ fecha: filters.value.fecha, estado: 'en_venta', per_page: 100 }); }

async function seleccionarViaje() {
  pasajeros.value.forEach((item) => { item.asiento = null; });
  boletos.value = [];
  if (selectedViajeId.value) { await asientosStore.cargar(selectedViajeId.value); asientosStore.suscribir(selectedViajeId.value); }
}

function ajustarCantidad() {
  const actuales = pasajeros.value;
  pasajeros.value = Array.from({ length: cantidadPasajeros.value }, (_, index) => actuales[index] || nuevoPasajero(index + 1));
  if (pasajeroActivoIndex.value >= pasajeros.value.length) pasajeroActivoIndex.value = 0;
  autoAsignarAdultos();
}

async function buscarPasajeroVenta(index) {
  const item = pasajeros.value[index];
  item.error = ''; item.pasajero = null; item.asiento = null; item.adulto_resp_id = ''; pasajeroActivoIndex.value = index;
  if (!item.ci) return;
  const repetido = pasajeros.value.some((pasajero, pasajeroIndex) => pasajeroIndex !== index && pasajero.ci && String(pasajero.ci) === String(item.ci));
  if (repetido) { item.error = 'Este CI ya esta cargado en otra tarjeta de esta venta.'; return; }
  try { item.pasajero = (await buscarPasajero(item.ci)).data; autoAsignarAdultos(); } catch (err) { item.error = err.message || 'No se encontro el pasajero.'; }
}

function seleccionarAsiento(asiento) {
  const item = pasajeroActivo.value;
  error.value = '';
  if (!selectedViajeId.value) { error.value = 'Selecciona un viaje antes de elegir asiento.'; return; }
  if (!item?.pasajero?.id) { error.value = 'Busca y valida el CI del pasajero activo antes de elegir asiento.'; return; }
  if (asiento.estado !== 'disponible') return;
  const ocupadoPorOtro = pasajeros.value.some((pasajero, index) => index !== pasajeroActivoIndex.value && Number(pasajero.asiento?.id) === Number(asiento.id));
  if (ocupadoPorOtro) { error.value = 'Ese asiento ya esta elegido por otro pasajero de esta compra.'; return; }
  item.asiento = item.asiento?.id === asiento.id ? null : asiento;
}

async function emitir(pagoYaValidado = false) {
  const pagoValidado = pagoYaValidado === true;
  error.value = ''; message.value = ''; boletos.value = [];
  if (!selectedViajeId.value) { error.value = 'Selecciona un viaje.'; return; }
  if (pasajerosListos.value !== cantidadPasajeros.value) { error.value = 'Completa todos los pasajeros, sus asientos y adulto responsable si hay menor.'; return; }
  if (metodoPago.value !== 'efectivo' && !pagoValidado) { mostrarPago.value = true; return; }
  emitiendo.value = true;
  try {
    for (const item of pasajeros.value) {
      if (esMenor(item.pasajero)) await vincularAdulto(item.pasajero.id, { adulto_responsable_id: item.adulto_resp_id, tipo_relacion: 'acompanante_autorizado', numero_permiso_dna: null });
      const boleto = (await emitirBoleto({ viaje_id: selectedViajeId.value, asiento_id: item.asiento.id, pasajero_id: item.pasajero.id, metodo_pago: metodoPago.value, adulto_resp_id: item.adulto_resp_id || null })).data;
      boletos.value.push(boleto);
    }
    message.value = 'Compra confirmada. Los asientos quedaron asignados al viaje.';
    await asientosStore.cargar(selectedViajeId.value);
    pasajeros.value.forEach((item) => { item.asiento = null; });
    pagoConfirmado.value = metodoPago.value === 'efectivo';
  } catch (err) { error.value = err.message || 'No se pudo emitir la compra.'; } finally { emitiendo.value = false; }
}

function finalizarPagoSimulado(confirmado) {
  if (!confirmado || emitiendo.value) return;
  pagoConfirmado.value = true;
  message.value = 'Transaccion realizada. Generando boletos...';
  window.setTimeout(() => { mostrarPago.value = false; emitir(true); }, 800);
}

function cerrarPago() { mostrarPago.value = false; }

function nuevoPasajero(index) { return { uid: `${Date.now()}-${index}-${Math.random().toString(16).slice(2, 8)}`, ci: '', pasajero: null, asiento: null, adulto_resp_id: '', error: '' }; }

function adultosDisponibles(indexActual) { return pasajeros.value.filter((item, index) => index !== indexActual && item.pasajero?.id && !esMenor(item.pasajero)).map((item) => item.pasajero); }
function autoAsignarAdultos() { const adultos = pasajeros.value.filter((item) => item.pasajero?.id && !esMenor(item.pasajero)).map((item) => item.pasajero); pasajeros.value.forEach((item) => { if (!esMenor(item.pasajero)) return; const adultoExiste = adultos.some((adulto) => Number(adulto.id) === Number(item.adulto_resp_id)); if (!adultoExiste) item.adulto_resp_id = adultos.length === 1 ? adultos[0].id : ''; }); }
function adultoNombre(item) { const adulto = pasajeros.value.find((pasajero) => Number(pasajero.pasajero?.id) === Number(item.adulto_resp_id))?.pasajero; return adulto ? nombreCompleto(adulto) : ''; }
function menoresVinculados(itemAdulto) { if (!itemAdulto.pasajero?.id) return []; return pasajeros.value.filter((item) => esMenor(item.pasajero) && Number(item.adulto_resp_id) === Number(itemAdulto.pasajero.id)).map((item) => nombreCompleto(item.pasajero)).filter(Boolean); }
function kindPasajero(item) { if (esMenor(item.pasajero)) return 'menor'; if (edad(item.pasajero?.fecha_nacimiento) >= 60) return 'adulto_mayor'; if (menoresVinculados(item).length) return 'con_menor'; return 'adulto'; }
function esMenor(pasajero) { return pasajero ? edad(pasajero.fecha_nacimiento) < 18 : false; }
function nombreCompleto(item) { return item ? `${item.nombres || ''} ${item.apellidos || ''}`.trim() : ''; }
function textoHuella(item) { return `${String(item.numero_ci || '').slice(-6).padStart(6, '0')} huella digital de ${item.nombres}`; }
function edad(fechaNacimiento) { if (!fechaNacimiento) return 0; const today = new Date(); const birth = new Date(fechaNacimiento); let age = today.getFullYear() - birth.getFullYear(); const monthDiff = today.getMonth() - birth.getMonth(); if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) age--; return age; }
function hora(value) { return value ? new Date(value).toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit', hour12: true }) : '--:--'; }
</script>
