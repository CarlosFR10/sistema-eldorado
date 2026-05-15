import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  timeout: 15000,
  headers: {
    Accept: 'application/json'
  }
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('eldorado_token');

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

api.interceptors.response.use(
  (response) => response.data,
  (error) => {
    const payload = error.response?.data || {
      success: false,
      message: 'No se pudo conectar con el servidor.',
      errors: {},
      code: 'NETWORK_ERROR'
    };

    if (error.response?.status === 401) {
      localStorage.removeItem('eldorado_token');
      localStorage.removeItem('eldorado_user');
    }

    return Promise.reject(payload);
  }
);

export default api;
