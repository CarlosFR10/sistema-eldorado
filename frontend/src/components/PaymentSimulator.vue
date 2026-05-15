<template>
  <section v-if="method !== 'efectivo'" class="rounded-lg border border-teal-100 bg-white p-4 shadow-sm">
    <div class="mb-3 flex items-center justify-between gap-3">
      <div>
        <p class="text-xs font-black uppercase text-eldorado-teal">Pago simulado</p>
        <h3 class="flex items-center gap-2 font-black text-slate-900">
          <CreditCard v-if="method === 'tarjeta'" :size="18" />
          <QrCode v-else :size="18" />
          {{ method === 'tarjeta' ? 'Pago con tarjeta' : 'Pago con QR' }}
        </h3>
      </div>
      <span class="chip" :class="approved ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'">
        {{ approved ? 'Pago confirmado' : `Bs ${amountLabel}` }}
      </span>
    </div>

    <div v-if="method === 'tarjeta'" class="grid gap-3">
      <input v-model.trim="card.name" class="field" placeholder="Nombre del titular" />
      <input v-model.trim="card.number" class="field" inputmode="numeric" maxlength="19" placeholder="Numero de tarjeta" />
      <div class="grid grid-cols-2 gap-2">
        <input v-model.trim="card.expiry" class="field" maxlength="5" placeholder="MM/AA" />
        <input v-model.trim="card.cvv" class="field" inputmode="numeric" maxlength="4" placeholder="CVV" />
      </div>
      <p class="rounded-md bg-slate-50 p-3 text-sm font-bold text-slate-700">
        Ingresa los datos de la tarjeta. El sistema validara el pago antes de emitir el boleto.
      </p>
      <button class="btn btn-primary w-full" type="button" :disabled="processing || !canProcessCard || approved" @click="processCard">
        <LoaderCircle v-if="processing" class="animate-spin" :size="18" />
        <ShieldCheck v-else :size="18" />
        {{ processing ? 'Procesando pago...' : approved ? 'Transaccion realizada' : 'Procesar pago' }}
      </button>
    </div>

    <div v-else class="grid gap-3">
      <div class="flex flex-wrap items-center justify-between gap-2 rounded-md bg-amber-50 p-3 text-sm font-black text-amber-900">
        <span>Tiempo para pagar</span>
        <span class="rounded-md bg-white px-2 py-1 font-mono">{{ countdownLabel }}</span>
      </div>
      <div class="rounded-xl border border-slate-200 bg-[#f8fbff] p-3 shadow-sm">
        <div class="mb-3 flex items-center justify-between gap-3">
          <div>
            <p class="text-xs font-black uppercase text-slate-500">Banco movil simulado</p>
            <p class="font-black text-slate-950">Terminal El Dorado</p>
          </div>
          <span class="rounded-md bg-teal-700 px-2 py-1 text-xs font-black text-white">QR SIMPLE</span>
        </div>

        <div class="mx-auto w-56 rounded-xl border border-slate-200 bg-white p-3 shadow-inner">
          <div class="grid gap-px" :style="{ gridTemplateColumns: `repeat(${qrSize}, minmax(0, 1fr))` }">
            <span
              v-for="(filled, index) in qrCells"
              :key="index"
              class="aspect-square rounded-[1px]"
              :class="filled ? 'bg-slate-950' : 'bg-white'"
            ></span>
          </div>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2 text-xs font-bold text-slate-600">
          <p class="rounded-md bg-white p-2">Monto<br /><span class="text-base font-black text-teal-800">Bs {{ amountLabel }}</span></p>
          <p class="rounded-md bg-white p-2">Codigo<br /><span class="font-mono text-slate-950">{{ qrPaymentCode }}</span></p>
        </div>
      </div>
      <p class="rounded-md bg-slate-50 p-3 text-center text-sm font-bold text-slate-700">
        Escanea el QR desde banca movil. Luego pulsa verificar para simular la confirmacion bancaria.
      </p>
      <button class="btn btn-primary w-full" type="button" :disabled="processing || approved" @click="verifyQr">
        <LoaderCircle v-if="processing" class="animate-spin" :size="18" />
        <Smartphone v-else :size="18" />
        {{ processing ? 'Verificando si ya pago...' : approved ? 'Compra realizada' : 'Verificar si ya pago' }}
      </button>
    </div>

    <div v-if="approved" class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm">
      <p class="flex items-center gap-2 font-black text-emerald-900">
        <Receipt :size="18" />
        Recibo emitido
      </p>
      <p class="mt-1 font-mono text-emerald-950">{{ receiptCode }}</p>
      <p class="text-emerald-800">Transaccion realizada. Se emitira el boleto automaticamente.</p>
    </div>
  </section>
