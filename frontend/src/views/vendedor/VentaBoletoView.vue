<template>
  <section class="space-y-5">
    <div>
      <p class="text-sm font-black uppercase text-eldorado-teal">Venta en terminal</p>
      <h1 class="text-2xl font-black text-slate-900">Emitir boleto</h1>
    </div>

    <div class="grid gap-4 lg:grid-cols-[430px_1fr]">
      <aside class="space-y-4">
        <section class="panel rounded-lg p-4">
          <h2 class="mb-3 font-black text-slate-800">1. Viaje</h2>
          <input v-model="filters.fecha" class="field mb-3" type="date" @change="cargarViajes" />
          <select v-model="selectedViajeId" class="field" @change="seleccionarViaje">
            <option :value="null">Seleccionar viaje</option>
            <option v-for="viaje in viajeStore.viajes" :key="viaje.id" :value="viaje.id">
              {{ viaje.codigo_viaje }} | {{ viaje.ruta?.origen }} - {{ viaje.ruta?.destino }} | {{ hora(viaje.fecha_salida) }} | Bs {{ viaje.precio_final }}
            </option>
          </select>
          <div v-if="selectedViaje" class="mt-3 rounded-md bg-teal-50 border border-teal-200 p-3 text-sm">
            <p class="font-black text-teal-900">{{ selectedViaje.codigo_viaje }}</p>
            <p class="font-bold text-teal-800">{{ selectedViaje.ruta?.origen }} - {{ selectedViaje.ruta?.destino }} | Bus {{ selectedViaje.bus?.placa }}</p>
            <p class="text-teal-700">{{ selectedViaje.bus?.tipo_bus }} - {{ selectedViaje.bus?.capacidad }} asientos</p>
          </div>
        </section>

        <section class="panel rounded-lg p-4">
          <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-black text-slate-800">2. Pasajeros</h2>
            <span class="chip bg-teal-100 text-teal-900">{{ pasajerosListos }} de {{ cantidadPasajeros }} listos</span>
          </div>

          <label class="block space-y-1">
            <span class="flex items-center gap-2 text-sm font-bold text-slate-600">
              <Users :size="16" />
              Cantidad de pasajeros
            </span>
            <select v-model.number="cantidadPasajeros" class="field" @change="ajustarCantidad">
              <option v-for="numero in 10" :key="numero" :value="numero">{{ numero }} pasajero{{ numero > 1 ? 's' : '' }}</option>
            </select>
          </label>

          <div class="mt-4 grid gap-3">
            <article
              v-for="(item, index) in pasajeros"
              :key="item.uid"
              class="rounded-lg border p-3 transition"
              :class="index === pasajeroActivoIndex ? 'border-teal-500 bg-teal-50 shadow-sm' : 'border-slate-200 bg-white'"
            >
              <div class="mb-2 flex items-center justify-between gap-2">
                <button class="text-left font-black text-slate-900" type="button" @click="pasajeroActivoIndex = index">
                  Pasajero {{ index + 1 }}
                </button>
                <span v-if="item.asiento" class="chip bg-teal-100 text-teal-900">Asiento {{ item.asiento.numero }}</span>
              </div>

              <div class="flex gap-2">
                <input v-model.trim="item.ci" class="field" placeholder="CI del pasajero" @focus="pasajeroActivoIndex = index" @keyup.enter="buscarPasajeroVenta(index)" />
                <button class="btn btn-secondary shrink-0" type="button" @click="buscarPasajeroVenta(index)">
                  <Search :size="18" />
                </button>
              </div>

              <div v-if="item.pasajero" class="mt-3 rounded-md bg-white p-3 text-sm">
                <p class="font-black text-slate-900">{{ nombreCompleto(item.pasajero) }}</p>
                <p class="text-slate-600">CI {{ item.pasajero.numero_ci }} {{ item.pasajero.expedido_en }} | {{ edad(item.pasajero.fecha_nacimiento) }} anos</p>
                <p class="mt-2 flex items-center gap-2 font-bold" :class="item.pasajero.tiene_huella ? 'text-emerald-800' : 'text-amber-800'">
                  <Fingerprint :size="16" />
                  {{ item.pasajero.tiene_huella ? textoHuella(item.pasajero) : 'Huella no verificada: debe pasar por plataforma' }}
                </p>
              </div>

              <div v-if="item.pasajero && esMenor(item.pasajero)" class="mt-3 rounded-md border border-yellow-300 bg-yellow-50 p-3 text-sm">
                <p class="flex items-center gap-2 font-black text-yellow-950">
                  <Link2 :size="16" />
                  Menor de edad: enlazar adulto
                </p>
                <select v-model="item.adulto_resp_id" class="field mt-2">
                  <option value="">Seleccionar adulto responsable</option>
                  <option v-for="adulto in adultosDisponibles(index)" :key="adulto.id" :value="adulto.id">
                    {{ nombreCompleto(adulto) }}
                  </option>
                </select>
                <p v-if="adultoNombre(item)" class="mt-2 font-bold text-yellow-950">
                  {{ adultoNombre(item) }} viajara con {{ nombreCompleto(item.pasajero) }}.
                </p>
                <p v-else class="mt-2 font-bold text-yellow-900">
                  Agrega o busca un adulto como otro pasajero de esta venta.
                </p>
              </div>

              <p v-if="item.error" class="mt-3 rounded-md bg-red-50 p-2 text-sm font-bold text-red-800">{{ item.error }}</p>
            </article>
          </div>
        </section>

        <section class="panel rounded-lg p-4">
          <h2 class="mb-3 font-black text-slate-800">3. Metodo y asiento activo</h2>
          <select v-model="metodoPago" class="field mb-3">
            <option value="efectivo">Efectivo</option>
            <option value="qr_bancario">QR bancario</option>
            <option value="tarjeta">Tarjeta</option>
          </select>

          <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
            <p class="text-sm font-bold text-slate-500">Asignando asiento a</p>
            <p class="font-black text-slate-900">
              Pasajero {{ pasajeroActivoIndex + 1 }}{{ pasajeroActivo?.pasajero ? ` - ${nombreCompleto(pasajeroActivo.pasajero)}` : '' }}
            </p>
            <p class="mt-1 text-sm font-semibold text-slate-600">Haz clic en un asiento libre del croquis para este pasajero.</p>
          </div>
        </section>
      </aside>

      <main class="space-y-4">
        <CroquisBus
          :asientos="asientosStore.asientos"
          :selected-id="pasajeroActivo?.asiento?.id"
          :selected-ids="asientosSeleccionados"
          :selected-labels="asientosLabels"
          :selected-kinds="asientosKinds"
          @select="seleccionarAsiento"
        />

        <section class="panel rounded-lg p-4">
          <h2 class="mb-3 font-black text-slate-800">Confirmar</h2>
          <div class="grid gap-3 md:grid-cols-4">
            <div><p class="text-sm font-bold text-slate-500">Viaje</p><p class="font-black">{{ selectedViaje?.ruta?.codigo || '-' }}</p></div>
            <div><p class="text-sm font-bold text-slate-500">Pasajeros</p><p class="font-black">{{ pasajerosListos }} de {{ cantidadPasajeros }}</p></div>
            <div><p class="text-sm font-bold text-slate-500">Asientos</p><p class="font-black">{{ resumenAsientos }}</p></div>
            <div><p class="text-sm font-bold text-slate-500">Total aprox.</p><p class="font-black">Bs {{ total }}</p></div>
          </div>
          <button class="btn btn-primary mt-4" :disabled="emitiendo" @click="emitir">
            <Ticket :size="18" />
            {{ emitiendo ? 'Emitiendo...' : 'Confirmar compra' }}
          </button>
          <p v-if="error" class="mt-3 rounded-md bg-red-50 p-3 text-sm font-bold text-red-800">{{ error }}</p>
          <p v-if="message" class="mt-3 rounded-md bg-emerald-50 p-3 text-sm font-bold text-emerald-800">{{ message }}</p>
        </section>

        <div v-if="boletos.length" class="grid gap-4 md:grid-cols-2">
          <BoletoDigital v-for="item in boletos" :key="item.id" :boleto="item" />
        </div>
      </main>
    </div>

    <div v-if="mostrarPago" class="fixed inset-0 z-50 grid place-items-center bg-slate-950/50 p-4">
      <div class="w-full max-w-md rounded-xl bg-white p-4 shadow-2xl">
        <div class="mb-3 flex items-center justify-between gap-3">
          <div>
            <p class="text-xs font-black uppercase text-eldorado-teal">Confirmacion de pago</p>
            <h2 class="text-xl font-black text-slate-900">Finalizar compra</h2>
          </div>
          <button class="btn btn-secondary px-3" type="button" :disabled="emitiendo" @click="cerrarPago">Cerrar</button>
        </div>
        <PaymentSimulator :amount="total" :method="metodoPago" @paid-change="finalizarPagoSimulado" />
      </div>
    </div>
  </section>
