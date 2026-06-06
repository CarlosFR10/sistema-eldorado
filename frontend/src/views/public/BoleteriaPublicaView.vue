<template>
  <section class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white">
      <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3">
        <RouterLink class="text-lg font-black text-eldorado-ink" to="/">Terminal El Dorado</RouterLink>
        <nav class="flex flex-wrap items-center gap-2 text-sm font-bold">
          <RouterLink class="rounded-md px-3 py-2 text-slate-700 hover:bg-teal-50" to="/">Inicio</RouterLink>
          <RouterLink class="rounded-md px-3 py-2 text-slate-700 hover:bg-teal-50" to="/registro">Registrarse</RouterLink>
          <RouterLink class="rounded-md px-3 py-2 text-slate-700 hover:bg-teal-50" to="/rastrear">Rastrear bus</RouterLink>
          <RouterLink class="rounded-md px-3 py-2 text-slate-700 hover:bg-teal-50" to="/consulta">Consulta autoridad</RouterLink>
          <RouterLink class="btn btn-secondary" to="/login">
            <LogIn :size="18" />
            Iniciar sesion
          </RouterLink>
        </nav>
      </div>
    </header>

    <main class="mx-auto grid max-w-7xl gap-4 px-4 py-5 lg:grid-cols-[390px_1fr]">
      <aside class="space-y-4">
        <section class="panel rounded-lg p-4">
          <p class="text-sm font-black uppercase text-eldorado-teal">Boleteria publica</p>
          <h1 class="text-2xl font-black text-slate-900">Comprar pasajes</h1>
          <label class="mt-4 block space-y-1">
            <span class="text-sm font-bold text-slate-600">Fecha de viaje</span>
            <input v-model="fecha" class="field" type="date" />
          </label>
        </section>

        <section class="panel rounded-lg p-4">
          <h2 class="mb-3 flex items-center gap-2 font-black text-slate-800">
            <MapPin :size="18" />
            Destinos desde Cochabamba
          </h2>
          <div v-if="cargando" class="text-sm text-slate-500">Cargando destinos...</div>
          <div v-else-if="rutas.length === 0" class="text-sm text-slate-500">No hay viajes para esta fecha.</div>
          <div v-else class="grid gap-2">
            <button
              v-for="ruta in rutas"
              :key="ruta.id"
              class="rounded-lg border p-3 text-left transition"
              :class="Number(rutaSeleccionadaId) === Number(ruta.id) ? 'border-teal-600 bg-teal-50' : 'border-slate-200 bg-white hover:bg-slate-50'"
              type="button"
              @click="seleccionarRuta(ruta)"
            >
              <div class="flex items-start justify-between gap-2">
                <div>
                  <p class="font-black text-slate-900">{{ ruta.destino }}</p>
                  <p class="text-xs font-bold text-slate-500">{{ ruta.codigo }}</p>
                </div>
                <span class="font-black text-teal-800">Bs {{ ruta.precio_base }}</span>
              </div>
            </button>
          </div>
        </section>

        <section class="panel rounded-lg p-4">
          <h2 class="mb-3 flex items-center gap-2 font-black text-slate-800">
            <CalendarDays :size="18" />
            Horarios disponibles
          </h2>
          <div v-if="cargandoViajes" class="text-sm text-slate-500">Cargando horarios...</div>
          <div v-else-if="viajes.length" class="grid gap-2">
            <button
              v-for="viaje in viajes"
              :key="viaje.id"
              class="rounded-lg border p-3 text-left transition"
              :class="Number(viajeSeleccionadoId) === Number(viaje.id) ? 'border-teal-600 bg-teal-50' : 'border-slate-200 bg-white hover:bg-slate-50'"
              type="button"
              @click="seleccionarViaje(viaje)"
            >
              <div class="flex items-center justify-between gap-2">
                <p class="font-black text-slate-900">Disponible {{ hora(viaje.fecha_salida) }}</p>
                <span class="chip bg-emerald-100 text-emerald-800">{{ viaje.asientos_disponibles_count ?? '-' }} libres</span>
              </div>
              <p class="mt-1 font-black text-teal-700">{{ viaje.codigo_viaje }}</p>
              <p class="text-xs font-bold text-slate-500">Bus {{ viaje.bus?.placa }} | {{ hora(viaje.fecha_salida) }}</p>
            </button>
          </div>
          <p v-else class="rounded-md bg-slate-50 p-3 text-sm font-bold text-slate-600">Selecciona destino o cambia la fecha.</p>
        </section>

        <section class="panel rounded-lg p-4">
          <h2 class="mb-3 flex items-center gap-2 font-black text-slate-800">
            <Users :size="18" />
            Pasajes
          </h2>
          <select v-model.number="cantidad" class="field" @change="ajustarCantidad">
            <option v-for="numero in 10" :key="numero" :value="numero">{{ numero }} pasajero{{ numero > 1 ? 's' : '' }}</option>
          </select>
        </section>
      </aside>

      <div class="space-y-4">
        <section class="grid gap-4 xl:grid-cols-[1fr_340px]">
          <div>
            <CroquisBus
              v-if="viajeSeleccionado && asientos.length"
              :asientos="asientos"
              :selected-id="pasajeros[pasajeroActivoIndex]?.asiento?.id"
              :selected-ids="asientosSeleccionados"
              :selected-labels="asientosLabels"
              :selected-kinds="asientosKinds"
              @select="seleccionarAsiento"
            />
            <div v-else-if="viajeSeleccionado" class="panel rounded-lg p-5">
              <h2 class="font-black text-slate-800">Cargando croquis...</h2>
              <p class="mt-2 text-sm text-slate-500">Asientos: {{ asientos.length }}</p>
            </div>
            <div v-else class="panel rounded-lg p-5">
              <h2 class="font-black text-slate-800">Croquis del bus</h2>
              <p class="mt-2 rounded-md bg-slate-50 p-3 text-sm font-bold text-slate-600">
                Selecciona destino y horario para ver el croquis de asientos disponible.
              </p>
            </div>
          </div>

          <aside class="panel rounded-lg p-4">
            <h2 class="mb-3 font-black text-slate-800">Resumen</h2>
            <dl class="space-y-3 text-sm">
              <div>
                <dt class="font-bold text-slate-500">Viaje</dt>
                <dd class="font-black text-slate-900">{{ viajeSeleccionado?.ruta?.destino || '-' }} {{ viajeSeleccionado ? hora(viajeSeleccionado.fecha_salida) : '' }}</dd>
              </div>
              <div>
                <dt class="font-bold text-slate-500">Pasajero activo</dt>
                <dd>Pasajero {{ pasajeroActivoIndex + 1 }}{{ pasajeros[pasajeroActivoIndex]?.pasajero ? ` - ${nombreCompleto(pasajeros[pasajeroActivoIndex].pasajero)}` : '' }}</dd>
              </div>
              <div>
                <dt class="font-bold text-slate-500">Pasajeros</dt>
                <dd>{{ pasajerosListos }} de {{ cantidad }} listos</dd>
              </div>
              <div>
                <dt class="font-bold text-slate-500">Asientos</dt>
                <dd>{{ pasajeros.map((item) => item.asiento?.numero).filter(Boolean).join(', ') || '-' }}</dd>
              </div>
              <div>
                <dt class="font-bold text-slate-500">Total</dt>
                <dd class="text-xl font-black text-teal-800">Bs {{ total }}</dd>
              </div>
            </dl>

            <select v-model="metodoPago" class="field mt-4">
              <option value="efectivo">Efectivo</option>
              <option value="qr_bancario">QR bancario</option>
              <option value="tarjeta">Tarjeta</option>
            </select>

            <button class="btn bg-blue-600 hover:bg-blue-700 text-white mt-3 w-full" :disabled="reservando" @click="reservar">
              <Ticket :size="18" />
              COMPRAR / RESERVAR AHORA AZUL
            </button>

            <p v-if="error" class="mt-3 rounded-md bg-red-50 p-3 text-sm font-bold text-red-800">{{ error }}</p>
            <p v-if="mensaje" class="mt-3 rounded-md bg-emerald-50 p-3 text-sm font-bold text-emerald-800">{{ mensaje }}</p>
          </aside>
        </section>

        <section class="panel rounded-lg p-4">
          <h2 class="mb-3 font-black text-slate-800">Datos de pasajeros</h2>
          <div class="grid gap-3 xl:grid-cols-2">
            <article
              v-for="(item, index) in pasajeros"
              :key="item.uid"
              class="rounded-lg border p-3"
              :class="index === pasajeroActivoIndex ? 'border-teal-500 bg-teal-50' : 'border-slate-200 bg-white'"
            >
              <div class="mb-3 flex items-center justify-between gap-2">
                <button class="font-black text-slate-900" type="button" @click="pasajeroActivoIndex = index">
                  Pasajero {{ index + 1 }}
                </button>
                <span v-if="item.asiento" class="chip bg-teal-100 text-teal-900">Asiento {{ item.asiento.numero }}</span>
              </div>

              <div class="flex gap-2">
                <input v-model="item.ci" class="field" placeholder="Carnet de identidad" />
                <button class="btn btn-secondary" type="button" @click="buscarPasajero(index)">
                  <Search :size="18" />
                </button>
              </div>

              <div v-if="item.pasajero" class="mt-3 rounded-md bg-slate-50 p-3 text-sm">
                <p class="font-black text-slate-900">{{ nombreCompleto(item.pasajero) }}</p>
                <p class="text-slate-600">CI {{ item.pasajero.numero_ci }} | {{ edad(item.pasajero.fecha_nacimiento) }} anos</p>
                <p class="mt-2 flex items-center gap-2 font-bold" :class="item.pasajero.tiene_huella ? 'text-emerald-800' : 'text-amber-800'">
                  <Fingerprint :size="16" />
                  {{ textoHuella(item.pasajero) }}
                </p>
              </div>

              <div v-if="item.pasajero && !esMenor(item.pasajero)" class="mt-3 rounded-md border border-cyan-200 bg-cyan-50 p-3 text-sm">
                <p class="flex items-center gap-2 font-black text-cyan-950">
                  <Link2 :size="16" />
                  Adulto responsable disponible
                </p>
                <p v-if="menoresVinculados(item).length" class="mt-1 font-bold text-cyan-900">
                  {{ nombreCompleto(item.pasajero) }} viajara con {{ menoresVinculados(item).join(', ') }}.
                </p>
                <p v-else class="mt-1 text-cyan-800">Puede enlazarse con un menor de esta misma compra.</p>
              </div>

              <div v-if="item.modoRegistro" class="mt-3 grid gap-2">
                <input v-model="item.form.nombres" class="field" placeholder="Nombres" />
                <input v-model="item.form.apellidos" class="field" placeholder="Apellidos" />
                <input v-model="item.form.expedido_en" class="field" maxlength="2" placeholder="Expedido en" />
                <input v-model="item.form.fecha_nacimiento" class="field" type="date" />
                <input v-model="item.form.telefono" class="field" placeholder="Telefono" />
                <button class="btn btn-secondary" type="button" @click="preRegistrar(index)">Pre-registrar pasajero</button>
              </div>

              <div v-if="esMenor(item.pasajero)" class="mt-3 rounded-md border border-yellow-300 bg-yellow-50 p-3 text-sm">
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
                  Agrega al adulto como otro pasajero para habilitar este pasaje.
                </p>
              </div>

              <p v-if="item.error" class="mt-3 rounded-md bg-red-50 p-2 text-sm font-bold text-red-800">{{ item.error }}</p>
            </article>
          </div>
        </section>

        <section v-if="boletos.length" class="panel rounded-lg p-4">
          <h2 class="mb-3 font-black text-slate-800">Boletos generados</h2>
          <div class="grid gap-3 md:grid-cols-2">
            <article v-for="boleto in boletos" :key="boleto.id" class="rounded-lg border border-slate-200 bg-white p-3">
              <p class="font-black text-slate-900">{{ boleto.codigo_boleto }}</p>
              <p class="text-sm text-slate-600">{{ boleto.pasajero?.nombres }} {{ boleto.pasajero?.apellidos }} | Asiento {{ boleto.asiento?.numero }}</p>
              <span class="chip mt-2" :class="boleto.estado === 'pendiente_verificacion' ? 'bg-amber-100 text-amber-900' : 'bg-emerald-100 text-emerald-800'">
                {{ boleto.estado === 'pendiente_verificacion' ? 'Huella pendiente a verificacion' : 'Comprado' }}
              </span>
              <RouterLink class="btn btn-secondary mt-3 w-full justify-center" :to="{ name: 'rastrear-bus', query: { codigo: boleto.codigo_boleto } }">
                Rastrear este bus
              </RouterLink>
            </article>
          </div>
        </section>
      </div>
    </main>

    <div v-if="mostrarPago" class="fixed inset-0 z-50 grid place-items-center bg-slate-950/50 p-4">
      <div class="w-full max-w-md rounded-xl bg-white p-4 shadow-2xl">
        <div class="mb-3 flex items-center justify-between gap-3">
          <div>
            <p class="text-xs font-black uppercase text-eldorado-teal">Confirmacion de pago</p>
            <h2 class="text-xl font-black text-slate-900">Finalizar compra</h2>
          </div>
          <button class="btn btn-secondary px-3" type="button" :disabled="reservando" @click="cerrarPago">Cerrar</button>
        </div>
        <PaymentSimulator :amount="total" :method="metodoPago" @paid-change="finalizarPagoSimulado" />
      </div>
    </div>
  </section>
