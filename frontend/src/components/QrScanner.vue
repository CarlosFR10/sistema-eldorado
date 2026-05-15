<template>
  <div class="panel rounded-lg p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <h3 class="font-black text-slate-800">Lector QR</h3>
      <div class="flex gap-2">
        <button class="btn btn-secondary" type="button" @click="start">
          <Camera :size="18" />
          Activar
        </button>
        <button class="btn btn-secondary" type="button" @click="stop">
          <Square :size="18" />
          Detener
        </button>
      </div>
    </div>
    <div :id="elementId" class="mt-3 min-h-64 rounded-lg border border-slate-200 bg-slate-950"></div>
    <input v-model="manual" class="field mt-3" placeholder="Codigo manual" @keyup.enter="emitManual" />
  </div>
</template>

<script setup>
import { Camera, Square } from 'lucide-vue-next';
import { Html5Qrcode } from 'html5-qrcode';
import { onBeforeUnmount, ref } from 'vue';

const emit = defineEmits(['decoded']);
const elementId = `qr-${Math.random().toString(16).slice(2)}`;
const scanner = ref(null);
const manual = ref('');

async function start() {
  if (scanner.value) return;
  scanner.value = new Html5Qrcode(elementId);
  await scanner.value.start(
    { facingMode: 'environment' },
    { fps: 10, qrbox: 240 },
    (text) => emit('decoded', text)
  );
}

async function stop() {
  if (!scanner.value) return;
  await scanner.value.stop();
  scanner.value.clear();
  scanner.value = null;
}

function emitManual() {
  if (manual.value.trim()) emit('decoded', manual.value.trim());
}

onBeforeUnmount(stop);
</script>
