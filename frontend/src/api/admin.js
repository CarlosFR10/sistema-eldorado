import api from './axios';

export const listarUsuarios = (params = {}) => api.get('/usuarios', { params });
export const crearUsuario = (payload) => api.post('/usuarios', payload);
export const actualizarUsuario = (id, payload) => api.put(`/usuarios/${id}`, payload);
export const eliminarUsuario = (id) => api.delete(`/usuarios/${id}`);
export const ventasDiarias = (params = {}) => api.get('/reportes/ventas-diarias', { params });
export const ocupacionPorRuta = (params = {}) => api.get('/reportes/ocupacion-por-ruta', { params });
export const abordajesPorViaje = (params = {}) => api.get('/reportes/abordajes-por-viaje', { params });
export const auditoria = (params = {}) => api.get('/reportes/auditoria', { params });
export const ingresos = (params = {}) => api.get('/reportes/ingresos', { params });
