import api from './axios';

export const listarViajes = (params = {}) => api.get('/viajes', { params });
export const crearViaje = (payload) => api.post('/viajes', payload);
export const obtenerViaje = (id) => api.get(`/viajes/${id}`);
export const cambiarEstadoViaje = (id, payload) => api.put(`/viajes/${id}/estado`, payload);
export const viajesDelDia = () => api.post('/viajes/del-dia');
export const horasDisponibles = (params) => api.get('/viajes/horas-disponibles', { params });
export const listarRutas = () => api.get('/rutas');
export const listarBuses = () => api.get('/buses');
export const listarConductores = () => api.get('/conductores');
export const manifiestoViaje = (id) => api.get(`/viajes/${id}/manifiesto`);