</template>

<script setup>
import { CalendarDays, Fingerprint, Link2, LogIn, MapPin, Search, Ticket, Users } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import CroquisBus from '../../components/CroquisBus.vue';
import PaymentSimulator from '../../components/PaymentSimulator.vue';
import {
  publicAsientos,
  publicBuscarPasajero,
  publicPreRegistrarPasajero,
  publicReservarBoletos,
  publicRutas,
  publicViajes
} from '../../api/public';

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
const pagoConfirmado = ref(true);
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
    const response = await publicViajes({
      fecha: fecha.value,
      ruta_id: rutaSeleccionadaId.value,
      per_page: 50
    });
    
    let data = [];
    if (Array.isArray(response)) {
      data = response;
    } else if (response?.data) {
      data = Array.isArray(response.data) ? response.data : (response.data?.data || []);
    }
    
    if (!Array.isArray(data)) data = [];
    
    const viajesFiltrados = data.filter((v) => v.estado !== 'cancelado');
    viajes.value = viajesFiltrados;
    
    const viajeExiste = viajes.value.some((v) => Number(v.id) === Number(viajeSeleccionadoId.value));
    if (!viajeExiste && viajes.value.length) {
      await seleccionarViaje(viajes.value[0]);
    } else if (!viajeExiste) {
      viajeSeleccionadoId.value = '';
      viajeSeleccionado.value = null;
      asientos.value = [];
    }
  } catch (e) {
    // Silencioso - no molestar al usuario
  }
}

