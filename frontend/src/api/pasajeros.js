import api from './axios';

export const listarPasajeros = (params = {}) => api.get('/pasajeros', { params });
export const buscarPasajero = (ci) => api.get('/pasajeros/buscar', { params: { ci } });
export const crearPasajero = (payload) => api.post('/pasajeros', payload);
export const actualizarPasajero = (id, payload) => api.put(`/pasajeros/${id}`, payload);
export const registrarHuella = (id, payload) => api.post(`/pasajeros/${id}/huella`, payload);
export const vincularAdulto = (id, payload) => api.post(`/pasajeros/${id}/menor/vincular`, payload);
