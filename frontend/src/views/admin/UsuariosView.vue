<template>
  <section class="grid gap-4 lg:grid-cols-[360px_1fr]">
    <form class="panel rounded-lg p-4" @submit.prevent="guardar">
      <h1 class="mb-4 text-xl font-black text-slate-900">Usuarios</h1>
      <div class="space-y-3">
        <input v-model="form.nombre" class="field" placeholder="Nombre completo" required />
        <input v-model="form.email" class="field" type="email" placeholder="Email" required />
        <input v-model="form.password" class="field" type="password" placeholder="Contrasena" :required="!form.id" />
        <select v-model="form.rol" class="field" required>
          <option value="administrador">Administrador</option>
          <option value="supervisor">Supervisor</option>
          <option value="vendedor">Vendedor</option>
          <option value="auxiliar">Auxiliar</option>
          <option value="conductor">Conductor</option>
          <option value="autoridad">Autoridad</option>
        </select>
        <select v-model="form.turno" class="field">
          <option :value="null">Sin turno</option>
          <option value="manana">Manana</option>
          <option value="tarde">Tarde</option>
          <option value="noche">Noche</option>
        </select>
        <button class="btn btn-primary w-full">
          <Save :size="18" />
          Guardar
        </button>
      </div>
    </form>

    <section class="panel rounded-lg p-4">
      <div class="mb-3 flex items-center justify-between gap-3">
        <h2 class="font-black text-slate-800">Operadores</h2>
        <button class="btn btn-secondary" @click="cargar">
          <RefreshCcw :size="18" />
          Actualizar
        </button>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Nombre</th><th>Email</th><th>Rol</th><th></th></tr>
          </thead>
          <tbody>
            <tr v-for="usuario in usuarios" :key="usuario.id">
              <td>{{ usuario.nombre }}</td>
              <td>{{ usuario.email }}</td>
              <td><span class="chip bg-slate-100 text-slate-700">{{ usuario.rol }}</span></td>
              <td class="text-right">
                <button class="btn btn-secondary" @click="editar(usuario)">
                  <Pencil :size="16" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </section>
</template>

<script setup>
import { Pencil, RefreshCcw, Save } from 'lucide-vue-next';
import { onMounted, reactive, ref } from 'vue';
import { actualizarUsuario, crearUsuario, listarUsuarios } from '../../api/admin';

const usuarios = ref([]);
const form = reactive({ id: null, nombre: '', email: '', password: '', rol: 'vendedor', turno: 'manana', activo: true });

onMounted(cargar);

async function cargar() {
  const response = await listarUsuarios();
  usuarios.value = response.data?.data || response.data || [];
}

function editar(usuario) {
  Object.assign(form, { ...usuario, password: '' });
}

async function guardar() {
  if (form.id) {
    await actualizarUsuario(form.id, form);
  } else {
    await crearUsuario(form);
  }
  Object.assign(form, { id: null, nombre: '', email: '', password: '', rol: 'vendedor', turno: 'manana', activo: true });
  await cargar();
}
</script>
