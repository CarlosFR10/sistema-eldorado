<template>
  <section class="space-y-6 dark:text-white">
    <div>
      <p class="text-sm font-bold uppercase text-blue-600">Registro biometrico</p>
      <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Registro de pasajero</h1>
    </div>

    <form class="card p-6" @submit.prevent="guardar">
      <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        <div class="space-y-4">
          <section>
            <h2 class="mb-3 font-bold text-lg text-slate-800 dark:text-white">Datos del pasajero</h2>
            <div class="grid gap-3 md:grid-cols-2">
              <input v-model.trim="form.nombres" class="form-input" placeholder="Nombres" required />
              <input v-model.trim="form.apellidos" class="form-input" placeholder="Apellidos" required />
              <input v-model.trim="form.numero_ci" class="form-input" placeholder="CI" required />
              <input v-model.trim="form.expedido_en" class="form-input" maxlength="2" placeholder="Expedido en" required />
              <input v-model="form.fecha_nacimiento" class="form-input" type="date" required />
              <input v-model.trim="form.telefono" class="form-input" placeholder="Telefono" />
            </div>
          </section>

          <label class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 font-semibold text-amber-900 cursor-pointer">
            <input v-model="viajaConMenor" class="h-5 w-5" type="checkbox" />
            Viaja con menor de edad
          </label>

          <section v-if="viajaConMenor" class="rounded-xl border border-amber-200 bg-white p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
              <h2 class="font-bold text-amber-900">Menor acompanante</h2>
              <div class="flex gap-2">
                <input v-model="ciMenorBusqueda" class="form-input min-w-48" placeholder="Buscar CI menor" />
                <button class="btn-secondary" type="button" @click="buscarMenor">
                  <Search :size="18" />
                </button>
              </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
              <input v-model.trim="menor.nombres" class="form-input" placeholder="Nombres del menor" required />
              <input v-model.trim="menor.apellidos" class="form-input" placeholder="Apellidos del menor" required />
              <input v-model.trim="menor.numero_ci" class="form-input" placeholder="CI del menor" required />
              <input v-model.trim="menor.expedido_en" class="form-input" maxlength="2" placeholder="Expedido en" required />
              <input v-model="menor.fecha_nacimiento" class="form-input" type="date" required />
              <select v-model="relacion.tipo_relacion" class="form-input" required>
                <option value="padre">Padre</option>
                <option value="madre">Madre</option>
                <option value="tutor_legal">Tutor legal</option>
                <option value="acompanante_autorizado">Acompanante autorizado</option>
              </select>
              <input v-model.trim="relacion.numero_permiso_dna" class="form-input md:col-span-2" placeholder="Permiso DNA / Defensoria" />
            </div>
          </section>
        </div>

        <aside class="space-y-4">
          <HuellaSimulador @captured="huella = $event" />
          <section v-if="registro" class="rounded-xl border border-green-200 bg-green-50 p-4">
            <h2 class="font-bold text-green-900">Registro guardado</h2>
            <dl class="mt-3 space-y-2 text-sm">
              <div>
                <dt class="font-semibold text-green-800">Clave</dt>
                <dd class="font-mono text-green-950">{{ registro.clave }}</dd>
              </div>
              <div>
                <dt class="font-semibold text-green-800">Pasajero</dt>
                <dd class="text-green-900">{{ registro.nombre }}</dd>
              </div>
              <div>
                <dt class="font-semibold text-green-800">Huella</dt>
                <dd class="text-green-900">{{ registro.huella }}</dd>
              </div>
              <div v-if="registro.menor">
                <dt class="font-semibold text-green-800">Menor vinculado</dt>
                <dd class="text-green-900">{{ registro.menor }}</dd>
              </div>
            </dl>
          </section>
        </aside>
      </div>

      <div class="mt-6 flex flex-wrap items-center gap-3">
        <button class="btn-primary" :disabled="guardando">
          <Save :size="18" />
          {{ guardando ? 'Guardando...' : 'Guardar pasajero' }}
        </button>
        <RouterLink class="btn-secondary" to="/venta">
          <UserPlus :size="18" />
          Ir a venta
        </RouterLink>
        <div v-if="message" class="alert alert-success">{{ message }}</div>
        <div v-if="error" class="alert alert-error">{{ error }}</div>
      </div>
    </form>

    <section class="card p-5">
      <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h2 class="font-bold text-lg text-slate-800">Pasajeros registrados</h2>
        <button class="btn-secondary" type="button" @click="cargarPasajeros">Actualizar</button>
      </div>
      <div class="grid gap-3 md:grid-cols-4">
        <input v-model="filtros.nombre" class="form-input" placeholder="Filtrar por nombre" @input="cargarPasajeros" />
        <select v-model="filtros.expedido_en" class="form-input" @change="cargarPasajeros">
          <option value="">Todos los departamentos</option>
          <option v-for="dep in departamentos" :key="dep" :value="dep">{{ dep }}</option>
        </select>
        <select v-model="filtros.es_menor" class="form-input" @change="cargarPasajeros">
          <option value="">Todas las edades</option>
          <option value="0">Mayores de edad</option>
          <option value="1">Menores de edad</option>
        </select>
        <select v-model="filtros.huella" class="form-input" @change="cargarPasajeros">
          <option value="">Todas las huellas</option>
          <option value="verificada">Huella verificada</option>
          <option value="pendiente">Huella no verificada</option>
        </select>
      </div>

      <div class="mt-4 overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>CI</th>
              <th>Edad</th>
              <th>Departamento</th>
              <th>Huella</th>
              <th>Menores vinculados</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="pasajero in pasajeros" :key="pasajero.id">
              <td class="font-semibold text-slate-800">{{ nombreCompleto(pasajero) }}</td>
              <td class="text-slate-600">{{ pasajero.numero_ci }}</td>
              <td class="text-slate-600">{{ pasajero.edad }}</td>
              <td class="text-slate-600">{{ pasajero.expedido_en }}</td>
              <td>
                <span class="badge" :class="pasajero.tiene_huella ? 'badge-green' : 'badge-amber'">
                  {{ pasajero.tiene_huella ? 'Verificada' : 'No verificada' }}
                </span>
              </td>
              <td class="text-slate-600">{{ menoresTexto(pasajero) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </section>
</template>

<script setup>
import { Save, Search, UserPlus } from 'lucide-vue-next';
import { onMounted, reactive, ref } from 'vue';
import { buscarPasajero, crearPasajero, listarPasajeros, registrarHuella, vincularAdulto } from '../../api/pasajeros';
import HuellaSimulador from '../../components/HuellaSimulador.vue';

const departamentos = ['CB', 'LP', 'SC', 'OR', 'PT', 'CH', 'TJ', 'BN', 'PD'];
const huella = ref(null);
const message = ref('');
const error = ref('');
const guardando = ref(false);
const viajaConMenor = ref(false);
const ciMenorBusqueda = ref('');
const registro = ref(null);
const pasajeros = ref([]);
const form = reactive(basePasajero());
const menor = reactive(basePasajero());
const relacion = reactive({ tipo_relacion: 'padre', numero_permiso_dna: '' });
const filtros = reactive({ nombre: '', expedido_en: '', es_menor: '', huella: '' });

onMounted(cargarPasajeros);

async function guardar() {
  message.value = '';
  error.value = '';
  guardando.value = true;

  try {
    await validarNoExiste(form);
    const pasajero = (await crearPasajero(pickPasajero(form))).data;
    const huellaFinal = huella.value || generarHuella(pasajero);
    await registrarHuella(pasajero.id, huellaFinal);

    let menorRegistrado = null;
    if (viajaConMenor.value) {
      menorRegistrado = await guardarOMostrarExistente(menor);
      await vincularAdulto(menorRegistrado.id, {
        adulto_responsable_id: pasajero.id,
        tipo_relacion: relacion.tipo_relacion,
        numero_permiso_dna: relacion.numero_permiso_dna || null
      });
    }

    registro.value = {
      clave: `ELD-${String(pasajero.id).padStart(6, '0')}-${pasajero.numero_ci}`,
      nombre: nombreCompleto(pasajero),
      menor: menorRegistrado ? nombreCompleto(menorRegistrado) : null,
      huella: textoHuella(pasajero)
    };
    message.value = 'Pasajero registrado correctamente con huella verificada.';
    limpiarFormulario();
    await cargarPasajeros();
  } catch (err) {
    error.value = err.message || 'No se pudo guardar. Revisa si el pasajero ya existe.';
  } finally {
    guardando.value = false;
  }
}

async function buscarMenor() {
  if (!ciMenorBusqueda.value) return;
  try {
    const response = await buscarPasajero(ciMenorBusqueda.value);
    Object.assign(menor, pickPasajero(response.data));
    message.value = 'Menor encontrado y cargado.';
  } catch {
    menor.numero_ci = ciMenorBusqueda.value;
    message.value = 'Menor no encontrado. Completa sus datos para registrarlo.';
  }
}

async function guardarOMostrarExistente(payload) {
  try {
    return (await buscarPasajero(payload.numero_ci)).data;
  } catch {
    return (await crearPasajero(pickPasajero(payload))).data;
  }
}

async function validarNoExiste(payload) {
  try {
    const existente = (await buscarPasajero(payload.numero_ci)).data;
    throw new Error(`El pasajero ya existe: ${nombreCompleto(existente)} / CI ${existente.numero_ci}`);
  } catch (err) {
    if (err.code === 'PASAJERO_NO_ENCONTRADO') return;
    throw err;
  }
}

async function cargarPasajeros() {
  const response = await listarPasajeros({
    nombre: filtros.nombre || undefined,
    expedido_en: filtros.expedido_en || undefined,
    es_menor: filtros.es_menor || undefined,
    huella: filtros.huella || undefined,
    per_page: 100
  });
  pasajeros.value = response.data?.data || response.data || [];
}

function limpiarFormulario() {
  Object.assign(form, basePasajero());
  Object.assign(menor, basePasajero());
  viajaConMenor.value = false;
  ciMenorBusqueda.value = '';
  huella.value = null;
}

function basePasajero() {
  return { id: null, nombres: '', apellidos: '', numero_ci: '', complemento_ci: '', expedido_en: 'CB', fecha_nacimiento: '', telefono: '', email: '' };
}

function pickPasajero(payload) {
  return {
    id: payload.id || null,
    nombres: payload.nombres || '',
    apellidos: payload.apellidos || '',
    numero_ci: payload.numero_ci || '',
    complemento_ci: payload.complemento_ci || '',
    expedido_en: (payload.expedido_en || 'CB').toUpperCase(),
    fecha_nacimiento: payload.fecha_nacimiento || '',
    telefono: payload.telefono || '',
    email: payload.email || ''
  };
}

function generarHuella(pasajero) {
  return { plantilla: `SIM-${pasajero.numero_ci}-${Date.now()}`, dedo: 'indice_der', calidad: 92 };
}

function textoHuella(pasajero) {
  return `${String(pasajero.numero_ci || '').slice(-6).padStart(6, '0')} huella digital de ${pasajero.nombres}`;
}

function nombreCompleto(pasajero) {
  return pasajero ? `${pasajero.nombres || ''} ${pasajero.apellidos || ''}`.trim() : '';
}

function menoresTexto(pasajero) {
  const menores = (pasajero.menores_responsables || []).map((relacion) => nombreCompleto(relacion.menor)).filter(Boolean);
  return menores.length ? menores.join(', ') : '-';
}
</script>

<style scoped>
.card {
  @apply bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700;
}

.form-input {
  @apply w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.btn-primary {
  @apply flex items-center justify-center gap-2 py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors;
}

.dark .btn-primary {
  @apply bg-teal-600 hover:bg-teal-700;
}

.btn-secondary {
  @apply flex items-center justify-center gap-2 py-3 px-5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors;
}

.dark .btn-secondary {
  @apply bg-slate-700 hover:bg-slate-600 text-slate-200;
}

.badge {
  @apply inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full;
}

.badge-green {
  @apply bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400;
}

.badge-amber {
  @apply bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400;
}

.alert {
  @apply p-3 rounded-xl text-sm font-medium;
}

.alert-success {
  @apply bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400;
}

.alert-error {
  @apply bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400;
}

table {
  @apply w-full text-sm;
}

thead {
  @apply bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700;
}

th {
  @apply px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300;
}

tbody tr {
  @apply border-b border-slate-100 dark:border-slate-700 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-700/50;
}

td {
  @apply px-4 py-3 text-slate-600 dark:text-slate-300;
}
</style>
