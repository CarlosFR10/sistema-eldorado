<template>
  <span class="chip" :class="classes">{{ label }}</span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  estado: { type: String, required: true }
});

const map = {
  disponible: ['Disponible', 'bg-emerald-100 text-emerald-800'],
  bloqueado: ['Bloqueado', 'bg-amber-100 text-amber-800'],
  reservado: ['Reservado', 'bg-red-100 text-red-800'],
  ocupado: ['Ocupado', 'bg-red-100 text-red-800'],
  en_venta: ['En venta', 'bg-emerald-100 text-emerald-800'],
  abordando: ['Abordando', 'bg-sky-100 text-sky-800'],
  en_ruta: ['En ruta', 'bg-indigo-100 text-indigo-800'],
  completado: ['Completado', 'bg-slate-100 text-slate-700'],
  cancelado: ['Cancelado', 'bg-red-100 text-red-800']
};

const label = computed(() => map[props.estado]?.[0] || props.estado);
const classes = computed(() => map[props.estado]?.[1] || 'bg-slate-100 text-slate-700');
</script>
