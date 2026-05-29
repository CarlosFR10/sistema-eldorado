<template>
  <section class="grid gap-6 lg:grid-cols-[400px_1fr]">
    <aside class="space-y-4">
      <section class="card p-5">
        <h1 class="mb-3 text-xl font-bold text-slate-800">Viaje de abordaje</h1>
        <select v-model="viajeId" class="form-input" @change="cargarListas">
          <option :value="null">Seleccionar viaje</option>
          <option v-for="viaje in viajeStore.viajes" :key="viaje.id" :value="viaje.id">
            {{ viaje.ruta?.codigo }} - {{ hora(viaje.fecha_salida) }}
          </option>
        </select>
      </section>

      <QrScanner @decoded="validarCodigo" />
    </aside>

    <main class="space-y-4">
      <section v-if="resultado" class="rounded-xl p-5" :class="resultado.success ? 'bg-green-50 text-green-900 border border-green-200' : 'bg-red-50 text-red-900 border border-red-200'">
        <div class="flex items-center gap-3">
          <CheckCircle v-if="resultado.success" :size="34" />
          <XCircle v-else :size="34" />
          <div>
            <p class="text-2xl font-bold">{{ resultado.success ? 'APROBADO' : 'RECHAZADO' }}</p>
            <p class="font-semibold">{{ resultado.message }}</p>
          </div>
        </div>
      </section>

      <section class="card p-5">
        <div class="mb-3 flex items-center justify-between">
          <h2 class="font-bold text-lg text-slate-800">Avance</h2>
          <span class="font-bold text-slate-800">{{ abordadosList.length }} / {{ total }}</span>
        </div>
        <div class="h-4 overflow-hidden rounded-full bg-slate-100">
          <div class="h-full bg-blue-600 rounded-full transition-all" :style="{ width: `${progreso}%` }"></div>
        </div>
      </section>

      <div class="grid gap-4 md:grid-cols-2">
        <ListaAbordaje titulo="Pendientes" :items="pendientesList" />
        <ListaAbordaje titulo="Abordados" :items="abordadosList" />
      </div>
    </main>
  </section>
</template>

<script setup>
import { CheckCircle, XCircle } from 'lucide-vue-next';
import { computed, defineComponent, h, onMounted, onUnmounted, ref } from 'vue';
import { abordados, pendientes, validarQr } from '../../api/abordaje';
import QrScanner from '../../components/QrScanner.vue';
import { getEcho, leaveChannel } from '../../realtime/echo';
import { useViajeStore } from '../../stores/viaje';

const viajeStore = useViajeStore();
const viajeId = ref(null);
const pendientesList = ref([]);
const abordadosList = ref([]);
const resultado = ref(null);
const channel = ref(null);
const total = computed(() => pendientesList.value.length + abordadosList.value.length);
const progreso = computed(() => (total.value ? Math.round((abordadosList.value.length / total.value) * 100) : 0));

const ListaAbordaje = defineComponent({
  props: { titulo: String, items: Array },
  setup(props) {
    return () =>
      h('section', { class: 'card p-5' }, [
        h('h2', { class: 'mb-3 font-bold text-lg text-slate-800' }, props.titulo),
        h(
          'div',
          { class: 'space-y-2' },
          props.items?.map((boleto) =>
            h('div', { class: 'rounded-xl border border-slate-200 bg-white p-4' }, [
              h('p', { class: 'font-semibold text-slate-800' }, `${boleto.asiento?.numero || '-'} - ${boleto.pasajero?.nombres || ''} ${boleto.pasajero?.apellidos || ''}`),
              h('p', { class: 'text-sm text-slate-500' }, boleto.codigo_boleto)
            ])
          )
        )
      ]);
  }
});

onMounted(async () => {
  await viajeStore.cargarViajes({ fecha: new Date().toISOString().slice(0, 10), estado: 'abordando' });
});

async function cargarListas() {
  if (!viajeId.value) return;
  const [pendientesResp, abordadosResp] = await Promise.all([pendientes(viajeId.value), abordados(viajeId.value)]);
  pendientesList.value = pendientesResp.data || [];
  abordadosList.value = abordadosResp.data || [];
  suscribirAbordaje();
}

function suscribirAbordaje() {
  if (!viajeId.value) return;
  if (channel.value) leaveChannel(channel.value);

  channel.value = `viaje.${viajeId.value}.abordaje`;
  getEcho()
    .private(`viaje.${viajeId.value}.abordaje`)
    .listen('.pasajero.abordado', () => {
      cargarListas();
    });
}

async function validarCodigo(codigo) {
  try {
    resultado.value = await validarQr({ codigo_boleto: codigo });
  } catch (err) {
    resultado.value = err;
  } finally {
    await cargarListas();
  }
}

function hora(value) {
  return value ? new Date(value).toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit', hour12: true }) : '--:--';
}

onUnmounted(() => {
  if (channel.value) leaveChannel(channel.value);
});
</script>

<style scoped>
.card {
  @apply bg-white rounded-xl border border-slate-200;
}

.form-input {
  @apply w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500;
}
</style>
