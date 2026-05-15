<template>
  <main class="app-shell min-h-screen px-4 py-8">
    <section class="mx-auto max-w-7xl space-y-5">
      <header class="panel overflow-hidden rounded-lg">
        <div class="grid gap-4 p-5 lg:grid-cols-[1fr_auto] lg:items-center">
          <div>
            <p class="text-sm font-black uppercase text-eldorado-teal">Consulta publica y autoridad</p>
            <h1 class="text-3xl font-black text-slate-950">Viajes, manifiestos y boletos</h1>
            <p class="mt-2 max-w-3xl font-semibold text-slate-600">
              Autoridades pueden consultar manifiestos por codigo de viaje. Los pasajeros pueden escribir su carnet para ver sus boletos, QR y rastrear su bus.
            </p>
          </div>
          <nav class="flex flex-wrap gap-2">
            <RouterLink class="btn btn-secondary" to="/">Inicio</RouterLink>
            <RouterLink class="btn btn-secondary" to="/registro">Registrarse</RouterLink>
            <RouterLink class="btn btn-secondary" to="/boleteria">Comprar boletos</RouterLink>
            <RouterLink class="btn btn-secondary" to="/rastrear">Rastrear bus</RouterLink>
          </nav>
        </div>
        <div class="h-2 bg-gradient-to-r from-teal-600 via-cyan-500 to-amber-400"></div>
      </header>

      <section class="grid gap-4 lg:grid-cols-2">
        <article class="panel rounded-lg p-5">
          <div class="flex items-start gap-3">
            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-lg bg-teal-100 text-teal-800">
              <ShieldCheck :size="22" />
            </div>
            <div>
              <p class="text-xs font-black uppercase text-teal-700">Autoridades</p>
              <h2 class="text-xl font-black text-slate-900">Manifiesto por codigo de viaje</h2>
              <p class="mt-1 text-sm font-semibold text-slate-600">Escribe el codigo de viaje VJ-AAAAMMDD-NNN que aparece en cada boleto.</p>
            </div>
          </div>

          <div class="mt-4 flex flex-wrap gap-2">
            <input v-model.trim="codigo" class="field min-w-64 flex-1" placeholder="Ejemplo: VJ-20260512-001" @keyup.enter="consultar" />
            <button class="btn btn-primary" :disabled="consultando" @click="consultar">
              <LoaderCircle v-if="consultando" class="animate-spin" :size="18" />
              <Search v-else :size="18" />
              {{ consultando ? 'Consultando...' : 'Consultar' }}
            </button>
          </div>
          <p v-if="error" class="mt-3 rounded-md bg-red-50 p-3 text-sm font-bold text-red-800">{{ error }}</p>
        </article>

        <article class="panel rounded-lg p-5">
          <div class="flex items-start gap-3">
            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-lg bg-cyan-100 text-cyan-800">
              <Ticket :size="22" />
            </div>
            <div>
              <p class="text-xs font-black uppercase text-cyan-700">Pasajeros</p>
              <h2 class="text-xl font-black text-slate-900">Buscar boletos por carnet</h2>
              <p class="mt-1 text-sm font-semibold text-slate-600">Escribe tu CI para ver los viajes que compraste, descargar el comprobante o imprimir el QR.</p>
            </div>
          </div>

          <div class="mt-4 flex flex-wrap gap-2">
            <input v-model.trim="ciCliente" class="field min-w-64 flex-1" placeholder="Carnet de identidad" @keyup.enter="consultarBoletos" />
            <button class="btn btn-primary" :disabled="consultandoCliente" @click="consultarBoletos">
              <LoaderCircle v-if="consultandoCliente" class="animate-spin" :size="18" />
              <UserRoundSearch v-else :size="18" />
              {{ consultandoCliente ? 'Buscando...' : 'Ver mis boletos' }}
            </button>
          </div>
          <p v-if="errorCliente" class="mt-3 rounded-md bg-red-50 p-3 text-sm font-bold text-red-800">{{ errorCliente }}</p>
        </article>
      </section>

      <section v-if="viaje" class="space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p class="text-xs font-black uppercase text-slate-500">Resultado autoridad</p>
            <h2 class="text-2xl font-black text-slate-900">Manifiesto y croquis del bus</h2>
          </div>
          <div class="flex flex-wrap gap-2">
            <RouterLink class="btn btn-secondary" :to="{ name: 'rastrear-bus', query: { codigo: codigoBus } }">
              <MapPinned :size="18" />
              Rastrear bus
            </RouterLink>
            <button class="btn btn-secondary" type="button" @click="imprimirManifiesto">
              <Printer :size="18" />
              Descargar constancia PDF
            </button>
          </div>
        </div>

        <section class="grid gap-4 lg:grid-cols-[1fr_280px]">
          <article class="panel rounded-lg p-4">
            <div class="grid gap-3 md:grid-cols-4">
              <div>
                <p class="text-sm font-bold text-slate-500">Codigo de bus</p>
                <p class="font-mono text-lg font-black text-teal-800">{{ codigoBus }}</p>
              </div>
              <div>
                <p class="text-sm font-bold text-slate-500">Bus</p>
                <p class="font-black text-slate-900">{{ viaje.bus?.placa }} | {{ viaje.bus?.marca }} {{ viaje.bus?.modelo }}</p>
              </div>
              <div>
                <p class="text-sm font-bold text-slate-500">Ruta</p>
                <p class="font-black text-slate-900">{{ viaje.ruta?.origen }} - {{ viaje.ruta?.destino }}</p>
              </div>
              <div>
                <p class="text-sm font-bold text-slate-500">Pasajeros</p>
                <p class="font-black text-slate-900">{{ pasajerosOrdenados.length }} registrados</p>
              </div>
            </div>
          </article>

          <article class="panel rounded-lg p-4 text-center">
            <p class="text-xs font-black uppercase text-eldorado-teal">QR del bus</p>
            <img v-if="viaje.bus_qr_imagen" class="mx-auto mt-2 h-40 w-40 rounded-lg border border-slate-200 bg-white p-2" :src="`data:image/png;base64,${viaje.bus_qr_imagen}`" alt="QR del bus" />
            <p class="mt-2 break-all text-xs font-bold text-slate-500">{{ viaje.bus_qr_url }}</p>
          </article>
        </section>

        <CroquisBus v-if="viaje.croquis_asientos?.length" :asientos="viaje.croquis_asientos" />

        <section class="panel rounded-lg p-4">
          <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h3 class="font-black text-slate-900">Pasajeros ordenados alfabeticamente</h3>
            <span class="chip bg-slate-100 text-slate-700">{{ pasajerosOrdenados.length }} en manifiesto</span>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Pasajero</th>
                  <th>CI</th>
                  <th>Asiento</th>
                  <th>Estado</th>
                  <th>Adulto resp.</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="pasajero in pasajerosOrdenados" :key="pasajero.boleto_id">
                  <td class="font-black text-slate-900">{{ pasajero.nombre_completo }}</td>
                  <td>{{ pasajero.ci }}</td>
                  <td>{{ pasajero.asiento || '-' }}</td>
                  <td><span class="chip" :class="estadoClass(pasajero.estado)">{{ estadoTexto(pasajero.estado) }}</span></td>
                  <td>{{ pasajero.adulto_responsable || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <section v-if="clienteBuscado" class="panel rounded-lg p-5">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
          <div>
            <p class="text-xs font-black uppercase text-eldorado-teal">Resultado pasajero</p>
            <h2 class="text-2xl font-black text-slate-900">{{ tituloCliente }}</h2>
            <p class="mt-1 font-semibold text-slate-600">
              {{ boletosCliente.length ? 'Estos son los boletos registrados para ese carnet.' : 'No hay boletos registrados para ese carnet.' }}
            </p>
          </div>
          <span class="chip bg-teal-100 text-teal-900">{{ boletosCliente.length }} boleto{{ boletosCliente.length === 1 ? '' : 's' }}</span>
        </div>

        <div v-if="boletosCliente.length" class="grid gap-4 lg:grid-cols-2">
          <article
            v-for="boleto in boletosCliente"
            :key="boleto.id"
            class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
          >
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 bg-slate-50 p-4">
              <div>
                <p class="text-xs font-black uppercase text-slate-500">Boleto digital</p>
                <h3 class="text-lg font-black text-slate-950">{{ boleto.codigo_boleto }}</h3>
                <p class="mt-1 text-sm font-semibold text-slate-600">
                  {{ boleto.viaje?.ruta?.origen }} - {{ boleto.viaje?.ruta?.destino }}
                </p>
              </div>
              <span class="chip" :class="estadoClass(boleto.estado)">{{ estadoTexto(boleto.estado) }}</span>
            </div>

            <div class="grid gap-4 p-4 sm:grid-cols-[150px_1fr]">
              <div class="rounded-xl border border-slate-200 bg-white p-2">
                <img v-if="boleto.qr_imagen" class="h-32 w-32 sm:h-36 sm:w-36" :src="`data:image/png;base64,${boleto.qr_imagen}`" alt="QR del boleto" />
                <div v-else class="grid h-32 w-32 place-items-center rounded-lg bg-slate-100 text-xs font-black text-slate-500 sm:h-36 sm:w-36">
                  QR
                </div>
              </div>

              <dl class="grid gap-2 text-sm">
                <div>
                  <dt class="font-bold text-slate-500">Pasajero</dt>
                  <dd class="font-black text-slate-900">{{ nombreCompleto(boleto.pasajero) }}</dd>
                </div>
                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <dt class="font-bold text-slate-500">Asiento</dt>
                    <dd class="font-black text-slate-900">{{ boleto.asiento?.numero || '-' }}</dd>
                  </div>
                  <div>
                    <dt class="font-bold text-slate-500">Bus</dt>
                    <dd class="font-black text-slate-900">{{ boleto.viaje?.bus?.placa || '-' }}</dd>
                  </div>
                </div>
                <div>
                  <dt class="font-bold text-slate-500">Salida</dt>
                  <dd class="font-black text-slate-900">{{ fechaHora(boleto.viaje?.fecha_salida) }}</dd>
                </div>
                <div>
                  <dt class="font-bold text-slate-500">Total</dt>
                  <dd class="text-lg font-black text-teal-800">Bs {{ boleto.precio_final }}</dd>
                </div>
              </dl>
            </div>

            <div class="grid gap-2 border-t border-slate-100 p-4 sm:grid-cols-2">
              <RouterLink class="btn btn-secondary" :to="{ name: 'rastrear-bus', query: { codigo: boleto.codigo_boleto } }">
                <MapPinned :size="18" />
                Rastrear bus
              </RouterLink>
              <button class="btn btn-primary" type="button" @click="imprimirBoleto(boleto)">
                <Printer :size="18" />
                Imprimir / guardar PDF
              </button>
            </div>
          </article>
        </div>

        <div v-else class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
          <QrCode class="mx-auto text-slate-400" :size="36" />
          <p class="mt-3 font-black text-slate-800">No encontramos boletos para ese carnet.</p>
          <p class="mt-1 text-sm font-semibold text-slate-600">Verifica el CI o compra desde boleteria publica.</p>
        </div>
      </section>
    </section>
  </main>
</template>

<script setup>
import { LoaderCircle, MapPinned, Printer, QrCode, Search, ShieldCheck, Ticket, UserRoundSearch } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../api/axios';
import CroquisBus from '../../components/CroquisBus.vue';

const route = useRoute();
const codigo = ref('');
const ciCliente = ref('');
const viaje = ref(null);
const cliente = ref(null);
const boletosCliente = ref([]);
const clienteBuscado = ref(false);
const error = ref('');
const errorCliente = ref('');
const consultando = ref(false);
const consultandoCliente = ref(false);

const codigoBus = computed(() => viaje.value?.codigo_bus || viaje.value?.bus?.gps_imei || viaje.value?.bus?.placa || '');
const pasajerosOrdenados = computed(() => {
  if (viaje.value?.pasajeros_alfabetico?.length) return viaje.value.pasajeros_alfabetico;

  return [...(viaje.value?.boletos || [])]
    .filter((boleto) => ['pendiente_verificacion', 'pagado', 'abordado'].includes(boleto.estado))
    .sort((a, b) => nombreCompleto(a.pasajero).localeCompare(nombreCompleto(b.pasajero), 'es'))
    .map((boleto) => ({
      boleto_id: boleto.id,
      nombre_completo: nombreCompleto(boleto.pasajero),
      ci: boleto.pasajero?.numero_ci,
      asiento: boleto.asiento?.numero,
      estado: boleto.estado,
      adulto_responsable: nombreCompleto(boleto.adulto_responsable)
    }));
});

const tituloCliente = computed(() => {
  if (cliente.value) {
    return `${nombreCompleto(cliente.value)} tiene los siguientes viajes comprados`;
  }

  return 'Usted tiene los siguientes viajes comprados';
});

onMounted(() => {
  const codigoQuery = route.query.codigo || route.query.bus;
  if (!codigoQuery) return;

  codigo.value = String(codigoQuery);
  consultar();
});

async function consultar() {
  error.value = '';
  viaje.value = null;

  if (!codigo.value) {
    error.value = 'Escribe un codigo de viaje o QR firmado.';
    return;
  }

  consultando.value = true;
  try {
    const consulta = await api.get(`/consulta/viaje/${encodeURIComponent(codigo.value)}`);
    const manifiesto = await api.get(`/consulta/viaje/${consulta.data.id}/manifiesto`);
    viaje.value = manifiesto.data;
  } catch (err) {
    error.value = err.message || 'No se pudo consultar el manifiesto.';
  } finally {
    consultando.value = false;
  }
}

async function consultarBoletos() {
  errorCliente.value = '';
  cliente.value = null;
  boletosCliente.value = [];
  clienteBuscado.value = false;

  if (!ciCliente.value) {
    errorCliente.value = 'Escribe el carnet del pasajero.';
    return;
  }

  consultandoCliente.value = true;
  try {
    const response = await api.get(`/consulta/cliente/${encodeURIComponent(ciCliente.value)}/boletos`);
    cliente.value = response.data.pasajero;
    boletosCliente.value = response.data.boletos || [];
    clienteBuscado.value = true;
  } catch (err) {
    errorCliente.value = err.message || 'No se pudieron consultar los boletos.';
  } finally {
    consultandoCliente.value = false;
  }
}

function imprimirBoleto(boleto) {
  const html = `
    <!doctype html>
    <html>
      <head>
        <meta charset="utf-8" />
        <title>Boleto ${escapeHtml(boleto.codigo_boleto)}</title>
        <style>
          body { font-family: Arial, sans-serif; margin: 0; color: #0f172a; background: #f8fafc; }
          .ticket { max-width: 760px; margin: 28px auto; background: white; border: 1px solid #cbd5e1; border-radius: 14px; overflow: hidden; }
          .top { padding: 22px; color: white; background: linear-gradient(135deg, #0f766e, #0f172a); }
          .top small { font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #99f6e4; }
          h1 { margin: 6px 0 0; font-size: 28px; }
          .body { display: grid; grid-template-columns: 180px 1fr; gap: 20px; padding: 22px; }
          .qr { display: grid; place-items: center; border: 1px solid #dbe5e1; border-radius: 12px; padding: 12px; }
          .qr img { width: 150px; height: 150px; }
          dl { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 18px; margin: 0; }
          dt { font-size: 12px; font-weight: 800; color: #64748b; text-transform: uppercase; }
          dd { margin: 3px 0 0; font-size: 16px; font-weight: 800; }
          .note { padding: 16px 22px; border-top: 1px solid #e2e8f0; color: #475569; font-size: 13px; font-weight: 700; }
          @media print { body { background: white; } .ticket { margin: 0; max-width: none; border-radius: 0; } }
        </style>
      </head>
      <body>
        <section class="ticket">
          <div class="top">
            <small>Terminal El Dorado - Boleto digital</small>
            <h1>${escapeHtml(boleto.codigo_boleto)}</h1>
          </div>
          <div class="body">
            <div class="qr">${boleto.qr_imagen ? `<img src="data:image/png;base64,${escapeHtml(boleto.qr_imagen)}" alt="QR" />` : '<strong>QR no disponible</strong>'}</div>
            <dl>
              <div><dt>Pasajero</dt><dd>${escapeHtml(nombreCompleto(boleto.pasajero))}</dd></div>
              <div><dt>CI</dt><dd>${escapeHtml(boleto.pasajero?.numero_ci || '-')}</dd></div>
              <div><dt>Ruta</dt><dd>${escapeHtml(boleto.viaje?.ruta?.origen || '-')} - ${escapeHtml(boleto.viaje?.ruta?.destino || '-')}</dd></div>
              <div><dt>Salida</dt><dd>${escapeHtml(fechaHora(boleto.viaje?.fecha_salida))}</dd></div>
              <div><dt>Bus</dt><dd>${escapeHtml(boleto.viaje?.bus?.placa || '-')}</dd></div>
              <div><dt>Asiento</dt><dd>${escapeHtml(boleto.asiento?.numero || '-')}</dd></div>
              <div><dt>Estado</dt><dd>${escapeHtml(estadoTexto(boleto.estado))}</dd></div>
              <div><dt>Total</dt><dd>Bs ${escapeHtml(boleto.precio_final || '0.00')}</dd></div>
            </dl>
          </div>
          <div class="note">Use este QR para control de abordaje y rastreo. En el dialogo de impresion puede elegir "Guardar como PDF".</div>
        </section>
      </body>
    </html>
  `;

  imprimirHtml(html);
}

function imprimirManifiesto() {
  if (!viaje.value) return;

  const rows = pasajerosOrdenados.value.map((pasajero) => `
    <tr>
      <td>${escapeHtml(pasajero.asiento || '-')}</td>
      <td>${escapeHtml(pasajero.nombre_completo)}</td>
      <td>${escapeHtml(pasajero.ci || '-')}</td>
      <td>${escapeHtml(estadoTexto(pasajero.estado))}</td>
      <td>${escapeHtml(pasajero.adulto_responsable || '-')}</td>
    </tr>
  `).join('');
  const seats = (viaje.value.croquis_asientos || []).map((asiento) => `
    <div class="seat ${asiento.pasajero_nombre ? 'busy' : 'free'}">
      <strong>${escapeHtml(asiento.numero)}</strong>
      <span>${escapeHtml(asiento.pasajero_nombre || 'Libre')}</span>
      <small>${escapeHtml(asiento.pasajero_ci || '')}</small>
    </div>
  `).join('');

  const html = `
    <!doctype html>
    <html>
      <head>
        <meta charset="utf-8" />
        <title>Manifiesto ${escapeHtml(viaje.value.codigo_viaje)}</title>
        <style>
          body { font-family: Arial, sans-serif; margin: 24px; color: #0f172a; }
          h1 { margin: 0; }
          p { margin: 4px 0 18px; color: #475569; font-weight: 700; }
          .meta { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 18px 0; }
          .box { border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; }
          .label { font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 800; }
          .value { margin-top: 4px; font-weight: 900; }
          .croquis { display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; margin: 16px 0 22px; }
          .seat { min-height: 48px; border: 1px solid #bbf7d0; background: #dcfce7; border-radius: 8px; padding: 5px; font-size: 10px; overflow: hidden; }
          .seat.busy { border-color: #93c5fd; background: #dbeafe; }
          .seat strong { display: block; font-size: 13px; }
          .seat span { display: block; font-weight: 800; }
          .seat small { color: #475569; }
          table { width: 100%; border-collapse: collapse; }
          th, td { border-bottom: 1px solid #cbd5e1; padding: 9px; text-align: left; }
          th { background: #f1f5f9; font-size: 12px; text-transform: uppercase; color: #475569; }
          @media print { .croquis { break-inside: avoid; } }
        </style>
      </head>
      <body>
        <h1>Constancia de bus ${escapeHtml(codigoBus.value)}</h1>
        <p>Manifiesto ${escapeHtml(viaje.value.codigo_viaje)} | ${escapeHtml(viaje.value.ruta?.origen || '-')} - ${escapeHtml(viaje.value.ruta?.destino || '-')} | ${escapeHtml(fechaHora(viaje.value.fecha_salida))}</p>
        <div class="meta">
          <div class="box"><div class="label">Codigo bus</div><div class="value">${escapeHtml(codigoBus.value)}</div></div>
          <div class="box"><div class="label">Placa</div><div class="value">${escapeHtml(viaje.value.bus?.placa || '-')}</div></div>
          <div class="box"><div class="label">Capacidad</div><div class="value">${escapeHtml(viaje.value.bus?.capacidad || '-')}</div></div>
          <div class="box"><div class="label">Pasajeros</div><div class="value">${escapeHtml(pasajerosOrdenados.value.length)}</div></div>
        </div>
        <h2>Croquis completo del bus</h2>
        <div class="croquis">${seats}</div>
        <h2>Lista alfabetica de pasajeros</h2>
        <table>
          <thead><tr><th>Asiento</th><th>Pasajero</th><th>CI</th><th>Estado</th><th>Adulto responsable</th></tr></thead>
          <tbody>${rows}</tbody>
        </table>
      </body>
    </html>
  `;

  imprimirHtml(html);
}

function imprimirHtml(html) {
  const ventana = window.open('', '_blank', 'width=860,height=900');

  if (!ventana) {
    errorCliente.value = 'El navegador bloqueo la ventana de impresion. Habilita ventanas emergentes.';
    return;
  }

  ventana.document.write(html);
  ventana.document.close();
  ventana.focus();
  window.setTimeout(() => ventana.print(), 300);
}

function estadoTexto(estado) {
  const textos = {
    pendiente_pago: 'Pendiente de pago',
    pendiente_verificacion: 'Huella pendiente',
    pagado: 'Comprado',
    abordado: 'Abordado',
    cancelado: 'Cancelado',
    reembolsado: 'Reembolsado'
  };

  return textos[estado] || estado || '-';
}

function estadoClass(estado) {
  if (estado === 'pagado') return 'bg-emerald-100 text-emerald-800';
  if (estado === 'abordado') return 'bg-blue-100 text-blue-800';
  if (estado === 'pendiente_verificacion') return 'bg-amber-100 text-amber-900';
  if (['cancelado', 'reembolsado'].includes(estado)) return 'bg-red-100 text-red-800';
  return 'bg-slate-100 text-slate-700';
}

function nombreCompleto(pasajero) {
  return pasajero ? `${pasajero.nombres || ''} ${pasajero.apellidos || ''}`.trim() : '';
}

function fechaHora(value) {
  return value
    ? new Date(value).toLocaleString('es-BO', {
      dateStyle: 'medium',
      timeStyle: 'short'
    })
    : '-';
}

function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}
</script>
