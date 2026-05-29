<template>
  <div class="boleteria">
    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-content">
        <h1 class="hero-title">Compra tus pasajes</h1>
        <p class="hero-subtitle">Viaja comodo y seguro con Terminal El Dorado</p>
      </div>
    </section>

    <!-- Main Content -->
    <section class="boleteria-content">
      <div class="boleteria-grid">
        <!-- Left Column - Selection -->
        <div class="selection-column">
          <!-- Date Selection -->
          <div class="card">
            <div class="card-header">
              <CalendarDays :size="20" />
              <h3>Fecha de viaje</h3>
            </div>
            <div class="card-body">
              <input v-model="fecha" class="form-input" type="date" @change="recargarTodo" />
            </div>
          </div>

          <!-- Routes -->
          <div class="card">
            <div class="card-header">
              <MapPin :size="20" />
              <h3>Destinos desde Cochabamba</h3>
            </div>
            <div class="card-body">
              <div v-if="cargando" class="loading">
                <span class="spinner"></span> Cargando destinos...
              </div>
              <div v-else-if="rutas.length === 0" class="empty-message">
                No hay viajes para esta fecha
              </div>
              <div v-else class="routes-list">
                <button
                  v-for="ruta in rutas"
                  :key="ruta.id"
                  class="route-item"
                  :class="{ 'is-selected': Number(rutaSeleccionadaId) === Number(ruta.id) }"
                  @click="seleccionarRuta(ruta)"
                >
                  <div class="route-info">
                    <span class="route-destination">{{ ruta.destino }}</span>
                    <span class="route-code">{{ ruta.codigo }}</span>
                  </div>
                  <div class="route-price">
                    <span class="price">Bs {{ ruta.precio_base }}</span>
                    <span class="duration">{{ ruta.duracion_horas }}h</span>
                  </div>
                </button>
              </div>
            </div>
          </div>

          <!-- Schedules -->
          <div class="card">
            <div class="card-header">
              <Clock :size="20" />
              <h3>Horarios disponibles</h3>
            </div>
            <div class="card-body">
              <div v-if="cargandoViajes" class="loading">
                <span class="spinner"></span> Cargando horarios...
              </div>
              <div v-else-if="viajes.length === 0" class="empty-message">
                Selecciona un destino
              </div>
              <div v-else class="schedules-list">
                <button
                  v-for="viaje in viajes"
                  :key="viaje.id"
                  class="schedule-item"
                  :class="{ 'is-selected': Number(viajeSeleccionadoId) === Number(viaje.id) }"
                  @click="seleccionarViaje(viaje)"
                >
                  <div class="schedule-time">{{ hora(viaje.fecha_salida) }}</div>
                  <div class="schedule-info">
                    <span class="schedule-bus">Bus {{ viaje.bus?.placa }}</span>
                    <span class="schedule-code">{{ viaje.codigo_viaje }}</span>
                  </div>
                  <div class="schedule-seats">
                    <span class="seats-available">{{ viaje.asientos_disponibles_count ?? '-' }}</span>
                    <span class="seats-label">libres</span>
                  </div>
                </button>
              </div>
            </div>
          </div>

          <!-- Passengers -->
          <div class="card">
            <div class="card-header">
              <Users :size="20" />
              <h3>Pasajes</h3>
            </div>
            <div class="card-body">
              <label class="form-label">Cantidad de pasajeros</label>
              <select v-model.number="cantidad" class="form-input" @change="ajustarCantidad">
                <option v-for="n in 10" :key="n" :value="n">{{ n }} pasajero{{ n > 1 ? 's' : '' }}</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Right Column - Bus Map & Summary -->
        <div class="results-column">
          <!-- Bus Map -->
          <div class="card">
            <div class="card-header">
              <Bus :size="20" />
              <h3>Seleccion de asientos</h3>
            </div>
            <div class="card-body">
              <CroquisBus
                v-if="viajeSeleccionado && asientos.length"
                :asientos="asientos"
                :selected-id="pasajeros[pasajeroActivoIndex]?.asiento?.id"
                :selected-ids="asientosSeleccionados"
                :selected-labels="asientosLabels"
                :selected-kinds="asientosKinds"
                @select="seleccionarAsiento"
              />
              <div v-else-if="viajeSeleccionado" class="loading">
                <span class="spinner"></span> Cargando asientos...
              </div>
              <div v-else class="empty-message">
                Selecciona destino y horario para ver los asientos disponibles
              </div>
            </div>
          </div>

          <!-- Summary -->
          <div class="card summary-card">
            <div class="card-header">
              <Receipt :size="20" />
              <h3>Resumen de compra</h3>
            </div>
            <div class="card-body">
              <dl class="summary-list">
                <div class="summary-item">
                  <dt>Destino</dt>
                  <dd>{{ viajeSeleccionado?.ruta?.destino || '-' }}</dd>
                </div>
                <div class="summary-item">
                  <dt>Horario</dt>
                  <dd>{{ viajeSeleccionado ? hora(viajeSeleccionado.fecha_salida) : '-' }}</dd>
                </div>
                <div class="summary-item">
                  <dt>Pasajeros listos</dt>
                  <dd>{{ pasajerosListos }} de {{ cantidad }}</dd>
                </div>
                <div class="summary-item">
                  <dt>Asientos</dt>
                  <dd>{{ asientosNombres || '-' }}</dd>
                </div>
                <div class="summary-item total">
                  <dt>Total</dt>
                  <dd>Bs {{ total }}</dd>
                </div>
              </dl>

              <div class="payment-section">
                <label class="form-label">Metodo de pago</label>
                <select v-model="metodoPago" class="form-input">
                  <option value="efectivo">Efectivo</option>
                  <option value="qr_bancario">QR bancario</option>
                  <option value="tarjeta">Tarjeta</option>
                </select>
              </div>

              <button class="btn-primary btn-block" :disabled="reservando" @click="reservar">
                <Ticket :size="18" />
                {{ reservando ? 'Procesando...' : 'Comprar ahora' }}
              </button>

              <div v-if="error" class="alert alert-error">{{ error }}</div>
              <div v-if="mensaje" class="alert alert-success">{{ mensaje }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Passengers Form -->
      <div class="passengers-section">
        <h2 class="section-title">
          <Users :size="24" />
          Datos de pasajeros
        </h2>
        <div class="passengers-grid">
          <div
            v-for="(item, index) in pasajeros"
            :key="item.uid"
            class="passenger-card"
            :class="{ 'is-active': index === pasajeroActivoIndex }"
          >
            <div class="passenger-header">
              <button class="passenger-name" @click="pasajeroActivoIndex = index">
                Pasajero {{ index + 1 }}
                <span v-if="item.pasajero" class="passenger-ci">CI: {{ item.pasajero.numero_ci }}</span>
              </button>
              <span v-if="item.asiento" class="badge badge-teal">
                Asiento {{ item.asiento.numero }}
              </span>
            </div>

            <div class="passenger-search">
              <input v-model="item.ci" class="form-input" placeholder="Carnet de identidad" />
              <button class="btn-secondary" @click="buscarPasajero(index)">
                <Search :size="18" />
              </button>
            </div>

            <div v-if="item.pasajero" class="passenger-info">
              <p class="passenger-fullname">{{ nombreCompleto(item.pasajero) }}</p>
              <p class="passenger-details">
                {{ item.pasajero.numero_ci }} | {{ edad(item.pasajero.fecha_nacimiento) }} anos
              </p>
              <p class="passenger-huella" :class="item.pasajero.tiene_huella ? 'text-green-600' : 'text-amber-600'">
                <Fingerprint :size="16" />
                {{ item.pasajero.tiene_huella ? 'Huella verificada' : 'Huella pendiente' }}
              </p>
            </div>

            <div v-if="item.modoRegistro" class="passenger-register">
              <input v-model="item.form.nombres" class="form-input" placeholder="Nombres" />
              <input v-model="item.form.apellidos" class="form-input" placeholder="Apellidos" />
              <input v-model="item.form.fecha_nacimiento" class="form-input" type="date" />
              <input v-model="item.form.telefono" class="form-input" placeholder="Telefono" />
              <button class="btn-secondary btn-block" @click="preRegistrar(index)">
                Pre-registrar
              </button>
            </div>

            <div v-if="item.error" class="alert alert-error">{{ item.error }}</div>
          </div>
        </div>
      </div>

      <!-- Generated Tickets -->
      <div v-if="boletos.length" class="tickets-section">
        <h2 class="section-title">
          <Ticket :size="24" />
          Boletos generados
        </h2>
        <div class="tickets-grid">
          <div v-for="boleto in boletos" :key="boleto.id" class="ticket-card">
            <div class="ticket-code">{{ boleto.codigo_boleto }}</div>
            <p class="ticket-passenger">
              {{ boleto.pasajero?.nombres }} {{ boleto.pasajero?.apellijos }} | Asiento {{ boleto.asiento?.numero }}
            </p>
            <span class="badge" :class="boleto.estado === 'pendiente_verificacion' ? 'badge-amber' : 'badge-green'">
              {{ boleto.estado === 'pendiente_verificacion' ? 'Pendiente verificacion' : 'Comprado' }}
            </span>
          </div>
        </div>
      </div>
    </section>

    <!-- Payment Modal -->
    <Teleport to="body">
      <div v-if="mostrarPago" class="modal-overlay" @click.self="cerrarPago">
        <div class="modal">
          <div class="modal-header">
            <h3>Confirmacion de pago</h3>
            <button class="modal-close" @click="cerrarPago">
              <X :size="20" />
            </button>
          </div>
          <div class="modal-body">
            <PaymentSimulator :amount="total" :method="metodoPago" @paid-change="finalizarPagoSimulado" />
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { CalendarDays, MapPin, Clock, Users, Bus, Receipt, Ticket, Search, Fingerprint, X } from 'lucide-vue-next';
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

onMounted(async () => {
  await recargarTodo();
  refrescarInterval = window.setInterval(recargarHorarios, 8000);
});

onUnmounted(() => {
  if (refrescarInterval) clearInterval(refrescarInterval);
});

async function recargarHorarios() {
  if (!rutaSeleccionadaId.value) return;
  try {
    const response = await publicViajes({ fecha: fecha.value, ruta_id: rutaSeleccionadaId.value, per_page: 50 });
    let data = Array.isArray(response) ? response : (response?.data || []);
    if (!Array.isArray(data)) data = response?.data?.data || [];
    viajes.value = data.filter((v) => v.estado !== 'cancelado');
    const viajeExiste = viajes.value.some((v) => Number(v.id) === Number(viajeSeleccionadoId.value));
    if (!viajeExiste && viajes.value.length) {
      await seleccionarViaje(viajes.value[0]);
    } else if (!viajeExiste) {
      viajeSeleccionadoId.value = '';
      viajeSeleccionado.value = null;
      asientos.value = [];
    }
  } catch (e) { /* silent */ }
}

async function recargarTodo() {
  cargando.value = true;
  cargandoViajes.value = true;
  try {
    const [rutasRes, viajesRes] = await Promise.all([
      publicRutas(),
      publicViajes({ fecha: fecha.value, per_page: 500 })
    ]);

    let listaRutas = Array.isArray(rutasRes) ? rutasRes : (rutasRes?.data?.data || rutasRes?.data || []);
    let viajesData = Array.isArray(viajesRes) ? viajesRes : (viajesRes?.data?.data || viajesRes?.data || []);

    rutas.value = listaRutas.filter((r) => String(r.origen || '').toLowerCase() === 'cochabamba').sort((a, b) => a.destino.localeCompare(b.destino, 'es'));
    viajes.value = viajesData.filter((v) => v.estado !== 'cancelado');

    if (rutas.value.length && !rutaSeleccionadaId.value) {
      await seleccionarRuta(rutas.value[0]);
    } else if (rutas.value.length && rutaSeleccionadaId.value) {
      const rutaExistente = rutas.value.find((r) => Number(r.id) === Number(rutaSeleccionadaId.value));
      if (rutaExistente) {
        await cargarViajesPorRuta(rutaExistente);
      } else {
        await seleccionarRuta(rutas.value[0]);
      }
    }
  } catch (e) {
    console.error('Error:', e);
  } finally {
    cargando.value = false;
    cargandoViajes.value = false;
  }
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
    if (viajes.value.length) {
      await seleccionarViaje(viajes.value[0]);
    } else {
      viajeSeleccionadoId.value = '';
      viajeSeleccionado.value = null;
      asientos.value = [];
    }
  } catch (e) {
    console.error('Error:', e);
  } finally {
    cargandoViajes.value = false;
  }
}

