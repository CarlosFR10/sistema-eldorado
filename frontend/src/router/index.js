import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
  // Public routes with PublicLayout
  {
    path: '/',
    component: () => import('../layouts/PublicLayout.vue'),
    children: [
      {
        path: '',
        name: 'inicio-publico',
        component: () => import('../views/public/HomePublicaView.vue')
      },
      {
        path: 'boleteria',
        name: 'boleteria',
        component: () => import('../views/public/BoleteriaPublicaView.vue')
      },
      {
        path: 'registro',
        name: 'registro-publico',
        component: () => import('../views/public/RegistroPublicoView.vue')
      },
      {
        path: 'rastrear',
        name: 'rastrear-bus',
        component: () => import('../views/public/RastrearBusView.vue')
      }
    ]
  },

  // Auth - no layout
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/auth/LoginView.vue')
  },

  // Consulta autoridad - no auth needed, public
  {
    path: '/consulta',
    name: 'consulta-autoridad',
    component: () => import('../views/autoridad/ConsultaViajeView.vue')
  },

  // Protected routes with AdminOneLayout
  {
    path: '/',
    component: () => import('../layouts/AdminOneLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      // Admin routes
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('../views/admin/DashboardView.vue'),
        meta: { roles: ['administrador', 'supervisor'] }
      },
      {
        path: 'admin/usuarios',
        name: 'usuarios',
        component: () => import('../views/admin/UsuariosView.vue'),
        meta: { roles: ['administrador'] }
      },
      {
        path: 'admin/buses',
        name: 'buses',
        component: () => import('../views/admin/BusesView.vue'),
        meta: { roles: ['administrador', 'supervisor'] }
      },
      {
        path: 'admin/rutas',
        name: 'rutas',
        component: () => import('../views/admin/RutasView.vue'),
        meta: { roles: ['administrador', 'supervisor'] }
      },
      {
        path: 'admin/reportes',
        name: 'reportes',
        component: () => import('../views/admin/ReportesView.vue'),
        meta: { roles: ['administrador', 'supervisor'] }
      },

      // Vendedor routes
      {
        path: 'venta',
        name: 'venta',
        component: () => import('../views/vendedor/VentaBoletoView.vue'),
        meta: { roles: ['vendedor', 'supervisor', 'administrador'] }
      },
      {
        path: 'venta/pasajeros',
        name: 'registro-pasajero',
        component: () => import('../views/vendedor/RegistroPasajeroView.vue'),
        meta: { roles: ['vendedor', 'supervisor', 'administrador'] }
      },
      {
        path: 'venta/viajes',
        name: 'viajes-activos',
        component: () => import('../views/vendedor/ViajesActivosView.vue'),
        meta: { roles: ['vendedor', 'supervisor', 'administrador'] }
      },
      {
        path: 'venta/viajes/nuevo',
        name: 'crear-viaje',
        component: () => import('../views/vendedor/CrearViajeView.vue'),
        meta: { roles: ['vendedor', 'supervisor', 'administrador'] }
      },

      // Abordaje routes
      {
        path: 'abordaje',
        name: 'control-abordaje',
        component: () => import('../views/abordaje/ControlAbordajeView.vue'),
        meta: { roles: ['auxiliar', 'supervisor', 'administrador'] }
      },
      {
        path: 'abordaje/validar-qr',
        name: 'validar-qr',
        component: () => import('../views/abordaje/ValidarQrView.vue'),
        meta: { roles: ['auxiliar', 'supervisor', 'administrador'] }
      },

      // GPS routes
      {
        path: 'gps',
        name: 'gps',
        component: () => import('../views/gps/MonitoreoMapaView.vue'),
        meta: { roles: ['supervisor', 'administrador'] }
      }
    ]
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach((to) => {
  const auth = useAuthStore();

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } };
  }

  const roles = to.meta.roles || to.matched.flatMap((route) => route.meta.roles || []);

  if (roles.length && !roles.includes(auth.role)) {
    return auth.role === 'vendedor' ? { name: 'venta' } : { name: 'dashboard' };
  }

  if (to.name === 'login' && auth.isAuthenticated) {
    return auth.role === 'vendedor' ? { name: 'venta' } : { name: 'dashboard' };
  }

  return true;
});

export default router;
