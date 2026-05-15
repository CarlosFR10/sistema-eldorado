<template>
  <section class="mx-auto max-w-5xl space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-sm font-black uppercase text-eldorado-teal">Programacion</p>
        <h1 class="text-2xl font-black text-slate-900">Agregar viaje</h1>
      </div>
      <RouterLink class="btn btn-secondary" to="/venta/viajes">
        <ArrowLeft :size="18" />
        Volver a viajes
      </RouterLink>
    </div>

    <form class="panel rounded-lg p-4" @submit.prevent="guardar">
      <div class="grid gap-4 md:grid-cols-3">
        <label class="space-y-1">
          <span class="text-sm font-bold text-slate-600">Ruta</span>
          <select v-model="form.ruta_id" class="field" required>
            <option value="">Seleccionar ruta</option>
            <option v-for="ruta in viajeStore.rutas" :key="ruta.id" :value="ruta.id">
              {{ ruta.origen }} - {{ ruta.destino }} | Bs {{ ruta.precio_base }}
            </option>
          </select>
        </label>

        <label class="space-y-1">
          <span class="text-sm font-bold text-slate-600">Bus</span>
          <select v-model="form.bus_id" class="field" required>
            <option value="">Seleccionar bus</option>
            <option v-for="bus in viajeStore.buses" :key="bus.id" :value="bus.id">
              {{ bus.placa }} - {{ bus.marca }} {{ bus.modelo }} ({{ bus.capacidad }} asientos)
            </option>
          </select>
        </label>

        <label class="space-y-1">
          <span class="text-sm font-bold text-slate-600">Fecha de salida</span>
          <input v-model="form.fecha" class="field" type="date" required />
        </label>

        <div class="md:col-span-3">
          <p class="mb-2 text-sm font-bold text-slate-600">
            Horas disponibles
            <span v-if="form.bus_id && form.fecha" class="ml-2 text-teal-600">
              ({{ horasDisponibles.length }} libres / {{ horasBloqueadas.length }} ocupados / {{ horasPasadas.length }} pasados)
            </span>
          </p>
          <div v-if="!form.bus_id || !form.fecha" class="rounded-md bg-slate-50 p-3 text-sm text-slate-500">
            Seleccione bus y fecha para ver horarios disponibles.
          </div>
          <div v-else-if="cargandoHoras" class="text-sm text-slate-500">Cargando...</div>
          <div v-else class="flex flex-wrap gap-2">
            <button
              v-for="hora in horasEstandar"
              :key="hora"
              type="button"
              class="rounded-lg border px-4 py-3 font-bold transition min-w-[100px] text-center"
              :class="form.hora_salida === hora
                ? 'border-teal-600 bg-teal-600 text-white shadow-md'
                : horasPasadas.includes(hora)
                  ? 'cursor-not-allowed border-slate-300 bg-slate-200 text-slate-400 opacity-70'
                  : horasBloqueadas.includes(hora)
                    ? 'cursor-not-allowed border-red-300 bg-red-50 text-red-400 line-through opacity-70'
                    : 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 hover:border-emerald-400'"
              :disabled="horasPasadas.includes(hora) || horasBloqueadas.includes(hora)"
              @click="form.hora_salida = hora"
            >
              {{ hora }}
              <span v-if="horasPasadas.includes(hora)" class="mt-1 block text-xs font-normal">Pasado</span>
              <span v-else-if="horasBloqueadas.includes(hora)" class="mt-1 block text-xs font-normal">Ocupado</span>
            </button>
          </div>
          <p v-if="form.hora_salida && !horasBloqueadas.includes(form.hora_salida)" class="mt-2 text-sm font-bold text-teal-600">
            Hora seleccionada: {{ form.hora_salida }}
          </p>
          <p v-else-if="!form.hora_salida && horasDisponibles.length === 0" class="mt-2 text-sm font-bold text-red-600">
            No hay horarios disponibles para este bus en esta fecha.
          </p>
        </div>

        <div v-if="busSeleccionado" class="rounded-lg border border-slate-200 bg-slate-50 p-3 md:col-span-3">
          <p class="font-black text-slate-900">
            {{ busSeleccionado.placa }} - {{ busSeleccionado.tipo_bus }} - {{ busSeleccionado.capacidad }} asientos
          </p>
          <p class="text-sm text-slate-600">
            Croquis: {{ busSeleccionado.config_asientos?.filas }} filas,
            {{ busSeleccionado.config_asientos?.columnas }} columnas
          </p>
        </div>

        <label class="space-y-1">
          <span class="text-sm font-bold text-slate-600">Llegada estimada</span>
          <input class="field bg-slate-50" :value="fechaLlegadaEstimada" readonly />
        </label>

        <label class="space-y-1">
          <span class="text-sm font-bold text-slate-600">Precio final</span>
          <input v-model.number="form.precio_final" class="field" min="0" step="0.01" type="number" required />
        </label>

        <label class="space-y-1">
          <span class="text-sm font-bold text-slate-600">Estado</span>
          <select v-model="form.estado" class="field">
            <option value="en_venta">En venta</option>
          </select>
        </label>

        <label class="space-y-1 md:col-span-3">
          <span class="text-sm font-bold text-slate-600">Observaciones</span>
          <textarea v-model="form.observaciones" class="field min-h-20" placeholder="Nota interna opcional"></textarea>
        </label>
      </div>

      <div class="mt-4 flex flex-wrap items-center gap-3">
        <button
          class="btn btn-primary"
          :disabled="guardando || !form.hora_salida || horasBloqueadas.includes(form.hora_salida)"
        >
          <Plus :size="18" />
          {{ guardando ? 'Guardando...' : 'Crear viaje' }}
        </button>
        <p v-if="message" class="rounded-md bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-800">{{ message }}</p>
        <p v-if="error" class="rounded-md bg-red-50 px-3 py-2 text-sm font-bold text-red-800">{{ error }}</p>
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
    console.log('horasDisponibles response:', response);
    const data = response?.data ?? response;
    horasDisponibles.value = data?.disponibles || [];
    horasBloqueadas.value = data?.bloqueados || [];
    horasPasadas.value = data?.pasados || [];
    console.log('disponibles:', horasDisponibles.value, 'bloqueados:', horasBloqueadas.value, 'pasados:', horasPasadas.value);
  } catch (err) {
    console.error('Error cargando horas:', err);
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