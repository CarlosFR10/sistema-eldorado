<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900">
    <main class="max-w-7xl mx-auto px-6 py-8">
      <div class="text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
          Compra tus <span class="text-blue-400">pasajes</span>
        </h1>
        <p class="text-lg text-white/60 max-w-2xl mx-auto">
          Viaja comodo y seguro desde Cochabamba hacia cualquier destino
        </p>
      </div>

      <div class="grid lg:grid-cols-[400px_1fr] gap-8">
        <div class="space-y-4">
          <div class="rounded-2xl bg-white/5 border border-white/10 p-5 backdrop-blur">
            <div class="flex items-center gap-3 mb-4">
              <CalendarDays :size="20" class="text-blue-400" />
              <h3 class="font-semibold text-white">Fecha de viaje</h3>
            </div>
            <input v-model="fecha" class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" type="date" @change="recargarTodo" />
          </div>

          <div class="rounded-2xl bg-white/5 border border-white/10 p-5 backdrop-blur">
            <div class="flex items-center gap-3 mb-4">
              <MapPin :size="20" class="text-blue-400" />
              <h3 class="font-semibold text-white">Destinos desde Cochabamba</h3>
            </div>
            <div v-if="cargando" class="text-center py-8 text-white/50">Cargando...</div>
            <div v-else-if="rutas.length === 0" class="text-center py-8 text-white/50">No hay viajes disponibles</div>
            <div v-else class="space-y-2">
              <button
                v-for="ruta in rutas"
                :key="ruta.id"
                class="w-full p-4 rounded-xl text-left transition-all"
                :class="Number(rutaSeleccionadaId) === Number(ruta.id)
                  ? 'bg-blue-600 text-white'
                  : 'bg-white/5 text-white/80 hover:bg-white/10'"
                @click="seleccionarRuta(ruta)"
              >
                <div class="flex justify-between items-start">
                  <div>
                    <p class="font-semibold">{{ ruta.destino }}</p>
                    <p class="text-sm opacity-70">{{ ruta.codigo }}</p>
                  </div>
                  <div class="text-right">
                    <p class="font-bold">Bs {{ ruta.precio_base }}</p>
                    <p class="text-sm opacity-70">{{ ruta.duracion_horas }}h</p>
                  </div>
                </div>
              </button>
            </div>
          </div>

          <div class="rounded-2xl bg-white/5 border border-white/10 p-5 backdrop-blur">
            <div class="flex items-center gap-3 mb-4">
              <Clock :size="20" class="text-blue-400" />
              <h3 class="font-semibold text-white">Horarios</h3>
            </div>
            <div v-if="cargandoViajes" class="text-center py-8 text-white/50">Cargando...</div>
            <div v-else-if="viajes.length === 0" class="text-center py-8 text-white/50">Selecciona un destino</div>
            <div v-else class="space-y-2">
              <button
                v-for="viaje in viajes"
                :key="viaje.id"
                class="w-full p-4 rounded-xl text-left transition-all"
                :class="Number(viajeSeleccionadoId) === Number(viaje.id)
                  ? 'bg-blue-600 text-white'
                  : 'bg-white/5 text-white/80 hover:bg-white/10'"
                @click="seleccionarViaje(viaje)"
              >
                <div class="flex justify-between items-center">
                  <div>
                    <p class="font-bold text-lg">{{ hora(viaje.fecha_salida) }}</p>
                    <p class="text-sm opacity-70">Bus {{ viaje.bus?.placa }}</p>
                  </div>
                  <div class="text-right">
                    <p class="font-bold text-lg">{{ viaje.asientos_disponibles_count ?? '-' }}</p>
                    <p class="text-sm opacity-70">libres</p>
                  </div>
                </div>
              </button>
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div class="rounded-2xl bg-white/5 border border-white/10 p-5 backdrop-blur">
            <div class="flex items-center gap-3 mb-4">
              <Bus :size="20" class="text-blue-400" />
              <h3 class="font-semibold text-white">Seleccion de asientos</h3>
            </div>
            <CroquisBus
              v-if="viajeSeleccionado && asientos.length"
              :asientos="asientos"
              :selected-id="pasajeros[pasajeroActivoIndex]?.asiento?.id"
              :selected-ids="asientosSeleccionados"
              :selected-labels="asientosLabels"
              :selected-kinds="asientosKinds"
              @select="seleccionarAsiento"
            />
            <div v-else-if="viajeSeleccionado" class="text-center py-16 text-white/50">Cargando asientos...</div>
            <div v-else class="text-center py-16 text-white/50">
              Selecciona destino y horario para ver los asientos
            </div>
          </div>

          <div class="rounded-2xl bg-white/5 border border-white/10 p-5 backdrop-blur">
            <div class="flex items-center gap-3 mb-4">
              <Users :size="20" class="text-blue-400" />
              <h3 class="font-semibold text-white">Pasajeros ({{ pasajerosListos }}/{{ cantidad }})</h3>
            </div>
            <select v-model.number="cantidad" class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/10 text-white mb-4" @change="ajustarCantidad">
              <option v-for="n in 10" :key="n" :value="n">{{ n }} pasajero{{ n > 1 ? 's' : '' }}</option>
            </select>
            <div class="space-y-3">
              <div v-for="(item, index) in pasajeros" :key="item.uid" class="p-4 rounded-xl bg-white/5">
                <div class="flex items-center justify-between mb-3">
                  <button class="font-semibold text-white" @click="pasajeroActivoIndex = index">Pasajero {{ index + 1 }}</button>
                  <span v-if="item.asiento" class="px-2.5 py-1 text-xs font-medium rounded-full bg-blue-500/20 text-blue-300">
                    Asiento {{ item.asiento.numero }}
                  </span>
                </div>
                <div class="flex gap-2">
                  <input v-model="item.ci" class="flex-1 px-3 py-2 rounded-lg bg-white/10 border border-white/10 text-white placeholder-white/40" placeholder="CI" />
                  <button class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-500" @click="buscarPasajero(index)">
                    <Search :size="18" />
                  </button>
                </div>
                <div v-if="item.pasajero" class="mt-3 p-3 rounded-lg bg-white/5">
                  <p class="font-medium text-white">{{ nombreCompleto(item.pasajero) }}</p>
                  <p class="text-sm text-white/60">CI: {{ item.pasajero.numero_ci }} | {{ edad(item.pasajero.fecha_nacimiento) }} anos</p>
                  <p class="text-sm mt-1" :class="item.pasajero.tiene_huella ? 'text-green-400' : 'text-amber-400'">
                    <Fingerprint :size="14" class="inline mr-1" />
                    {{ item.pasajero.tiene_huella ? 'Huella verificada' : 'Huella pendiente' }}
                  </p>
                </div>
                <p v-if="item.error" class="mt-2 text-sm text-red-400">{{ item.error }}</p>
              </div>
            </div>
          </div>

          <div class="rounded-2xl bg-gradient-to-br from-blue-600/20 to-blue-800/20 border border-blue-500/20 p-5 backdrop-blur">
            <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
              <Receipt :size="18" class="text-blue-400" />
              Resumen
            </h3>
            <dl class="space-y-2 text-sm">
              <div class="flex justify-between">
                <dt class="text-white/60">Destino</dt>
                <dd class="text-white font-medium">{{ viajeSeleccionado?.ruta?.destino || '-' }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-white/60">Horario</dt>
                <dd class="text-white font-medium">{{ viajeSeleccionado ? hora(viajeSeleccionado.fecha_salida) : '-' }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-white/60">Asientos</dt>
                <dd class="text-white font-medium">{{ asientosNombres || '-' }}</dd>
              </div>
              <div class="flex justify-between pt-2 border-t border-white/10">
                <dt class="text-white font-semibold">Total</dt>
                <dd class="text-white font-bold text-xl">Bs {{ total }}</dd>
              </div>
            </dl>
            <div class="mt-4 space-y-3">
              <select v-model="metodoPago" class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/10 text-white">
                <option value="efectivo">Efectivo</option>
                <option value="qr_bancario">QR bancario</option>
                <option value="tarjeta">Tarjeta</option>
              </select>
              <button class="w-full py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-colors flex items-center justify-center gap-2" :disabled="reservando" @click="reservar">
                <Ticket :size="18" />
                {{ reservando ? 'Procesando...' : 'Comprar ahora' }}
              </button>
            </div>
            <div v-if="error" class="mt-3 p-3 rounded-lg bg-red-500/20 text-red-300 text-sm">{{ error }}</div>
            <div v-if="mensaje" class="mt-3 p-3 rounded-lg bg-green-500/20 text-green-300 text-sm">{{ mensaje }}</div>
          </div>
        </div>
      </div>

      <div v-if="boletos.length" class="mt-8">
        <h2 class="text-xl font-bold text-white mb-4">Boletos generados</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="boleto in boletos" :key="boleto.id" class="p-4 rounded-xl bg-white/5 border border-white/10">
            <p class="font-mono font-bold text-blue-400">{{ boleto.codigo_boleto }}</p>
            <p class="text-white mt-1">{{ boleto.pasajero?.nombres }} {{ boleto.pasajero?.apellidos }}</p>
            <p class="text-sm text-white/60">Asiento {{ boleto.asiento?.numero }}</p>
          </div>
        </div>
      </div>
    </main>
  </div>

  <Teleport to="body">
    <div v-if="mostrarPago" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" @click.self="cerrarPago">
      <div class="w-full max-w-md rounded-2xl bg-slate-800 border border-white/10 p-6">
        <h3 class="text-lg font-bold text-white mb-4">Confirmacion de pago</h3>
        <PaymentSimulator :amount="total" :method="metodoPago" @paid-change="finalizarPagoSimulado" />
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { CalendarDays, Clock, Bus, Fingerprint, MapPin, Receipt, Search, Ticket, Users } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import CroquisBus from '../../components/CroquisBus.vue';
import PaymentSimulator from '../../components/PaymentSimulator.vue';
import { publicAsientos, publicBuscarPasajero, publicPreRegistrarPasajero, publicReservarBoletos, publicRutas, publicViajes } from '../../api/public';

const fecha = ref(new Date().toISOString().slice(0, 10));
const rutas = ref([]);
const viajes = ref([]);
const asientos = ref([]);
const rutaSeleccionadaId = ref('');
const viajeSeleccionadoId = ref('');
const viajeSeleccionado = ref(null);
const cantidad = ref(1);
const pasajeros = ref([nuevoPasajero(1)]);
const pasajeroActivoIndex = ref(0);
const metodoPago = ref('efectivo');
const boletos = ref([]);
const mensaje = ref('');
const error = ref('');
const reservando = ref(false);
const mostrarPago = ref(false);
const cargando = ref(false);
const cargandoViajes = ref(false);
let refrescarInterval = null;

onMounted(async () => { await recargarTodo(); refrescarInterval = window.setInterval(recargarHorarios, 8000); });
onUnmounted(() => { if (refrescarInterval) clearInterval(refrescarInterval); });

async function recargarHorarios() {
  if (!rutaSeleccionadaId.value) return;
  try {
    const response = await publicViajes({ fecha: fecha.value, ruta_id: rutaSeleccionadaId.value, per_page: 50 });
    let data = Array.isArray(response) ? response : (response?.data || []);
    if (!Array.isArray(data)) data = response?.data?.data || [];
    viajes.value = data.filter((v) => v.estado !== 'cancelado');
    const viajeExiste = viajes.value.some((v) => Number(v.id) === Number(viajeSeleccionadoId.value));
    if (!viajeExiste && viajes.value.length) await seleccionarViaje(viajes.value[0]);
    else if (!viajeExiste) { viajeSeleccionadoId.value = ''; viajeSeleccionado.value = null; asientos.value = []; }
  } catch (e) {}
}

async function recargarTodo() {
  cargando.value = true; cargandoViajes.value = true;
  try {
    const [rutasRes, viajesRes] = await Promise.all([publicRutas(), publicViajes({ fecha: fecha.value, per_page: 500 })]);
    let listaRutas = Array.isArray(rutasRes) ? rutasRes : (rutasRes?.data?.data || rutasRes?.data || []);
    let viajesData = Array.isArray(viajesRes) ? viajesRes : (viajesRes?.data?.data || viajesRes?.data || []);
    rutas.value = listaRutas.filter((r) => String(r.origen || '').toLowerCase() === 'cochabamba').sort((a, b) => a.destino.localeCompare(b.destino, 'es'));
    viajes.value = viajesData.filter((v) => v.estado !== 'cancelado');
    if (rutas.value.length && !rutaSeleccionadaId.value) await seleccionarRuta(rutas.value[0]);
    else if (rutas.value.length && rutaSeleccionadaId.value) { const rutaExistente = rutas.value.find((r) => Number(r.id) === Number(rutaSeleccionadaId.value)); if (rutaExistente) await cargarViajesPorRuta(rutaExistente); else await seleccionarRuta(rutas.value[0]); }
  } catch (e) { console.error('Error:', e); } finally { cargando.value = false; cargandoViajes.value = false; }
}

async function seleccionarRuta(ruta) {
  rutaSeleccionadaId.value = ruta.id;
  await cargarViajesPorRuta(ruta);
}

async function cargarViajesPorRuta(ruta) {
  cargandoViajes.value = true;
  try {
    const response = await publicViajes({ fecha: fecha.value, ruta_id: ruta.id, per_page: 50 });
    let data = Array.isArray(response) ? response : (response?.data || []);
    if (!Array.isArray(data)) data = response?.data?.data || [];
    viajes.value = data.filter((v) => v.estado !== 'cancelado');
    if (viajes.value.length) await seleccionarViaje(viajes.value[0]);
    else { viajeSeleccionadoId.value = ''; viajeSeleccionado.value = null; asientos.value = []; }
  } catch (e) { console.error('Error:', e); } finally { cargandoViajes.value = false; }
}

async function seleccionarViaje(viaje) {
  viajeSeleccionadoId.value = viaje.id;
  viajeSeleccionado.value = viaje;
  asientos.value = [];
  try {
    const res = await publicAsientos(viaje.id);
    asientos.value = Array.isArray(res) ? res : (res?.data || res?.data?.data || []);
    pasajeros.value.forEach((item) => { item.asiento = null; });
  } catch (e) { console.error('Error:', e); asientos.value = []; }
}

function ajustarCantidad() {
  const actuales = pasajeros.value;
  pasajeros.value = Array.from({ length: cantidad.value }, (_, index) => actuales[index] || nuevoPasajero(index + 1));
  if (pasajeroActivoIndex.value >= pasajeros.value.length) pasajeroActivoIndex.value = 0;
  autoAsignarAdultos();
}

async function buscarPasajero(index) {
  const item = pasajeros.value[index];
  item.error = ''; item.pasajero = null; item.modoRegistro = false;
  if (!item.ci) return;
  try {
    const response = await publicBuscarPasajero(item.ci);
    item.pasajero = response.data;
    item.form = pasajeroForm(response.data);
    autoAsignarAdultos();
  } catch { item.modoRegistro = true; item.form.numero_ci = item.ci; item.error = 'No registrado. Puede pre-registrarse.'; }
}

async function preRegistrar(index) {
  const item = pasajeros.value[index];
  item.error = '';
  try {
    const response = await publicPreRegistrarPasajero(item.form);
    item.pasajero = response.data;
    item.modoRegistro = false;
    autoAsignarAdultos();
  } catch (err) { item.error = err.message || 'Error al pre-registrar.'; }
}

function seleccionarAsiento(asiento) {
  const item = pasajeros.value[pasajeroActivoIndex.value];
  error.value = '';
  if (!viajeSeleccionado.value) { error.value = 'Selecciona un viaje primero.'; return; }
  if (asiento.estado !== 'disponible') return;
  const ocupadoPorOtro = pasajeros.value.some((p, i) => i !== pasajeroActivoIndex.value && Number(p.asiento?.id) === Number(asiento.id));
  if (ocupadoPorOtro) { error.value = 'Ese asiento ya esta elegido.'; return; }
  item.asiento = asiento;
}

async function reservar(pagoYaValidado = false) {
  error.value = ''; mensaje.value = ''; boletos.value = '';
  if (!viajeSeleccionado.value) { error.value = 'Selecciona destino y horario.'; return; }
  if (pasajerosListos.value !== cantidad.value) { error.value = 'Completa todos los pasajeros y asientos.'; return; }
  if (metodoPago.value !== 'efectivo' && !pagoYaValidado) { mostrarPago.value = true; return; }
  reservando.value = true;
  try {
    const response = await publicReservarBoletos({
      viaje_id: viajeSeleccionado.value.id,
      metodo_pago: metodoPago.value,
      pasajes: pasajeros.value.map((item) => ({ pasajero_id: item.pasajero.id, asiento_id: item.asiento.id, adulto_resp_id: item.adulto_resp_id || null }))
    });
    boletos.value = response.data.boletos || [];
    mensaje.value = response.data.requiere_verificacion_huella ? 'Reserva generada. Hay pasajeros con huella pendiente.' : 'Compra generada correctamente.';
    const asientosResponse = await publicAsientos(viajeSeleccionado.value.id);
    asientos.value = asientosResponse.data || [];
  } catch (err) { error.value = err.message || 'No se pudo generar la reserva.'; } finally { reservando.value = false; }
}

function finalizarPagoSimulado(confirmado) {
  if (!confirmado || reservando.value) return;
  mensaje.value = 'Transaccion realizada. Generando boletos...';
  window.setTimeout(() => { mostrarPago.value = false; reservar(true); }, 800);
}

function cerrarPago() { mostrarPago.value = false; }

function nuevoPasajero(index) {
  return { uid: `${Date.now()}-${index}-${Math.random().toString(16).slice(2, 8)}`, ci: '', pasajero: null, asiento: null, adulto_resp_id: '', modoRegistro: false, form: pasajeroForm(), error: '' };
}

function pasajeroForm(pasajero = {}) {
  return { nombres: pasajero.nombres || '', apellidos: pasajero.apellidos || '', numero_ci: pasajero.numero_ci || '', complemento_ci: pasajero.complemento_ci || '', expedido_en: pasajero.expedido_en || 'CB', fecha_nacimiento: pasajero.fecha_nacimiento || '', telefono: pasajero.telefono || '', email: pasajero.email || '' };
}

function adultosDisponibles(indexActual) {
  return pasajeros.value.filter((item, index) => index !== indexActual && item.pasajero?.id && !esMenor(item.pasajero)).map((item) => item.pasajero);
}

function autoAsignarAdultos() {
  const adultos = pasajeros.value.filter((item) => item.pasajero?.id && !esMenor(item.pasajero)).map((item) => item.pasajero);
  pasajeros.value.forEach((item) => { if (!esMenor(item.pasajero)) return; const adultoExiste = adultos.some((adulto) => Number(adulto.id) === Number(item.adulto_resp_id)); if (!adultoExiste) item.adulto_resp_id = adultos.length === 1 ? adultos[0].id : ''; });
}

function esMenor(pasajero) { return pasajero ? edad(pasajero.fecha_nacimiento) < 18 : false; }
function edad(fechaNacimiento) { if (!fechaNacimiento) return 0; const today = new Date(); const birth = new Date(fechaNacimiento); let age = today.getFullYear() - birth.getFullYear(); const monthDiff = today.getMonth() - birth.getMonth(); if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) age--; return age; }
function nombreCompleto(pasajero) { return pasajero ? `${pasajero.nombres || ''} ${pasajero.apellidos || ''}`.trim() : ''; }
function hora(value) { return value ? new Date(value).toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit', hour12: true }) : '--:--'; }

const pasajerosListos = computed(() => pasajeros.value.filter((p) => p.pasajero?.id && p.asiento?.id).length);
const asientosSeleccionados = computed(() => pasajeros.value.filter((p) => p.asiento?.id).map((p) => p.asiento.id));
const asientosLabels = computed(() => pasajeros.value.filter((p) => p.asiento?.id).reduce((acc, p) => { acc[p.asiento.id] = p.pasajero?.nombres; return acc; }, {}));
const asientosKinds = computed(() => { const kinds = {}; pasajeros.value.forEach((p) => { if (!p.asiento?.id || !p.pasajero) return; kinds[p.asiento.id] = esMenor(p.pasajero) ? 'menor' : (edad(p.pasajero?.fecha_nacimiento) >= 60 ? 'adulto_mayor' : 'adulto'); }); return kinds; });
const asientosNombres = computed(() => pasajeros.value.map((p) => p.asiento?.numero).filter(Boolean).join(', ') || '-');
const total = computed(() => { if (!viajeSeleccionado.value) return 0; return pasajeros.value.filter((p) => p.asiento?.id).length * Number(viajeSeleccionado.value.precio_final || 0); });
</script>

<style scoped>
select.bg-white\/10 {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  padding-right: 40px;
  cursor: pointer;
}

select.bg-white\/10 option {
  background-color: #1e293b;
  color: white;
  padding: 8px;
}
</style>
