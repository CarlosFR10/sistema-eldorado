<template>
  <div class="h-[620px] overflow-hidden rounded-lg border border-slate-200">
    <LMap ref="map" :zoom="zoom" :center="center" :use-global-leaflet="false" @ready="onMapReady">
      <LTileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" attribution="OpenStreetMap" />

      <LMarker
        v-for="(wp, i) in waypoints"
        :key="'wp-' + i"
        :lat-lng="[wp.lat, wp.lng]"
        :icon="getWaypointIcon(i)"
      />

      <LMarker
        v-if="busPosition"
        ref="busMarkerRef"
        :lat-lng="[busPosition.lat, busPosition.lng]"
        :icon="busIcon"
      />

      <LPolyline
        v-if="routePoints.length"
        :lat-lngs="routePoints"
        :color="signalLoss ? '#94a3b8' : '#0f766e'"
        :weight="4"
        :opacity="0.8"
        :dash-array="signalLoss ? '15, 10' : undefined"
      />
    </LMap>
  </div>
</template>

<script setup>
import { LIcon, LMap, LMarker, LPopup, LPolyline, LTileLayer } from '@vue-leaflet/vue-leaflet';
import L from 'leaflet';
import { computed, ref } from 'vue';

const props = defineProps({
  waypoints: { type: Array, default: () => [] },
  busPosition: { type: Object, default: null },
  signalLoss: { type: Boolean, default: false },
  progreso: { type: Number, default: 0 },
  velocidad: { type: Number, default: 0 },
});

const map = ref(null);
const busMarkerRef = ref(null);

const zoom = computed(() => {
  if (props.waypoints.length > 2) return 7;
  return 8;
});

const center = computed(() => {
  if (props.waypoints.length >= 2) {
    const first = props.waypoints[0];
    const last = props.waypoints[props.waypoints.length - 1];
    return [(first.lat + last.lat) / 2, (first.lng + last.lng) / 2];
  }
  return [-17.3895, -66.1568];
});

const routePoints = computed(() =>
  props.waypoints.map((w) => [w.lat, w.lng])
);

const busIcon = computed(() => {
  const color = props.signalLoss ? '#94A3B8' : '#0F766E';
  const bgBadge = props.signalLoss ? '#EF4444' : '#0F766E';
  const badgeText = props.signalLoss ? 'Sin seal' : `${Math.round(props.progreso || 0)}%`;

  const html = `<div style="display:flex;flex-direction:column;align-items:center;">
    <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
      <rect x="2" y="8" width="40" height="28" rx="6" fill="${color}" stroke="#0F766E" stroke-width="2"/>
      <rect x="6" y="12" width="8" height="8" rx="2" fill="white" opacity="0.9"/>
      <rect x="18" y="12" width="8" height="8" rx="2" fill="white" opacity="0.9"/>
      <rect x="30" y="12" width="8" height="8" rx="2" fill="white" opacity="0.9"/>
      <rect x="0" y="32" width="44" height="6" rx="3" fill="#6B7280"/>
      <circle cx="12" cy="38" r="4" fill="#1F2937"/>
      <circle cx="12" cy="38" r="1.5" fill="#9CA3AF"/>
      <circle cx="32" cy="38" r="4" fill="#1F2937"/>
      <circle cx="32" cy="38" r="1.5" fill="#9CA3AF"/>
      <text x="22" y="26" text-anchor="middle" fill="white" font-size="10" font-weight="bold" font-family="Arial">${Math.round(props.progreso || 0)}%</text>
    </svg>
    <span style="margin-top:2px;font-size:10px;font-weight:700;background:${bgBadge};color:white;padding:2px 6px;border-radius:10px;white-space:nowrap;box-shadow:0 1px 3px rgba(0,0,0,0.3);">${badgeText}</span>
  </div>`;

  return L.divIcon({
    html,
    className: 'bus-icon',
    iconSize: [50, 58],
    iconAnchor: [25, 28],
  });
});

function getWaypointIcon(index) {
  const isStart = index === 0;
  const isEnd = index === props.waypoints.length - 1;

  let svg;
  if (isStart) {
    svg = `<svg width="32" height="40" viewBox="0 0 32 40" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M16 0L32 10V30L16 40L0 30V10L16 0Z" fill="#10B981" stroke="#059669" stroke-width="2"/>
      <circle cx="16" cy="18" r="7" fill="white"/>
      <path d="M16 11V25M11 18H21" stroke="#10B981" stroke-width="2.5" stroke-linecap="round"/>
    </svg>`;
  } else if (isEnd) {
    svg = `<svg width="32" height="40" viewBox="0 0 32 40" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M16 0L32 10V30L16 40L0 30V10L16 0Z" fill="#EF4444" stroke="#DC2626" stroke-width="2"/>
      <rect x="8" y="12" width="16" height="16" rx="2" fill="white"/>
      <path d="M12 16H20M12 20H20M12 24H20" stroke="#EF4444" stroke-width="1.5"/>
    </svg>`;
  } else {
    svg = `<svg width="28" height="36" viewBox="0 0 28 36" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="14" cy="14" r="12" fill="#94A3B8" stroke="#64748B" stroke-width="2"/>
      <circle cx="14" cy="14" r="5" fill="white"/>
    </svg>`;
  }

  const wpName = props.waypoints[index]?.nombre || '';
  const html = `<div style="display:flex;flex-direction:column;align-items:center;">
    ${svg}
    <span style="margin-top:4px;font-size:11px;font-weight:700;background:rgba(255,255,255,0.95);padding:2px 6px;border-radius:4px;color:#334155;white-space:nowrap;box-shadow:0 1px 3px rgba(0,0,0,0.15);">${wpName}</span>
  </div>`;

  return L.divIcon({
    html,
    className: 'waypoint-icon',
    iconSize: [40, 50],
    iconAnchor: [20, 38],
  });
}

function onMapReady() {
}
</script>

<style>
.waypoint-icon, .bus-icon {
  background: transparent !important;
  border: none !important;
}
</style>