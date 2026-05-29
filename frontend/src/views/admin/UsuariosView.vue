<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Gestion de usuarios</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Administra los usuarios del sistema</p>
      </div>
      <button class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors">
        <Plus :size="18" />
        Nuevo usuario
      </button>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden">
      <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-[200px]">
          <Search :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input v-model="searchQuery" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white text-sm" placeholder="Buscar usuario..." />
        </div>
        <select v-model="filterRole" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white text-sm">
          <option value="">Todos los roles</option>
          <option value="administrador">Administrador</option>
          <option value="supervisor">Supervisor</option>
          <option value="vendedor">Vendedor</option>
          <option value="auxiliar">Auxiliar</option>
        </select>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50 dark:bg-slate-900">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Usuario</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Correo</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rol</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estado</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ultima conexion</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
              <td class="px-4 py-4">
                <div class="flex items-center gap-3">
                  <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-medium">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <p class="font-medium text-slate-800 dark:text-white">{{ user.name }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ user.username }}</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4 text-sm text-slate-600 dark:text-slate-300">{{ user.email }}</td>
              <td class="px-4 py-4">
                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full" :class="getRoleClass(user.role)">
                  {{ user.roleLabel }}
                </span>
              </td>
              <td class="px-4 py-4">
                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full" :class="user.is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'">
                  {{ user.is_active ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td class="px-4 py-4 text-sm text-slate-500 dark:text-slate-400">{{ user.last_login || 'Nunca' }}</td>
              <td class="px-4 py-4">
                <div class="flex items-center justify-end gap-2">
                  <button class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400" title="Editar">
                    <Pencil :size="16" />
                  </button>
                  <button class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-500 dark:text-red-400" title="Eliminar">
                    <Trash2 :size="16" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
        <p class="text-sm text-slate-500 dark:text-slate-400">Mostrando {{ filteredUsers.length }} de {{ users.length }} usuarios</p>
        <div class="flex items-center gap-2">
          <button class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-50" :disabled="currentPage === 1" @click="currentPage--">Anterior</button>
          <span class="px-3 py-1.5 text-sm text-slate-600 dark:text-slate-300">{{ currentPage }} / {{ totalPages }}</span>
          <button class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-50" :disabled="currentPage === totalPages" @click="currentPage++">Siguiente</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Pencil, Plus, Search, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const searchQuery = ref('');
const filterRole = ref('');
const currentPage = ref(1);
const perPage = 10;

const users = ref([
  { id: 1, name: 'Carlos Mendoza', username: 'carlos.mendoza', email: 'carlos@eldorado.bo', role: 'administrador', roleLabel: 'Administrador', is_active: true, last_login: 'Hace 5 minutos' },
  { id: 2, name: 'Maria Garcia', username: 'maria.garcia', email: 'maria@eldorado.bo', role: 'supervisor', roleLabel: 'Supervisor', is_active: true, last_login: 'Hace 1 hora' },
  { id: 3, name: 'Juan Perez', username: 'juan.perez', email: 'juan@eldorado.bo', role: 'vendedor', roleLabel: 'Vendedor', is_active: true, last_login: 'Hace 3 horas' },
  { id: 4, name: 'Ana Lopez', username: 'ana.lopez', email: 'ana@eldorado.bo', role: 'vendedor', roleLabel: 'Vendedor', is_active: true, last_login: 'Ayer' },
  { id: 5, name: 'Roberto Sanchez', username: 'roberto.sanchez', email: 'roberto@eldorado.bo', role: 'auxiliar', roleLabel: 'Auxiliar', is_active: false, last_login: 'Hace 5 dias' },
]);

const filteredUsers = computed(() => {
  let result = users.value;
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter((u) => u.name.toLowerCase().includes(query) || u.email.toLowerCase().includes(query) || u.username.toLowerCase().includes(query));
  }
  if (filterRole.value) {
    result = result.filter((u) => u.role === filterRole.value);
  }
  return result;
});

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / perPage));

function getRoleClass(role) {
  const classes = {
    administrador: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    supervisor: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    vendedor: 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400',
    auxiliar: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
  };
  return classes[role] || 'bg-slate-100 text-slate-700';
}
</script>