</template>

<script setup>
import { Fingerprint, Link2, Search, Ticket, Users } from 'lucide-vue-next';
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
  if (route.query.viaje) {
    selectedViajeId.value = Number(route.query.viaje);
    await seleccionarViaje();
  }
});

watch([metodoPago, total], () => {
  pagoConfirmado.value = metodoPago.value === 'efectivo';
  mostrarPago.value = false;
});

async function cargarViajes() {
  await viajeStore.cargarViajes({ fecha: filters.value.fecha, estado: 'en_venta', per_page: 100 });
}

async function seleccionarViaje() {
  pasajeros.value.forEach((item) => {
    item.asiento = null;
  });
  boletos.value = [];
  if (selectedViajeId.value) {
    await asientosStore.cargar(selectedViajeId.value);
    asientosStore.suscribir(selectedViajeId.value);
  }
}

function ajustarCantidad() {
  const actuales = pasajeros.value;
  pasajeros.value = Array.from({ length: cantidadPasajeros.value }, (_, index) => actuales[index] || nuevoPasajero(index + 1));
  if (pasajeroActivoIndex.value >= pasajeros.value.length) pasajeroActivoIndex.value = 0;
  autoAsignarAdultos();
}

async function buscarPasajeroVenta(index) {
  const item = pasajeros.value[index];
  item.error = '';
  item.pasajero = null;
  item.asiento = null;
  item.adulto_resp_id = '';
  pasajeroActivoIndex.value = index;

  if (!item.ci) return;

  const repetido = pasajeros.value.some((pasajero, pasajeroIndex) => pasajeroIndex !== index && pasajero.ci && String(pasajero.ci) === String(item.ci));
  if (repetido) {
    item.error = 'Este CI ya esta cargado en otra tarjeta de esta venta.';
    return;
  }

  try {
    item.pasajero = (await buscarPasajero(item.ci)).data;
    autoAsignarAdultos();
  } catch (err) {
    item.error = err.message || 'No se encontro el pasajero. Debe registrarse primero.';
  }
}