async function seleccionarViaje(viaje) {
  viajeSeleccionadoId.value = viaje.id;
  viajeSeleccionado.value = viaje;
  asientos.value = [];
  try {
    const res = await publicAsientos(viaje.id);
    asientos.value = Array.isArray(res) ? res : (res?.data || res?.data?.data || []);
    pasajeros.value.forEach((item) => { item.asiento = null; });
  } catch (e) {
    console.error('Error:', e);
    asientos.value = [];
  }
}

function ajustarCantidad() {
  const actuales = pasajeros.value;
  pasajeros.value = Array.from({ length: cantidad.value }, (_, index) => actuales[index] || nuevoPasajero(index + 1));
  if (pasajeroActivoIndex.value >= pasajeros.value.length) pasajeroActivoIndex.value = 0;
  autoAsignarAdultos();
}

async function buscarPasajero(index) {
  const item = pasajeros.value[index];
  item.error = '';
  item.pasajero = null;
  item.modoRegistro = false;
  if (!item.ci) return;
  try {
    const response = await publicBuscarPasajero(item.ci);
    item.pasajero = response.data;
    item.form = pasajeroForm(response.data);
    autoAsignarAdultos();
  } catch {
    item.modoRegistro = true;
    item.form.numero_ci = item.ci;
    item.error = 'No esta registrado. Puede pre-registrarse.';
  }
}

