<template>
  <section class="grid gap-4 lg:grid-cols-[380px_1fr]">
    <aside class="space-y-4">
      <section class="panel rounded-lg p-4">
        <h1 class="mb-3 text-xl font-black text-slate-900">Viaje de abordaje</h1>
        <select v-model="viajeId" class="field" @change="cargarListas">
          <option :value="null">Seleccionar viaje</option>
          <option v-for="viaje in viajeStore.viajes" :key="viaje.id" :value="viaje.id">
            {{ viaje.ruta?.codigo }} - {{ hora(viaje.fecha_salida) }}
          </option>
        </select>
      </section>

      <QrScanner @decoded="validarCodigo" />
    </aside>

    <main class="space-y-4">
      <section v-if="resultado" class="rounded-lg p-5" :class="resultado.success ? 'bg-emerald-100 text-emerald-900' : 'bg-red-100 text-red-900'">
        <div class="flex items-center gap-3">
          <CheckCircle v-if="resultado.success" :size="34" />
          <XCircle v-else :size="34" />
          <div>
            <p class="text-2xl font-black">{{ resultado.success ? 'APROBADO' : 'RECHAZADO' }}</p>
            <p class="font-bold">{{ resultado.message }}</p>
          </div>
        </div>
      </section>

      <section class="panel rounded-lg p-4">
        <div class="mb-3 flex items-center justify-between">
          <h2 class="font-black text-slate-800">Avance</h2>
          <span class="font-black">{{ abordadosList.length }} / {{ total }}</span>
        </div>
        <div class="h-4 overflow-hidden rounded-full bg-slate-100">
          <div class="h-full bg-eldorado-teal" :style="{ width: `${progreso}%` }"></div>
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
      h('section', { class: 'panel rounded-lg p-4' }, [
        h('h2', { class: 'mb-3 font-black text-slate-800' }, props.titulo),
        h(
          'div',
          { class: 'space-y-2' },
          props.items?.map((boleto) =>
            h('div', { class: 'rounded-md border border-slate-200 bg-white p-3' }, [
              h('p', { class: 'font-black' }, `${boleto.asiento?.numero || '-'} - ${boleto.pasajero?.nombres || ''} ${boleto.pasajero?.apellidos || ''}`),
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
