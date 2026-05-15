<template>
  <div class="panel rounded-lg p-4">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <h3 class="text-base font-black text-slate-800">Croquis del bus</h3>
      <div class="flex flex-wrap gap-2 text-xs font-bold">
        <span class="chip bg-emerald-50 text-emerald-800 border border-emerald-200">Libre</span>
        <span class="chip bg-indigo-400 text-white border border-indigo-600">Adulto</span>
        <span class="chip bg-pink-500 text-white border border-pink-700">Con menor</span>
        <span class="chip bg-amber-500 text-white border border-amber-700">Mayor de edad</span>
        <span class="chip bg-red-500 text-white border border-red-700">Menor</span>
      </div>
    </div>

    <div v-if="!asientos || asientos.length === 0" class="text-center py-8 text-slate-500">
      No hay asientos disponibles para este viaje.
    </div>
    <div v-else class="grid max-w-3xl gap-2" :style="gridStyle">
      <button
        v-for="asiento in asientos"
        :key="asiento.id || asiento.numero"
        class="group relative h-12 rounded-lg border text-sm font-black shadow-sm transition duration-150 disabled:cursor-not-allowed"
        :class="seatClass(asiento)"
        :style="{ gridColumn: asiento.columna, gridRow: asiento.fila + (asiento.piso > 1 ? 14 : 0) }"
        :disabled="asiento.estado !== 'disponible' && !isSelected(asiento)"
        :title="tooltip(asiento)"
        type="button"
        @click="$emit('select', asiento)"
      >
        <span>{{ asiento.numero }}</span>
        <span v-if="selectedLabels[asiento.id]" class="absolute left-1 top-1 rounded bg-teal-800 px-1 text-[10px] leading-4 text-white">
          {{ selectedLabels[asiento.id] }}
        </span>
        <span class="pointer-events-none absolute left-1/2 top-[-8px] z-30 hidden w-64 -translate-x-1/2 -translate-y-full rounded-lg border border-slate-300 bg-white p-3 text-left text-xs shadow-xl group-hover:block">
          <span class="block text-sm font-black text-slate-900">{{ tooltipTitle(asiento) }}</span>
          <span class="mt-1 block font-semibold leading-relaxed text-slate-600">{{ tooltipDetail(asiento) }}</span>
        </span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  asientos: { type: Array, default: () => [] },
  selectedId: { type: [Number, String], default: null },
  selectedIds: { type: Array, default: () => [] },
  selectedLabels: { type: Object, default: () => ({}) },
  selectedKinds: { type: Object, default: () => ({}) }
});

defineEmits(['select']);

const gridStyle = computed(() => ({
  gridTemplateColumns: 'repeat(5, minmax(54px, 1fr))'
}));

function seatClass(asiento) {
  const active = Number(asiento.id) === Number(props.selectedId) ? ' ring-2 ring-teal-700 ring-offset-2' : '';
  const kind = selectedKind(asiento);

  if (kind) return `${kindClass(kind)}${active}`;
  if (asiento.es_menor) return `border-red-600 bg-red-500 text-white hover:bg-red-400${active}`;
  if (asiento.es_adulto_mayor) return `border-amber-700 bg-amber-500 text-white hover:bg-amber-400${active}`;
  if (asiento.menores_acompanados?.length) return `border-pink-700 bg-pink-500 text-white hover:bg-pink-400${active}`;
  if (asiento.pasajero_nombre) return `border-indigo-700 bg-indigo-400 text-white hover:bg-indigo-300${active}`;
  if (asiento.estado === 'disponible') return `border-emerald-500 bg-emerald-100 text-emerald-900 hover:-translate-y-0.5 hover:bg-emerald-200${active}`;

  return `border-slate-200 bg-slate-100 text-slate-500 opacity-70${active}`;
}

function kindClass(kind) {
  const classes = {
    menor: 'border-red-600 bg-red-500 text-white hover:bg-red-400',
    adulto_mayor: 'border-amber-700 bg-amber-500 text-white hover:bg-amber-400',
    con_menor: 'border-pink-700 bg-pink-500 text-white hover:bg-pink-400',
    adulto: 'border-indigo-700 bg-indigo-400 text-white hover:bg-indigo-300'
  };

  return classes[kind] || classes.adulto;
}

function selectedKind(asiento) {
  return props.selectedKinds?.[asiento.id] || null;
}

function isSelected(asiento) {
  return props.selectedIds.some((id) => Number(id) === Number(asiento.id));
}

function tooltipTitle(asiento) {
  if (selectedKind(asiento)) return `Asiento ${asiento.numero} seleccionado`;
  if (asiento.pasajero_nombre) return `Asiento ${asiento.numero} asignado`;
  if (asiento.estado === 'disponible') return `Asiento ${asiento.numero} disponible`;
  return `Asiento ${asiento.numero} no disponible`;
}

function tooltipDetail(asiento) {
  const kind = selectedKind(asiento);
  if (kind === 'menor') return 'Seleccionado para menor de edad. Debe tener adulto responsable vinculado.';
  if (kind === 'adulto_mayor') return 'Seleccionado para adulto mayor (60+ anos). Aplica trato preferente.';
  if (kind === 'con_menor') return 'Seleccionado para adulto responsable que viaja con menor.';
  if (kind === 'adulto') return 'Seleccionado para pasajero adulto.';

  if (asiento.es_menor) {
    return `Menor: ${asiento.pasajero_nombre || 'registrado'}. Adulto responsable: ${asiento.adulto_responsable_nombre || 'registrado'}.`;
  }

  if (asiento.es_adulto_mayor) return `Adulto mayor: ${asiento.pasajero_nombre || 'registrado'}.`;

  if (asiento.menores_acompanados?.length) {
    return `${asiento.pasajero_nombre || 'Adulto'} viaja con menor: ${asiento.menores_acompanados.join(', ')}.`;
  }

  if (asiento.pasajero_nombre) return `${asiento.pasajero_nombre} | CI ${asiento.pasajero_ci || '-'}.`;
  if (asiento.estado !== 'disponible') return 'Este asiento ya no puede elegirse para esta venta.';
  return asiento.tipo === 'preferencial' ? 'Disponible. Puede elegirse normalmente.' : 'Libre para seleccionar.';
}

function tooltip(asiento) {
  return `${tooltipTitle(asiento)} - ${tooltipDetail(asiento)}`;
}
</script>
