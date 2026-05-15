import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
  {
    path: '/',
    name: 'inicio-publico',
    component: () => import('../views/public/HomePublicaView.vue')
  },
  {
    path: '/boleteria',
    name: 'boleteria',
    component: () => import('../views/public/BoleteriaPublicaView.vue')
  },
  {
    path: '/registro',
    name: 'registro-publico',
    component: () => import('../views/public/RegistroPublicoView.vue')
  },
  {
    path: '/rastrear',
    name: 'rastrear-bus',
    component: () => import('../views/public/RastrearBusView.vue')
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/auth/LoginView.vue')
  },
  {
    path: '/',
    component: () => import('../layouts/AdminLayout.vue'),
    meta: { requiresAuth: true },
    children: [
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
      }
    ]
  },
  {
    path: '/venta',
    component: () => import('../layouts/VendedorLayout.vue'),
    meta: { requiresAuth: true, roles: ['vendedor', 'supervisor', 'administrador'] },
    children: [
      { path: '', name: 'venta', component: () => import('../views/vendedor/VentaBoletoView.vue') },
      { path: 'pasajeros', name: 'registro-pasajero', component: () => import('../views/vendedor/RegistroPasajeroView.vue') },
      { path: 'viajes', name: 'viajes-activos', component: () => import('../views/vendedor/ViajesActivosView.vue') },
      { path: 'viajes/nuevo', name: 'crear-viaje', component: () => import('../views/vendedor/CrearViajeView.vue') }
    ]
  },
  {
    path: '/abordaje',
    component: () => import('../layouts/AuxiliarLayout.vue'),
    meta: { requiresAuth: true, roles: ['auxiliar', 'supervisor', 'administrador'] },
    children: [
      { path: '', name: 'control-abordaje', component: () => import('../views/abordaje/ControlAbordajeView.vue') },
      { path: 'validar-qr', name: 'validar-qr', component: () => import('../views/abordaje/ValidarQrView.vue') }
    ]
  },
  {
    path: '/gps',
    component: () => import('../layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, roles: ['supervisor', 'administrador'] },
    children: [{ path: '', name: 'gps', component: () => import('../views/gps/MonitoreoMapaView.vue') }]
  },
  {
    path: '/consulta',
    name: 'consulta-autoridad',
    component: () => import('../views/autoridad/ConsultaViajeView.vue')
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