</template>

<script setup>
import { CreditCard, LoaderCircle, QrCode, Receipt, ShieldCheck, Smartphone } from 'lucide-vue-next';
import { computed, onUnmounted, reactive, ref, watch } from 'vue';

const props = defineProps({
  method: { type: String, default: 'efectivo' },
  amount: { type: [Number, String], default: 0 }
});

const emit = defineEmits(['paid-change']);

const processing = ref(false);
const approved = ref(false);
const receiptCode = ref('');
const remainingSeconds = ref(600);
const card = reactive({ name: '', number: '', expiry: '', cvv: '' });
let timerId = null;
const qrSize = 29;

const amountNumber = computed(() => Number(props.amount || 0));
const amountLabel = computed(() => amountNumber.value.toFixed(2));
const qrPaymentCode = computed(() => `ELD-${String(Math.round(amountNumber.value * 100)).padStart(6, '0')}`);
const countdownLabel = computed(() => {
  const minutes = Math.floor(remainingSeconds.value / 60);
  const seconds = remainingSeconds.value % 60;
  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
});
const canProcessCard = computed(() => card.name.length >= 3 && onlyDigits(card.number).length >= 12 && card.expiry.length >= 4 && onlyDigits(card.cvv).length >= 3);
const qrCells = computed(() => Array.from({ length: qrSize * qrSize }, (_, index) => {
  const row = Math.floor(index / qrSize);
  const col = index % qrSize;

  return qrModule(row, col);
}));

watch(() => [props.method, props.amount], () => resetPayment(), { immediate: true });

function resetPayment() {
  processing.value = false;
  approved.value = false;
  receiptCode.value = '';
  remainingSeconds.value = 600;
  stopTimer();
  if (props.method === 'qr_bancario') startTimer();
  emit('paid-change', props.method === 'efectivo');
}

function processCard() {
  if (!canProcessCard.value || processing.value || approved.value) return;
  finishAfterDelay('TAR');
}

function verifyQr() {
  if (processing.value || approved.value) return;
  finishAfterDelay('QR');
}

function finishAfterDelay(prefix) {
  processing.value = true;
  window.setTimeout(() => {
    processing.value = false;
    approved.value = true;
    stopTimer();
    receiptCode.value = `${prefix}-${Date.now().toString().slice(-8)}-ELD`;
    emit('paid-change', true);
  }, 1300);
}

function startTimer() {
  timerId = window.setInterval(() => {
    remainingSeconds.value = Math.max(remainingSeconds.value - 1, 0);
    if (remainingSeconds.value === 0) stopTimer();
  }, 1000);
}

function stopTimer() {
  if (!timerId) return;
  window.clearInterval(timerId);
  timerId = null;
}

function onlyDigits(value) {
  return String(value || '').replace(/\D/g, '');
}

function qrModule(row, col) {
  const seed = Math.round(amountNumber.value * 100) + 2026;
  const finder = finderModule(row, col, 0, 0)
    ?? finderModule(row, col, 0, qrSize - 7)
    ?? finderModule(row, col, qrSize - 7, 0);

  if (finder !== null) return finder;
  if (row === 6 || col === 6) return (row + col) % 2 === 0;
  if (row > 20 && col > 20 && row < 26 && col < 26) return row === 22 || col === 22 || row === 24 || col === 24;

  return ((row * 31 + col * 17 + seed) % 9) < 4 || ((row ^ col ^ seed) % 17) === 0;
}

function finderModule(row, col, startRow, startCol) {
  const localRow = row - startRow;
  const localCol = col - startCol;

  if (localRow < 0 || localCol < 0 || localRow > 6 || localCol > 6) return null;

  return localRow === 0
    || localRow === 6
    || localCol === 0
    || localCol === 6
    || (localRow >= 2 && localRow <= 4 && localCol >= 2 && localCol <= 4);
}

onUnmounted(stopTimer);
</script>