function seleccionarAsiento(asiento) {
  const item = pasajeroActivo.value;
  error.value = '';

  if (!selectedViajeId.value) {
    error.value = 'Selecciona un viaje antes de elegir asiento.';
    return;
  }

  if (!item?.pasajero?.id) {
    error.value = 'Busca y valida el CI del pasajero activo antes de elegir asiento.';
    return;
  }

  if (asiento.estado !== 'disponible') return;

  const ocupadoPorOtro = pasajeros.value.some((pasajero, index) => index !== pasajeroActivoIndex.value && Number(pasajero.asiento?.id) === Number(asiento.id));
  if (ocupadoPorOtro) {
    error.value = 'Ese asiento ya esta elegido por otro pasajero de esta compra.';
    return;
  }

  item.asiento = item.asiento?.id === asiento.id ? null : asiento;
}

async function emitir(pagoYaValidado = false) {
  const pagoValidado = pagoYaValidado === true;

  error.value = '';
  message.value = '';
  boletos.value = [];

  if (!selectedViajeId.value) {
    error.value = 'Selecciona un viaje.';
    return;
  }

  if (pasajerosListos.value !== cantidadPasajeros.value) {
    error.value = 'Completa todos los pasajeros, sus asientos y adulto responsable si hay menor.';
    return;
  }

  if (metodoPago.value !== 'efectivo' && !pagoValidado) {
    mostrarPago.value = true;
    return;
  }

  emitiendo.value = true;
  try {
    for (const item of pasajeros.value) {
      if (esMenor(item.pasajero)) {
        await vincularAdulto(item.pasajero.id, {
          adulto_responsable_id: item.adulto_resp_id,
          tipo_relacion: 'acompanante_autorizado',
          numero_permiso_dna: null
        });
      }

      const boleto = (await emitirBoleto({
        viaje_id: selectedViajeId.value,
        asiento_id: item.asiento.id,
        pasajero_id: item.pasajero.id,
        metodo_pago: metodoPago.value,
        adulto_resp_id: item.adulto_resp_id || null
      })).data;
      boletos.value.push(boleto);
    }

    message.value = 'Compra confirmada. Los asientos quedaron asignados al viaje.';
    await asientosStore.cargar(selectedViajeId.value);
    pasajeros.value.forEach((item) => {
      item.asiento = null;
    });
    pagoConfirmado.value = metodoPago.value === 'efectivo';
  } catch (err) {
    error.value = err.message || 'No se pudo emitir la compra.';
  } finally {
    emitiendo.value = false;
  }
}

