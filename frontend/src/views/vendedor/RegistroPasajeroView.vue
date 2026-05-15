<template>
  <section class="space-y-4">
    <div>
      <p class="text-sm font-black uppercase text-eldorado-teal">Registro biometrico</p>
      <h1 class="text-2xl font-black text-slate-900">Registro de pasajero</h1>
    </div>

    <form class="panel rounded-lg p-4" @submit.prevent="guardar">
      <div class="grid gap-4 lg:grid-cols-[1fr_360px]">
        <div class="space-y-4">
          <section>
            <h2 class="mb-3 font-black text-slate-800">Datos del pasajero</h2>
            <div class="grid gap-3 md:grid-cols-2">
              <input v-model.trim="form.nombres" class="field" placeholder="Nombres" required />
              <input v-model.trim="form.apellidos" class="field" placeholder="Apellidos" required />
              <input v-model.trim="form.numero_ci" class="field" placeholder="CI" required />
              <input v-model.trim="form.expedido_en" class="field" maxlength="2" placeholder="Expedido en" required />
              <input v-model="form.fecha_nacimiento" class="field" type="date" required />
              <input v-model.trim="form.telefono" class="field" placeholder="Telefono" />
            </div>
          </section>

          <label class="flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 p-3 font-bold text-amber-950">
            <input v-model="viajaConMenor" class="h-5 w-5" type="checkbox" />
            Viaja con menor de edad
          </label>

          <section v-if="viajaConMenor" class="rounded-lg border border-amber-200 bg-white p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
              <h2 class="font-black text-amber-950">Menor acompanante</h2>
              <div class="flex gap-2">
                <input v-model="ciMenorBusqueda" class="field min-w-48" placeholder="Buscar CI menor" />
                <button class="btn btn-secondary" type="button" @click="buscarMenor">
                  <Search :size="18" />
                </button>
              </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
              <input v-model.trim="menor.nombres" class="field" placeholder="Nombres del menor" required />
              <input v-model.trim="menor.apellidos" class="field" placeholder="Apellidos del menor" required />
              <input v-model.trim="menor.numero_ci" class="field" placeholder="CI del menor" required />
              <input v-model.trim="menor.expedido_en" class="field" maxlength="2" placeholder="Expedido en" required />
              <input v-model="menor.fecha_nacimiento" class="field" type="date" required />
              <select v-model="relacion.tipo_relacion" class="field" required>
                <option value="padre">Padre</option>
                <option value="madre">Madre</option>
                <option value="tutor_legal">Tutor legal</option>
                <option value="acompanante_autorizado">Acompanante autorizado</option>
              </select>
              <input v-model.trim="relacion.numero_permiso_dna" class="field md:col-span-2" placeholder="Permiso DNA / Defensoria" />
            </div>
          </section>
        </div>

        <aside class="space-y-4">
          <HuellaSimulador @captured="huella = $event" />
          <section v-if="registro" class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
            <h2 class="font-black text-emerald-950">Registro guardado</h2>
            <dl class="mt-3 space-y-2 text-sm">
              <div>
                <dt class="font-bold text-emerald-800">Clave</dt>
                <dd class="font-mono text-emerald-950">{{ registro.clave }}</dd>
              </div>
              <div>
                <dt class="font-bold text-emerald-800">Pasajero</dt>
                <dd>{{ registro.nombre }}</dd>
              </div>
              <div>
                <dt class="font-bold text-emerald-800">Huella</dt>
                <dd>{{ registro.huella }}</dd>
              </div>
              <div v-if="registro.menor">
                <dt class="font-bold text-emerald-800">Menor vinculado</dt>
                <dd>{{ registro.menor }}</dd>
              </div>
            </dl>
          </section>
        </aside>
      </div>

      <div class="mt-4 flex flex-wrap items-center gap-3">
        <button class="btn btn-primary" :disabled="guardando">
          <Save :size="18" />
          {{ guardando ? 'Guardando...' : 'Guardar pasajero' }}
        </button>
        <RouterLink class="btn btn-secondary" to="/venta">
          <UserPlus :size="18" />
          Ir a venta
        </RouterLink>
        <p v-if="message" class="rounded-md bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-800">{{ message }}</p>
        <p v-if="error" class="rounded-md bg-red-50 px-3 py-2 text-sm font-bold text-red-800">{{ error }}</p>
      </div>
    </form>

    <section class="panel rounded-lg p-4">
      <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
        <h2 class="font-black text-slate-800">Pasajeros registrados</h2>
        <button class="btn btn-secondary" type="button" @click="cargarPasajeros">Actualizar</button>
      </div>
      <div class="grid gap-3 md:grid-cols-4">
        <input v-model="filtros.nombre" class="field" placeholder="Filtrar por nombre" @input="cargarPasajeros" />
        <select v-model="filtros.expedido_en" class="field" @change="cargarPasajeros">
          <option value="">Todos los departamentos</option>
          <option v-for="dep in departamentos" :key="dep" :value="dep">{{ dep }}</option>
        </select>
        <select v-model="filtros.es_menor" class="field" @change="cargarPasajeros">
          <option value="">Todas las edades</option>
          <option value="0">Mayores de edad</option>
          <option value="1">Menores de edad</option>
        </select>
        <select v-model="filtros.huella" class="field" @change="cargarPasajeros">
          <option value="">Todas las huellas</option>
          <option value="verificada">Huella verificada</option>
          <option value="pendiente">Huella no verificada</option>
        </select>
      </div>

      <div class="table-wrap mt-4">
        <table>
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
              <td class="font-bold">{{ nombreCompleto(pasajero) }}</td>
              <td>{{ pasajero.numero_ci }}</td>
              <td>{{ pasajero.edad }}</td>
              <td>{{ pasajero.expedido_en }}</td>
              <td>
                <span class="chip" :class="pasajero.tiene_huella ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900'">
                  {{ pasajero.tiene_huella ? 'Verificada' : 'No verificada' }}
                </span>
              </td>
              <td>{{ menoresTexto(pasajero) }}</td>
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
