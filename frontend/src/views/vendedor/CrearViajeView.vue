<template>
  <section class="mx-auto max-w-5xl space-y-6 dark:text-white">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-sm font-bold uppercase text-blue-600">Programacion</p>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Agregar viaje</h1>
      </div>
      <RouterLink class="btn-secondary" to="/venta/viajes">
        <ArrowLeft :size="18" />
        Volver a viajes
      </RouterLink>
    </div>

    <form class="card p-6" @submit.prevent="guardar">
      <div class="grid gap-5 md:grid-cols-3">
        <div class="space-y-1.5">
          <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">Ruta</label>
          <select v-model="form.ruta_id" class="form-input" required>
            <option value="">Seleccionar ruta</option>
            <option v-for="ruta in viajeStore.rutas" :key="ruta.id" :value="ruta.id">
              {{ ruta.origen }} - {{ ruta.destino }} | Bs {{ ruta.precio_base }}
            </option>
          </select>
        </div>

        <div class="space-y-1.5">
          <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">Bus</label>
          <select v-model="form.bus_id" class="form-input" required>
            <option value="">Seleccionar bus</option>
            <option v-for="bus in viajeStore.buses" :key="bus.id" :value="bus.id">
              {{ bus.placa }} - {{ bus.marca }} {{ bus.modelo }} ({{ bus.capacidad }} asientos)
            </option>
          </select>
        </div>

        <div class="space-y-1.5">
          <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">Fecha de salida</label>
          <input v-model="form.fecha" class="form-input" type="date" required />
        </div>

        <div class="md:col-span-3">
          <p class="mb-3 text-sm font-semibold text-slate-600 dark:text-slate-300">
            Horas disponibles
            <span v-if="form.bus_id && form.fecha" class="ml-2 text-blue-600 font-medium">
              ({{ horasDisponibles.length }} libres / {{ horasBloqueadas.length }} ocupados / {{ horasPasadas.length }} pasados)
            </span>
          </p>
          <div v-if="!form.bus_id || !form.fecha" class="rounded-xl bg-slate-50 dark:bg-slate-700 p-4 text-sm text-slate-500 dark:text-slate-300 text-center">
            Seleccione bus y fecha para ver horarios disponibles.
          </div>
          <div v-else-if="cargandoHoras" class="text-sm text-slate-500 dark:text-slate-300">Cargando...</div>
          <div v-else class="flex flex-wrap gap-2">
            <button
              v-for="hora in horasEstandar"
              :key="hora"
              type="button"
              class="rounded-xl border px-4 py-3 font-semibold transition min-w-[100px] text-center"
              :class="form.hora_salida === hora
                ? 'border-blue-600 bg-blue-600 text-white shadow-md dark:border-teal-500 dark:bg-teal-600'
                : horasPasadas.includes(hora)
                  ? 'cursor-not-allowed border-slate-300 bg-slate-200 text-slate-400 opacity-70 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-500'
                  : horasBloqueadas.includes(hora)
                    ? 'cursor-not-allowed border-red-300 bg-red-50 text-red-400 line-through opacity-70 dark:border-red-800 dark:bg-red-900/20 dark:text-red-500'
                    : 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 hover:border-emerald-400 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/30'"
              :disabled="horasPasadas.includes(hora) || horasBloqueadas.includes(hora)"
              @click="form.hora_salida = hora"
            >
              {{ hora }}
              <span v-if="horasPasadas.includes(hora)" class="mt-1 block text-xs font-normal">Pasado</span>
              <span v-else-if="horasBloqueadas.includes(hora)" class="mt-1 block text-xs font-normal">Ocupado</span>
            </button>
          </div>
          <p v-if="form.hora_salida && !horasBloqueadas.includes(form.hora_salida)" class="mt-2 text-sm font-semibold text-blue-600">
            Hora seleccionada: {{ form.hora_salida }}
          </p>
          <p v-else-if="!form.hora_salida && horasDisponibles.length === 0" class="mt-2 text-sm font-semibold text-red-600">
            No hay horarios disponibles para este bus en esta fecha.
          </p>
        </div>

        <div v-if="busSeleccionado" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700 p-4 md:col-span-3">
          <p class="font-bold text-slate-900 dark:text-white">
            {{ busSeleccionado.placa }} - {{ busSeleccionado.tipo_bus }} - {{ busSeleccionado.capacidad }} asientos
          </p>
          <p class="text-sm text-slate-600 dark:text-slate-300">
            Croquis: {{ busSeleccionado.config_asientos?.filas }} filas,
            {{ busSeleccionado.config_asientos?.columnas }} columnas
          </p>
        </div>

        <div class="space-y-1.5">
          <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">Llegada estimada</label>
          <input class="form-input bg-slate-100 dark:bg-slate-600 dark:text-white" :value="fechaLlegadaEstimada" readonly />
        </div>

        <div class="space-y-1.5">
          <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">Precio final</label>
          <input v-model.number="form.precio_final" class="form-input" min="0" step="0.01" type="number" required />
        </div>

        <div class="space-y-1.5">
          <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">Estado</label>
          <select v-model="form.estado" class="form-input">
            <option value="en_venta">En venta</option>
          </select>
        </div>

        <div class="space-y-1.5 md:col-span-3">
          <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">Observaciones</label>
          <textarea v-model="form.observaciones" class="form-input min-h-20" placeholder="Nota interna opcional"></textarea>
        </div>
      </div>

      <div class="mt-6 flex flex-wrap items-center gap-3">
        <button
          class="btn-primary"
          :disabled="guardando || !form.hora_salida || horasBloqueadas.includes(form.hora_salida)"
        >
          <Plus :size="18" />
          {{ guardando ? 'Guardando...' : 'Crear viaje' }}
        </button>
        <div v-if="message" class="alert alert-success">{{ message }}</div>
        <div v-if="error" class="alert alert-error">{{ error }}</div>
      </div>
    </form>
  </section>