function finalizarPagoSimulado(confirmado) {
  if (!confirmado || emitiendo.value) return;

  pagoConfirmado.value = true;
  message.value = 'Transaccion realizada. Generando boletos...';
  window.setTimeout(() => {
    mostrarPago.value = false;
    emitir(true);
  }, 800);
}

function cerrarPago() {
  mostrarPago.value = false;
}

function nuevoPasajero(index) {
  return {
    uid: `${Date.now()}-${index}-${Math.random().toString(16).slice(2, 8)}`,
    ci: '',
    pasajero: null,
    asiento: null,
    adulto_resp_id: '',
    error: ''
  };
}

function adultosDisponibles(indexActual) {
  return pasajeros.value
    .filter((item, index) => index !== indexActual && item.pasajero?.id && !esMenor(item.pasajero))
    .map((item) => item.pasajero);
}

function autoAsignarAdultos() {
  const adultos = pasajeros.value.filter((item) => item.pasajero?.id && !esMenor(item.pasajero)).map((item) => item.pasajero);

  pasajeros.value.forEach((item) => {
    if (!esMenor(item.pasajero)) return;

    const adultoExiste = adultos.some((adulto) => Number(adulto.id) === Number(item.adulto_resp_id));
    if (!adultoExiste) item.adulto_resp_id = adultos.length === 1 ? adultos[0].id : '';
  });
}

function adultoNombre(item) {
  const adulto = pasajeros.value.find((pasajero) => Number(pasajero.pasajero?.id) === Number(item.adulto_resp_id))?.pasajero;
  return adulto ? nombreCompleto(adulto) : '';
}

function menoresVinculados(itemAdulto) {
  if (!itemAdulto.pasajero?.id) return [];

  return pasajeros.value
    .filter((item) => esMenor(item.pasajero) && Number(item.adulto_resp_id) === Number(itemAdulto.pasajero.id))
    .map((item) => nombreCompleto(item.pasajero))
    .filter(Boolean);
}

function kindPasajero(item) {
  if (esMenor(item.pasajero)) return 'menor';
  if (edad(item.pasajero?.fecha_nacimiento) >= 60) return 'adulto_mayor';
  if (menoresVinculados(item).length) return 'con_menor';
  return 'adulto';
}

function esMenor(pasajero) {
  return pasajero ? edad(pasajero.fecha_nacimiento) < 18 : false;
}

function nombreCompleto(item) {
  return item ? `${item.nombres || ''} ${item.apellidos || ''}`.trim() : '';
}

function textoHuella(item) {
  return `${String(item.numero_ci || '').slice(-6).padStart(6, '0')} huella digital de ${item.nombres}`;
}

function edad(fechaNacimiento) {
  if (!fechaNacimiento) return 0;
  const today = new Date();
  const birth = new Date(fechaNacimiento);
  let age = today.getFullYear() - birth.getFullYear();
  const monthDiff = today.getMonth() - birth.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) age -= 1;
  return age;
}

function hora(value) {
  return value ? new Date(value).toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit', hour12: true }) : '--:--';
}
</script>
