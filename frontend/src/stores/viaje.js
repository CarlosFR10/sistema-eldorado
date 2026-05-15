import { defineStore } from 'pinia';
import { listarBuses, listarConductores, listarRutas, listarViajes, manifiestoViaje } from '../api/viajes';

export const useViajeStore = defineStore('viaje', {
  state: () => ({
    viajes: [],
    rutas: [],
    buses: [],
    conductores: [],
    selectedViaje: null,
    loading: false
  }),
  actions: {
    async cargarCatalogos() {
      const [rutas, buses, conductores] = await Promise.all([listarRutas(), listarBuses(), listarConductores()]);
      this.rutas = rutas.data || [];
      this.buses = buses.data || [];
      this.conductores = conductores.data || [];
    },
    async cargarViajes(params = {}) {
      this.loading = true;
      try {
        const response = await listarViajes(params);
        this.viajes = response.data?.data || response.data || [];
        return this.viajes;
      } finally {
        this.loading = false;
      }
    },
    async cargarManifiesto(id) {
      const response = await manifiestoViaje(id);
      this.selectedViaje = response.data;
      return response.data;
    }
  }
});
