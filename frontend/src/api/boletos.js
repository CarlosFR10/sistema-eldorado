import api from './axios';

export const emitirBoleto = (payload) => api.post('/boletos', payload);
export const obtenerBoleto = (id) => api.get(`/boletos/${id}`);
export const obtenerBoletoPorCodigo = (codigo) => api.get(`/boletos/${codigo}`);
export const cancelarBoleto = (id) => api.put(`/boletos/${id}/cancelar`);
export const bloquearAsiento = (payload) => api.post('/asientos/bloquear', payload);
export const liberarAsiento = (payload) => api.post('/asientos/liberar', payload);
export const asientosViaje = (viajeId) => api.get(`/viajes/${viajeId}/asientos`);
export const asientosDisponibles = (viajeId) => api.get(`/viajes/${viajeId}/asientos/disponibles`);
