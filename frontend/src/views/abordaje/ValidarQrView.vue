<template>
  <section class="mx-auto max-w-2xl space-y-6">
    <QrScanner @decoded="codigo = $event" />
    <button class="btn-primary" :disabled="!codigo" @click="validar">
      <ScanLine :size="18" />
      Validar QR
    </button>
    <div v-if="resultado" class="card p-5 overflow-auto">
      <pre class="text-sm text-slate-800 whitespace-pre-wrap">{{ resultado }}</pre>
    </div>
  </section>
</template>

<script setup>
import { ScanLine } from 'lucide-vue-next';
import { ref } from 'vue';
import { validarQr } from '../../api/abordaje';
import QrScanner from '../../components/QrScanner.vue';

const codigo = ref('');
const resultado = ref(null);

async function validar() {
  try {
    resultado.value = await validarQr({ codigo_boleto: codigo.value });
  } catch (err) {
    resultado.value = err;
  }
}
</script>

<style scoped>
.card {
  @apply bg-white rounded-xl border border-slate-200;
}

.btn-primary {
  @apply flex items-center justify-center gap-2 py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors;
}
</style>