async function preRegistrar(index) {
  const item = pasajeros.value[index];
  item.error = '';
  try {
    const response = await publicPreRegistrarPasajero(item.form);
    item.pasajero = response.data;
    item.modoRegistro = false;
    autoAsignarAdultos();
  } catch (err) {
    item.error = err.message || 'No se pudo pre-registrar.';
  }
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
  error.value = '';
  mensaje.value = '';
  boletos.value = [];
  if (!viajeSeleccionado.value) { error.value = 'Selecciona destino y horario.'; return; }
  if (pasajerosListos.value !== cantidad.value) { error.value = 'Completa todos los pasajeros y asientos.'; return; }
  if (metodoPago.value !== 'efectivo' && !pagoYaValidado) { mostrarPago.value = true; return; }
  reservando.value = true;
  try {
    const response = await publicReservarBoletos({
      viaje_id: viajeSeleccionado.value.id,
      metodo_pago: metodoPago.value,
      pasajes: pasajeros.value.map((item) => ({
        pasajero_id: item.pasajero.id,
        asiento_id: item.asiento.id,
        adulto_resp_id: item.adulto_resp_id || null
      }))
    });
    boletos.value = response.data.boletos || [];
    mensaje.value = response.data.requiere_verificacion_huella ? 'Reserva generada. Hay pasajeros con huella pendiente.' : 'Compra generada correctamente.';
    const asientosResponse = await publicAsientos(viajeSeleccionado.value.id);
    asientos.value = asientosResponse.data || [];
  } catch (err) {
    error.value = err.message || 'No se pudo generar la reserva.';
  } finally {
    reservando.value = false;
  }
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
  pasajeros.value.forEach((item) => {
    if (!esMenor(item.pasajero)) return;
    const adultoExiste = adultos.some((adulto) => Number(adulto.id) === Number(item.adulto_resp_id));
    if (!adultoExiste) item.adulto_resp_id = adultos.length === 1 ? adultos[0].id : '';
  });
}

