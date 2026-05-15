<template>
  <div class="panel rounded-lg p-4">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h3 class="font-black text-slate-800">Huella dactilar</h3>
        <p class="text-sm text-slate-500">{{ estado }}</p>
      </div>
      <button class="btn btn-secondary" type="button" :disabled="escaneando" @click="capturar">
        <Fingerprint :size="18" />
        {{ escaneando ? 'Escaneando...' : 'Capturar' }}
      </button>
    </div>
    <div
      class="mt-3 h-20 rounded-lg border p-3 font-mono text-xs text-slate-600"
      :class="escaneando ? 'border-teal-300 bg-teal-50' : 'border-slate-200 bg-slate-50'"
    >
      {{ plantilla || '---- ---- ---- ----' }}
    </div>
  </div>
</template>

<script setup>
import { Fingerprint } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const emit = defineEmits(['captured']);
const plantilla = ref('');
const escaneando = ref(false);
const estado = computed(() => {
  if (escaneando.value) return 'Escaneando huella simulada...';
  return plantilla.value ? 'Captura lista' : 'Sensor en espera';
});

function capturar() {
  escaneando.value = true;
  plantilla.value = 'Leyendo puntos biometricos...';

  window.setTimeout(() => {
    plantilla.value = `SIM-${Date.now()}-${Math.random().toString(16).slice(2, 10)}`;
    escaneando.value = false;
    emit('captured', {
      plantilla: plantilla.value,
      dedo: 'indice_der',
      calidad: 92
    });
  }, 800);
}
</script>
