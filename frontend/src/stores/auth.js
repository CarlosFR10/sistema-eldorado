import { defineStore } from 'pinia';
import * as authApi from '../api/auth';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('eldorado_token'),
    user: JSON.parse(localStorage.getItem('eldorado_user') || 'null'),
    pending2fa: null,
    loading: false,
    error: null
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.token),
    role: (state) => state.user?.rol || null,
    nombre: (state) => state.user?.nombre || 'Operador',
    displayName: (state) => state.user?.nombre || 'Operador',
    roleLabel: (state) => {
      const labels = {
        administrador: 'Administrador',
        supervisor: 'Supervisor',
        vendedor: 'Vendedor',
        auxiliar: 'Auxiliar'
      };
      return labels[state.user?.rol] || state.user?.rol || 'Usuario';
    }
  },
  actions: {
    async login(credentials) {
      this.loading = true;
      this.error = null;

      try {
        const response = await authApi.login(credentials);

        if (response.data?.requires_2fa) {
          this.pending2fa = response.data;
          return response.data;
        }

        this.setSession(response.data);
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async verify2fa(token2fa) {
      const response = await authApi.verify2fa({
        email: this.pending2fa?.email,
        token_2fa: token2fa
      });
      this.setSession(response.data);
      this.pending2fa = null;
      return response.data;
    },
    setSession(data) {
      this.token = data.token;
      this.user = data.usuario;
      localStorage.setItem('eldorado_token', data.token);
      localStorage.setItem('eldorado_user', JSON.stringify(data.usuario));
    },
    async logout() {
      try {
        await authApi.logout();
      } finally {
        this.token = null;
        this.user = null;
        this.pending2fa = null;
        localStorage.removeItem('eldorado_token');
        localStorage.removeItem('eldorado_user');
      }
    }
  }
});