function esMenor(pasajero) { return pasajero ? edad(pasajero.fecha_nacimiento) < 18 : false; }
function edad(fechaNacimiento) {
  if (!fechaNacimiento) return 0;
  const today = new Date();
  const birth = new Date(fechaNacimiento);
  let age = today.getFullYear() - birth.getFullYear();
  const monthDiff = today.getMonth() - birth.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) age--;
  return age;
}
function nombreCompleto(pasajero) { return pasajero ? `${pasajero.nombres || ''} ${pasajero.apellidos || ''}`.trim() : ''; }
function hora(value) { return value ? new Date(value).toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit', hour12: true }) : '--:--'; }

const pasajerosListos = computed(() => pasajeros.value.filter((p) => p.pasajero?.id && p.asiento?.id).length);
const asientosSeleccionados = computed(() => pasajeros.value.filter((p) => p.asiento?.id).map((p) => p.asiento.id));
const asientosLabels = computed(() => pasajeros.value.filter((p) => p.asiento?.id).reduce((acc, p) => { acc[p.asiento.id] = p.pasajero?.nombres; return acc; }, {}));
const asientosKinds = computed(() => {
  const kinds = {};
  pasajeros.value.forEach((p) => {
    if (!p.asiento?.id || !p.pasajero) return;
    kinds[p.asiento.id] = esMenor(p.pasajero) ? 'menor' : (edad(p.pasajero?.fecha_nacimiento) >= 60 ? 'adulto_mayor' : 'adulto');
  });
  return kinds;
});
const asientosNombres = computed(() => pasajeros.value.map((p) => p.asiento?.numero).filter(Boolean).join(', ') || '-');
const total = computed(() => {
  if (!viajeSeleccionado.value) return 0;
  return pasajeros.value.filter((p) => p.asiento?.id).length * Number(viajeSeleccionado.value.precio_final || 0);
});
</script>

<style scoped>
.hero {
  @apply bg-gradient-to-r from-blue-600 to-blue-700 text-white py-16 px-4;
}

.hero-content {
  @apply max-w-7xl mx-auto text-center;
}

.hero-title {
  @apply text-4xl font-bold mb-4;
}

.hero-subtitle {
  @apply text-blue-100 text-lg;
}

.boleteria-content {
  @apply max-w-7xl mx-auto px-4 py-8;
}

.boleteria-grid {
  @apply grid gap-6 lg:grid-cols-[400px_1fr];
}

.selection-column {
  @apply space-y-4;
}

.results-column {
  @apply space-y-4;
}

.card {
  @apply bg-white rounded-xl border border-slate-200 overflow-hidden;
}

.card-header {
  @apply flex items-center gap-3 px-5 py-4 border-b border-slate-100;
}

.card-header h3 {
  @apply font-semibold text-slate-800;
}

.card-body {
  @apply p-5;
}

.loading, .empty-message {
  @apply text-center py-8 text-slate-400;
}

.routes-list, .schedules-list {
  @apply space-y-2;
}

.route-item, .schedule-item {
  @apply w-full flex items-center justify-between p-4 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition-all text-left;
}

.route-item.is-selected, .schedule-item.is-selected {
  @apply border-blue-500 bg-blue-50;
}

.route-destination {
  @apply font-semibold text-slate-800;
}

.route-code {
  @apply text-xs text-slate-400;
}

.route-price {
  @apply text-right;
}

.price {
  @apply block font-bold text-blue-600;
}

.duration {
  @apply text-xs text-slate-400;
}

.schedule-time {
  @apply font-bold text-lg text-slate-800;
}

.schedule-info {
  @apply flex-1 px-4;
}

.schedule-bus {
  @apply block text-sm text-slate-600;
}

.schedule-code {
  @apply text-xs text-slate-400;
}

.schedule-seats {
  @apply text-right;
}

.seats-available {
  @apply block font-bold text-green-600;
}

.seats-label {
  @apply text-xs text-slate-400;
}

.form-input {
  @apply w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.form-label {
  @apply block text-sm font-medium text-slate-600 mb-2;
}

.btn-primary {
  @apply flex items-center justify-center gap-2 py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors;
}

.btn-secondary {
  @apply flex items-center justify-center gap-2 py-3 px-6 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors;
}

.btn-block {
  @apply w-full;
}

.summary-list {
  @apply space-y-3;
}

.summary-item {
  @apply flex justify-between text-sm;
}

.summary-item dt {
  @apply text-slate-500;
}

.summary-item dd {
  @apply font-medium text-slate-800;
}

.summary-item.total {
  @apply pt-3 border-t border-slate-100;
}

.summary-item.total dt {
  @apply font-semibold text-slate-800;
}

.summary-item.total dd {
  @apply text-xl font-bold text-blue-600;
}

.payment-section {
  @apply mt-4 pt-4 border-t border-slate-100;
}

.alert {
  @apply mt-4 p-3 rounded-xl text-sm font-medium;
}

.alert-error {
  @apply bg-red-50 text-red-700;
}

.alert-success {
  @apply bg-green-50 text-green-700;
}

.badge {
  @apply inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full;
}

.badge-teal {
  @apply bg-teal-100 text-teal-700;
}

.badge-green {
  @apply bg-green-100 text-green-700;
}

.badge-amber {
  @apply bg-amber-100 text-amber-700;
}

.spinner {
  @apply inline-block w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin;
}

.section-title {
  @apply flex items-center gap-3 text-xl font-bold text-slate-800 mb-6 mt-12;
}

.passengers-grid {
  @apply grid gap-4 md:grid-cols-2 lg:grid-cols-3;
}

.passenger-card {
  @apply bg-white rounded-xl border border-slate-200 p-5;
}

.passenger-card.is-active {
  @apply border-blue-400 shadow-lg;
}

.passenger-header {
  @apply flex items-center justify-between mb-4;
}

.passenger-name {
  @apply font-bold text-slate-800 text-left;
}

.passenger-ci {
  @apply block text-xs text-slate-400 font-normal;
}

.passenger-search {
  @apply flex gap-2;
}

.passenger-info {
  @apply mt-4 p-3 bg-slate-50 rounded-lg;
}

.passenger-fullname {
  @apply font-semibold text-slate-800;
}

.passenger-details {
  @apply text-sm text-slate-500;
}

.passenger-huella {
  @apply flex items-center gap-2 text-sm font-medium mt-2;
}

.passenger-register {
  @apply mt-4 space-y-2;
}

.tickets-grid {
  @apply grid gap-4 md:grid-cols-2;
}

.ticket-card {
  @apply bg-white rounded-xl border border-slate-200 p-4;
}

.ticket-code {
  @apply font-bold text-lg text-blue-600;
}

.ticket-passenger {
  @apply text-sm text-slate-600 mt-1;
}

.modal-overlay {
  @apply fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4;
}

.modal {
  @apply w-full max-w-md bg-white rounded-2xl shadow-xl;
}

.modal-header {
  @apply flex items-center justify-between px-6 py-4 border-b border-slate-200;
}

.modal-header h3 {
  @apply font-semibold text-lg text-slate-800;
}

.modal-close {
  @apply p-2 rounded-lg hover:bg-slate-100 text-slate-400;
}

.modal-body {
  @apply p-6;
}

@media (max-width: 1024px) {
  .boleteria-grid {
    @apply grid-cols-1;
  }
}
</style>
