import { defineStore } from 'pinia';
import { asientosViaje, bloquearAsiento, liberarAsiento } from '../api/boletos';
import { getEcho, leaveChannel } from '../realtime/echo';

export const useAsientosStore = defineStore('asientos', {
  state: () => ({
    asientos: [],
    seleccionado: null,
    bloqueoHasta: null,
    loading: false,
    channel: null
  }),
  getters: {
    disponibles: (state) => state.asientos.filter((asiento) => asiento.estado === 'disponible')
  },
  actions: {
    async cargar(viajeId) {
      this.loading = true;
      try {
        const response = await asientosViaje(viajeId);
        this.asientos = response.data || [];
      } finally {
        this.loading = false;
      }
    },
    async bloquear(viajeId, numeroAsiento) {
      const response = await bloquearAsiento({ viaje_id: viajeId, numero_asiento: numeroAsiento });
      this.seleccionado = response.data;
      this.bloqueoHasta = response.data?.bloqueado_hasta;
      await this.cargar(viajeId);
      return response.data;
    },
    async liberar(viajeId, numeroAsiento) {
      const response = await liberarAsiento({ viaje_id: viajeId, numero_asiento: numeroAsiento });
      this.seleccionado = null;
      this.bloqueoHasta = null;
      await this.cargar(viajeId);
      return response.data;
    },
    suscribir(viajeId) {
      if (!viajeId) return;
      if (this.channel) leaveChannel(this.channel);

      this.channel = `viaje.${viajeId}.asientos`;
      getEcho()
        .private(`viaje.${viajeId}.asientos`)
        .listen('.asiento.actualizado', (event) => {
          const index = this.asientos.findIndex((asiento) => Number(asiento.numero) === Number(event.numero_asiento));
          if (index >= 0) {
            this.asientos[index] = {
              ...this.asientos[index],
              estado: event.estado,
              bloqueado_hasta: event.bloqueado_hasta
            };
          }
        });
    }
  }
});
