import api from './axios';

export const login = (payload) => api.post('/auth/login', payload);
export const verify2fa = (payload) => api.post('/auth/verify-2fa', payload);
export const logout = () => api.post('/auth/logout');
export const me = () => api.get('/auth/me');
