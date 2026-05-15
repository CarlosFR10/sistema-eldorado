import { defineStore } from 'pinia';
import { busesActivos, simularGps } from '../api/gps';
import { getEcho } from '../realtime/echo';

export const useGpsStore = defineStore('gps', {
  state: () => ({
    buses: [],
    loading: false,
    lastUpdated: null,
    subscribed: false,
    alertas: []
  }),
  actions: {
    async cargar() {
      this.loading = true;
      try {
        const response = await busesActivos();
        this.buses = response.data || [];
        this.lastUpdated = new Date();
      } finally {
        this.loading = false;
      }
    },
    async simular(payload) {
      const response = await simularGps(payload);
      await this.cargar();
      return response.data;
    },
    suscribir() {
      if (this.subscribed) return;
      this.subscribed = true;

      getEcho()
        .private('gps.buses')
        .listen('.bus.ubicacion_actualizada', (event) => {
          const index = this.buses.findIndex((item) => Number(item.bus?.id) === Number(event.bus_id));
          if (index >= 0) {
            this.buses[index] = {
              ...this.buses[index],
              ubicacion: {
                ...(this.buses[index].ubicacion || {}),
                latitud: event.latitud,
                longitud: event.longitud,
                velocidad: event.velocidad,
                rumbo: event.rumbo,
                timestamp: event.timestamp
              }
            };
          }
          this.lastUpdated = new Date();
        });

      getEcho()
        .private('alertas.supervisores')
        .listen('.alerta.velocidad_excesiva', (event) => {
          this.alertas.unshift(event);
          this.alertas = this.alertas.slice(0, 10);
        });
    }
  }
});
