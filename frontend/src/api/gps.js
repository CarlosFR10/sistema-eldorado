import api from './axios';

export const busesActivos = () => api.get('/gps/buses/activos');
export const rutaBus = (id) => api.get(`/gps/bus/${id}/ruta`);
export const timelineViaje = (id) => api.get(`/gps/viaje/${id}/timeline`);
export const simularGps = (payload) => api.post('/gps/simular', payload);
export const iniciarViajeGps = (viajeId) => api.post(`/gps/viaje/${viajeId}/iniciar`);
export const avanzarSimulacionGps = (viajeId) => api.post(`/gps/viaje/${viajeId}/avanzar`);
export const estadoSimulacionGps = (viajeId) => api.get(`/gps/viaje/${viajeId}/estado`);
