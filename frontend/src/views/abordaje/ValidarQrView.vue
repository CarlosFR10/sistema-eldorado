<template>
  <section class="mx-auto max-w-2xl space-y-4">
    <QrScanner @decoded="codigo = $event" />
    <button class="btn btn-primary" :disabled="!codigo" @click="validar">
      <ScanLine :size="18" />
      Validar QR
    </button>
    <pre v-if="resultado" class="panel overflow-auto rounded-lg p-4 text-sm">{{ resultado }}</pre>
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
