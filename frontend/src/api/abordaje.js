import api from './axios';

export const validarQr = (payload) => api.post('/abordaje/validar-qr', payload);
export const validarHuella = (payload) => api.post('/abordaje/validar-huella', payload);
export const validarQrHuella = (payload) => api.post('/abordaje/validar-qr-huella', payload);
export const pendientes = (viajeId) => api.get(`/abordaje/viaje/${viajeId}/pendientes`);
export const abordados = (viajeId) => api.get(`/abordaje/viaje/${viajeId}/abordados`);
export const eventos = (viajeId) => api.get(`/abordaje/eventos/${viajeId}`);