</template>

<script setup>
import { ArrowLeft, Plus } from 'lucide-vue-next';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { crearViaje, horasDisponibles as apiHorasDisponibles } from '../../api/viajes';
import { useViajeStore } from '../../stores/viaje';

const viajeStore = useViajeStore();
const guardando = ref(false);
const message = ref('');
const error = ref('');

const horasEstandar = ['06:00', '07:30', '09:00', '10:30', '12:00', '13:30', '15:00', '16:30', '18:00', '20:00'];
const horasDisponibles = ref([]);
const horasBloqueadas = ref([]);
const horasPasadas = ref([]);
const cargandoHoras = ref(false);

const form = reactive({
  ruta_id: '',
  bus_id: '',
  fecha: new Date().toISOString().slice(0, 10),
  hora_salida: '',
  precio_final: '',
  estado: 'en_venta',
  observaciones: ''
});

const rutaSeleccionada = computed(() => viajeStore.rutas.find((ruta) => Number(ruta.id) === Number(form.ruta_id)));
const busSeleccionado = computed(() => viajeStore.buses.find((bus) => Number(bus.id) === Number(form.bus_id)));

const fechaSalidaCompleta = computed(() => {
  if (!form.fecha || !form.hora_salida) return '';
  return `${form.fecha}T${form.hora_salida}:00`;
});

const fechaLlegadaEstimada = computed(() => {
  if (!fechaSalidaCompleta.value) return '';
  const date = new Date(fechaSalidaCompleta.value);
  const duracion = parseFloat(rutaSeleccionada.value?.duracion_horas || 1.5);
  date.setMinutes(date.getMinutes() + Math.round(duracion * 60));
  return formatDateTimeLocal(date);
});

onMounted(async () => {
  await viajeStore.cargarCatalogos();
  if (!form.ruta_id && viajeStore.rutas.length) form.ruta_id = viajeStore.rutas[0].id;
  if (!form.bus_id && viajeStore.buses.length) form.bus_id = viajeStore.buses[0].id;
  await cargarHorasDisponibles();
});

watch([() => form.bus_id, () => form.fecha], async () => {
  form.hora_salida = '';
  if (form.bus_id && form.fecha) {
    await cargarHorasDisponibles();
    if (horasDisponibles.value.length) {
      form.hora_salida = horasDisponibles.value[horasDisponibles.value.length - 1];
    }
  } else {
    horasDisponibles.value = [];
    horasBloqueadas.value = [];
  }
});

watch(rutaSeleccionada, (ruta) => {
  if (ruta) form.precio_final = Number(ruta.precio_base);
});

async function cargarHorasDisponibles() {
  if (!form.bus_id || !form.fecha) return;
  cargandoHoras.value = true;
  try {
    const response = await apiHorasDisponibles({ bus_id: form.bus_id, fecha: form.fecha });
    const data = response?.data ?? response;
    horasDisponibles.value = data?.disponibles || [];
    horasBloqueadas.value = data?.bloqueados || [];
    horasPasadas.value = data?.pasados || [];
  } catch (err) {
    horasDisponibles.value = [...horasEstandar];
    horasBloqueadas.value = [];
    horasPasadas.value = [];
  } finally {
    cargandoHoras.value = false;
  }
}

async function guardar() {
  message.value = '';
  error.value = '';

  if (!form.hora_salida || !horasBloqueadas.value || horasBloqueadas.value.includes(form.hora_salida)) {
    error.value = 'Seleccione un horario disponible.';
    return;
  }

  guardando.value = true;

  try {
    const salida = new Date(`${form.fecha}T${form.hora_salida}:00`);
    const llegada = new Date(salida);
    const duracion = parseFloat(rutaSeleccionada.value?.duracion_horas || 1.5);
    llegada.setMinutes(llegada.getMinutes() + Math.round(duracion * 60));

    const response = await crearViaje({
      ruta_id: form.ruta_id,
      bus_id: form.bus_id,
      fecha_salida: formatDateTimeLocal(salida),
      fecha_llegada_est: formatDateTimeLocal(llegada),
      precio_final: form.precio_final,
      estado: form.estado,
      observaciones: form.observaciones || 'Salida generada desde panel vendedor'
    });

    const codigo = response.data?.codigo_viaje || 'viaje creado';
    message.value = `Viaje ${codigo} creado exitosamente.`;
    form.hora_salida = '';
    await cargarHorasDisponibles();

    if (horasDisponibles.value.length === 0) {
      form.hora_salida = '';
    } else {
      form.hora_salida = horasDisponibles.value[horasDisponibles.value.length - 1];
    }
  } catch (err) {
    error.value = (err.message && typeof err.message === 'string') ? err.message : 'No se pudo crear el viaje.';
  } finally {
    guardando.value = false;
  }
}

function formatDateTimeLocal(date) {
  const pad = (value) => String(value).padStart(2, '0');
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
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

.alert {
  @apply p-3 rounded-xl text-sm font-medium;
}

.alert-success {
  @apply bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400;
}

.alert-error {
  @apply bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400;
}
</style>