async function recargarTodo() {
  cargando.value = true;
  cargandoViajes.value = true;
  try {
    const [rutasRes, viajesRes] = await Promise.all([
      publicRutas(),
      publicViajes({ fecha: fecha.value, per_page: 500 })
    ]);

    let listaRutas = [];
    if (Array.isArray(rutasRes)) {
      listaRutas = rutasRes;
    } else if (rutasRes?.data) {
      if (Array.isArray(rutasRes.data)) {
        listaRutas = rutasRes.data;
      } else if (rutasRes.data?.data) {
        listaRutas = rutasRes.data.data;
      }
    }

    let viajesData = [];
    if (Array.isArray(viajesRes)) {
      viajesData = viajesRes;
    } else if (viajesRes?.data) {
      if (Array.isArray(viajesRes.data)) {
        viajesData = viajesRes.data;
      } else if (viajesRes.data?.data) {
        viajesData = viajesRes.data.data;
      }
    }

    console.log('DEBUG - viajesRes type:', typeof viajesRes);
    console.log('DEBUG - viajesRes keys:', viajesRes ? Object.keys(viajesRes) : 'null');
    console.log('DEBUG - viajesRes.data type:', typeof viajesRes?.data);
    console.log('DEBUG - viajesData length:', Array.isArray(viajesData) ? viajesData.length : 'no es array');
    
if (!Array.isArray(viajesData)) {
      viajesData = [];
    }
    
    const rutasFiltradas = listaRutas.filter(
      (ruta) => String(ruta.origen || '').toLowerCase() === 'cochabamba'
    );

    const viajesFiltrados = viajesData.filter(
      (v) => v.estado !== 'cancelado'
    );

    const rutasConViajes = new Set(viajesFiltrados.map((v) => v.ruta_id));

    rutas.value = rutasFiltradas
      .filter((ruta) => rutasConViajes.has(ruta.id))
      .sort((a, b) => String(a.destino || '').localeCompare(String(b.destino || ''), 'es'));

    if (rutas.value.length && rutaSeleccionadaId.value) {
      const rutaExistente = rutas.value.find((r) => Number(r.id) === Number(rutaSeleccionadaId.value));
      if (rutaExistente) {
        await cargarViajesPorRuta(rutaExistente);
      } else {
        rutaSeleccionadaId.value = '';
        viajeSeleccionado.value = null;
        viajes.value = [];
        asientos.value = [];
        if (rutas.value.length) {
          await seleccionarRuta(rutas.value[0]);
        }
      }
    } else if (rutas.value.length) {
      await seleccionarRuta(rutas.value[0]);
    } else {
      rutaSeleccionadaId.value = '';
      viajeSeleccionado.value = null;
      viajes.value = [];
      asientos.value = [];
    }
  } catch (e) {
    console.error('Error recargando:', e);
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
    const response = await publicViajes({
      fecha: fecha.value,
      ruta_id: ruta.id,
      per_page: 50
    });
    
    let data = [];
    if (Array.isArray(response)) {
      data = response;
    } else if (response?.data) {
      data = Array.isArray(response.data) ? response.data : (response.data?.data || []);
    }
    
    if (!Array.isArray(data)) {
      console.error('data no es array:', data);
      data = [];
    }
    
    viajes.value = data.filter((v) => v.estado !== 'cancelado');
    console.log('cargarViajesPorRuta - viajes:', viajes.value.length);

    if (viajes.value.length) {
      await seleccionarViaje(viajes.value[0]);
    } else {
      viajeSeleccionadoId.value = '';
      viajeSeleccionado.value = null;
      asientos.value = [];
    }
  } catch (e) {
    console.error('Error cargando viajes:', e);
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
    
    if (Array.isArray(res)) {
      asientos.value = res;
    } else if (res?.data) {
      asientos.value = Array.isArray(res.data) ? res.data : (res.data?.data || []);
    } else {
      asientos.value = [];
    }
    
    pasajeros.value.forEach((item) => { item.asiento = null; });
  } catch (e) {
    console.error('Error cargando asientos:', e);
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
    item.error = 'No esta registrado. Puede pre-registrarse, pero la huella quedara pendiente en plataforma.';
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
  if (!viajeSeleccionado.value) { error.value = 'Selecciona un viaje antes de elegir asiento.'; return; }
  if (asiento.estado !== 'disponible') return;
  const ocupadoPorOtro = pasajeros.value.some((pasajero, index) => index !== pasajeroActivoIndex.value && Number(pasajero.asiento?.id) === Number(asiento.id));
  if (ocupadoPorOtro) { error.value = 'Ese asiento ya esta elegido por otro pasajero de esta compra.'; return; }
  item.asiento = asiento;
}

async function reservar(pagoYaValidado = false) {
  const pagoValidado = pagoYaValidado === true;
  error.value = '';
  mensaje.value = '';
  boletos.value = [];
  if (!viajeSeleccionado.value) { error.value = 'Selecciona destino, horario y asiento.'; return; }
  if (pasajerosListos.value !== cantidad.value) { error.value = 'Completa pasajeros, adultos responsables y asientos.'; return; }
  if (metodoPago.value !== 'efectivo' && !pagoValidado) { mostrarPago.value = true; return; }
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
    mensaje.value = response.data.requiere_verificacion_huella
      ? 'Reserva generada. Hay pasajeros con huella pendiente de verificacion en plataforma.'
      : 'Compra generada correctamente.';
    const asientosResponse = await publicAsientos(viajeSeleccionado.value.id);
    asientos.value = asientosResponse.data || [];
    pagoConfirmado.value = metodoPago.value === 'efectivo';
  } catch (err) {
    error.value = err.message || 'No se pudo generar la reserva.';
  } finally {
    reservando.value = false;
  }
}

