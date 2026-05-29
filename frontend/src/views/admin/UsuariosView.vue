<template>
  <section class="grid gap-6 lg:grid-cols-[400px_1fr]">
    <!-- Form -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ form.id ? 'Editar Usuario' : 'Nuevo Usuario' }}</h3>
      </div>
      <form class="card-body space-y-4" @submit.prevent="guardar">
        <div class="form-group">
          <label class="form-label">Nombre completo</label>
          <input v-model="form.nombre" class="form-input" placeholder="Juan Perez" required />
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input v-model="form.email" type="email" class="form-input" placeholder="juan@eldorado.bo" required />
        </div>
        <div class="form-group">
          <label class="form-label">Contrasena</label>
          <input v-model="form.password" type="password" class="form-input" placeholder="********" :required="!form.id" />
          <span v-if="form.id" class="form-hint">Solo llenar si desea cambiar la contrasena</span>
        </div>
        <div class="form-group">
          <label class="form-label">Rol</label>
          <select v-model="form.rol" class="form-input" required>
            <option value="administrador">Administrador</option>
            <option value="supervisor">Supervisor</option>
            <option value="vendedor">Vendedor</option>
            <option value="auxiliar">Auxiliar</option>
            <option value="conductor">Conductor</option>
            <option value="autoridad">Autoridad</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Turno</label>
          <select v-model="form.turno" class="form-input">
            <option :value="null">Sin turno</option>
            <option value="manana">Manana</option>
            <option value="tarde">Tarde</option>
            <option value="noche">Noche</option>
          </select>
        </div>
        <button type="submit" class="btn-primary w-full">
          <Save :size="18" />
          {{ form.id ? 'Actualizar' : 'Crear' }}
        </button>
        <button v-if="form.id" type="button" class="btn-secondary w-full" @click="resetForm">
          Cancelar
        </button>
      </form>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Operadores</h3>
        <button class="btn-icon" @click="cargar">
          <RefreshCw :size="18" />
        </button>
      </div>
      <div class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Email</th>
              <th>Rol</th>
              <th>Turno</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="5" class="text-center py-8">
                <span class="spinner mx-auto"></span>
              </td>
            </tr>
            <tr v-else-if="usuarios.length === 0">
              <td colspan="5" class="text-center py-8 text-slate-400">No hay usuarios registrados</td>
            </tr>
            <tr v-else v-for="usuario in usuarios" :key="usuario.id">
              <td class="font-medium text-slate-800 dark:text-white">{{ usuario.nombre }}</td>
              <td class="text-slate-500">{{ usuario.email }}</td>
              <td>
                <span class="badge" :class="getRolClass(usuario.rol)">{{ usuario.rol }}</span>
              </td>
              <td class="text-slate-500">{{ usuario.turno || '-' }}</td>
              <td class="text-right">
                <button class="btn-icon" @click="editar(usuario)">
                  <Pencil :size="16" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { Pencil, RefreshCw, Save } from 'lucide-vue-next';
import { actualizarUsuario, crearUsuario, listarUsuarios } from '../../api/admin';

const usuarios = ref([]);
const loading = ref(false);
const form = reactive({
  id: null,
  nombre: '',
  email: '',
  password: '',
  rol: 'vendedor',
  turno: 'manana',
  activo: true
});

onMounted(cargar);

async function cargar() {
  loading.value = true;
  try {
    const response = await listarUsuarios();
    usuarios.value = response.data?.data || response.data || [];
  } finally {
    loading.value = false;
  }
}

function editar(usuario) {
  Object.assign(form, { ...usuario, password: '' });
}

function resetForm() {
  Object.assign(form, { id: null, nombre: '', email: '', password: '', rol: 'vendedor', turno: 'manana', activo: true });
}

async function guardar() {
  if (form.id) {
    await actualizarUsuario(form.id, form);
  } else {
    await crearUsuario(form);
  }
  resetForm();
  await cargar();
}

function getRolClass(rol) {
  const classes = {
    administrador: 'badge-red',
    supervisor: 'badge-purple',
    vendedor: 'badge-blue',
    auxiliar: 'badge-green',
    conductor: 'badge-orange'
  };
  return classes[rol] || 'badge-slate';
}
</script>

<style scoped>
.form-group {
  @apply space-y-1.5;
}

.form-label {
  @apply block text-sm font-medium text-slate-700 dark:text-slate-300;
}

.form-input {
  @apply w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all;
}

.form-hint {
  @apply text-xs text-slate-400;
}

.btn-primary {
  @apply flex items-center justify-center gap-2 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors;
}

.btn-secondary {
  @apply flex items-center justify-center gap-2 py-2.5 px-4 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-medium rounded-xl transition-colors;
}

.btn-icon {
  @apply p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors;
}

.badge {
  @apply inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full;
}

.badge-red { @apply bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400; }
.badge-purple { @apply bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400; }
.badge-blue { @apply bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400; }
.badge-green { @apply bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400; }
.badge-orange { @apply bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400; }
.badge-slate { @apply bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300; }

.table-container {
  @apply overflow-x-auto;
}

.table {
  @apply w-full text-sm;
}

.table th {
  @apply px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700;
}

.table td {
  @apply px-4 py-3 text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-slate-700;
}

.table tbody tr {
  @apply hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors;
}

.spinner {
  @apply inline-block w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin;
}
</style>
