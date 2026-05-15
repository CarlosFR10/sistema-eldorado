import api from './axios';

export const publicRutas = () => api.get('/public/rutas');
export const publicViajes = (params = {}) => api.get('/public/viajes', { params });
export const publicAsientos = (viajeId) => api.get(`/public/viajes/${viajeId}/asientos`);
export const publicBuscarPasajero = (ci) => api.get('/public/pasajeros/buscar', { params: { ci } });
export const publicPreRegistrarPasajero = (payload) => api.post('/public/pasajeros/pre-registro', payload);
export const publicReservarBoletos = (payload) => api.post('/public/boletos/reservar', payload);
export const publicRastrear = (codigo) => api.get(`/public/rastreo/${encodeURIComponent(codigo)}`);