function finalizarPagoSimulado(confirmado) {
  if (!confirmado || reservando.value) return;
  pagoConfirmado.value = true;
  mensaje.value = 'Transaccion realizada. Generando boletos...';
  window.setTimeout(() => { mostrarPago.value = false; reservar(true); }, 800);
}

function cerrarPago() { mostrarPago.value = false; }

function nuevoPasajero(index) {
  return {
    uid: `${Date.now()}-${index}-${Math.random().toString(16).slice(2, 8)}`,
    ci: '',
    pasajero: null,
    asiento: null,
    adulto_resp_id: '',
    modoRegistro: false,
    form: pasajeroForm(),
    error: ''
  };
}

function pasajeroForm(pasajero = {}) {
  return {
    nombres: pasajero.nombres || '',
    apellidos: pasajero.apellidos || '',
    numero_ci: pasajero.numero_ci || '',
    complemento_ci: pasajero.complemento_ci || '',
    expedido_en: pasajero.expedido_en || 'CB',
    fecha_nacimiento: pasajero.fecha_nacimiento || '',
    telefono: pasajero.telefono || '',
    email: pasajero.email || ''
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

function esMenor(pasajero) { return pasajero ? edad(pasajero.fecha_nacimiento) < 18 : false; }

function edad(fechaNacimiento) {
  if (!fechaNacimiento) return 0;
  const today = new Date();
  const birth = new Date(fechaNacimiento);
  let age = today.getFullYear() - birth.getFullYear();
  const monthDiff = today.getMonth() - birth.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) age -= 1;
  return age;
}

function nombreCompleto(pasajero) { return pasajero ? `${pasajero.nombres || ''} ${pasajero.apellidos || ''}`.trim() : ''; }

function textoHuella(pasajero) {
  if (!pasajero?.tiene_huella) return 'Huella dactilar pendiente a verificacion';
  const codigo = String(pasajero.numero_ci || '').slice(-6).padStart(6, '0');
  return `${codigo} huella digital de ${pasajero.nombres}`;
}

function hora(value) { return value ? new Date(value).toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit', hour12: true }) : '--:--'; }

const pasajerosListos = computed(() => pasajeros.value.filter((p) => p.pasajero?.id && p.asiento?.id).length);
const asientosSeleccionados = computed(() => pasajeros.value.filter((p) => p.asiento?.id).map((p) => p.asiento.id));
const asientosLabels = computed(() => pasajeros.value.filter((p) => p.asiento?.id).reduce((acc, p) => { acc[p.asiento.id] = p.pasajero?.nombres; return acc; }, {}));
const asientosKinds = computed(() => {
  const kinds = {};
  pasajeros.value.forEach((p) => {
    if (!p.asiento?.id || !p.pasajero) return;
    kinds[p.asiento.id] = kindPasajero(p);
  });
  return kinds;
});
const total = computed(() => {
  if (!viajeSeleccionado.value) return 0;
  return pasajeros.value.filter((p) => p.asiento?.id).length * Number(viajeSeleccionado.value.precio_final || 0);
});
function kindPasajero(item) {
  if (esMenor(item.pasajero)) return 'menor';
  if (edad(item.pasajero?.fecha_nacimiento) >= 60) return 'adulto_mayor';
  if (menoresVinculados(item).length) return 'con_menor';
  return 'adulto';
}
</script>