<template>
  <section class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white">
      <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3">
        <RouterLink class="text-lg font-black text-slate-800" to="/">Terminal El Dorado</RouterLink>
        <nav class="flex flex-wrap items-center gap-2 text-sm font-bold">
          <RouterLink class="rounded-lg px-4 py-2 text-slate-600 hover:bg-slate-100" to="/boleteria">Comprar boletos</RouterLink>
          <RouterLink class="rounded-lg px-4 py-2 text-slate-600 hover:bg-slate-100" to="/rastrear">Rastrear bus</RouterLink>
          <RouterLink class="btn btn-secondary" to="/login">Iniciar sesion</RouterLink>
        </nav>
      </div>
    </header>

    <main class="mx-auto grid max-w-7xl gap-6 px-4 py-8 lg:grid-cols-[1fr_400px]">
      <section class="card p-6">
        <p class="text-sm font-black uppercase text-blue-600">Registro publico</p>
        <h1 class="mt-2 text-3xl font-black text-slate-800">Registrar pasajero</h1>
        <p class="mt-3 max-w-2xl text-slate-600">
          Este registro permite comprar pasajes en linea. La huella queda como no verificada hasta que el pasajero pase por plataforma en la terminal.
        </p>

        <form class="mt-6 grid gap-4 md:grid-cols-2" @submit.prevent="registrar">
          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-slate-600">Nombres</label>
            <input v-model.trim="form.nombres" class="form-input" required />
          </div>
          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-slate-600">Apellidos</label>
            <input v-model.trim="form.apellidos" class="form-input" required />
          </div>
          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-slate-600">Carnet de identidad</label>
            <input v-model.trim="form.numero_ci" class="form-input" required />
          </div>
          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-slate-600">Complemento CI</label>
            <input v-model.trim="form.complemento_ci" class="form-input" maxlength="5" placeholder="Opcional" />
          </div>
          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-slate-600">Expedido en</label>
            <select v-model="form.expedido_en" class="form-input" required>
              <option value="CB">Cochabamba</option>
              <option value="LP">La Paz</option>
              <option value="SC">Santa Cruz</option>
              <option value="OR">Oruro</option>
              <option value="PT">Potosi</option>
              <option value="CH">Chuquisaca</option>
              <option value="TJ">Tarija</option>
              <option value="BN">Beni</option>
              <option value="PD">Pando</option>
            </select>
          </div>
          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-slate-600">Fecha de nacimiento</label>
            <input v-model="form.fecha_nacimiento" class="form-input" required type="date" />
          </div>
          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-slate-600">Telefono</label>
            <input v-model.trim="form.telefono" class="form-input" />
          </div>
          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-slate-600">Correo</label>
            <input v-model.trim="form.email" class="form-input" type="email" />
          </div>

          <div class="md:col-span-2">
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
              <p class="flex items-center gap-2 font-bold text-amber-800">
                <Fingerprint :size="18" />
                Huella no verificada
              </p>
              <p class="mt-1 text-sm text-amber-700">
                El registro publico no captura huella. Aparecera como pendiente hasta que se verifique en plataforma.
              </p>
            </div>
          </div>

          <div class="flex flex-wrap gap-3 md:col-span-2">
            <button class="btn-primary" :disabled="guardando" type="submit">
              <LoaderCircle v-if="guardando" class="animate-spin" :size="18" />
              <UserPlus v-else :size="18" />
              {{ guardando ? 'Guardando...' : 'Guardar registro' }}
            </button>
            <RouterLink class="btn-secondary" to="/boleteria">
              <Ticket :size="18" />
              Ir a comprar boleto
            </RouterLink>
          </div>
        </form>

        <div v-if="error" class="alert alert-error">{{ error }}</div>
        <div v-if="mensaje" class="alert alert-success">{{ mensaje }}</div>
      </section>

      <aside class="space-y-4">
        <section class="card p-5">
          <h2 class="font-bold text-lg text-slate-800">Estado del registro</h2>
          <div v-if="pasajero" class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
            <p class="font-black text-slate-900">{{ pasajero.nombres }} {{ pasajero.apellidos }}</p>
            <p class="text-sm text-slate-600">CI {{ pasajero.numero_ci }} {{ pasajero.expedido_en }}</p>
            <span class="badge" :class="pasajero.tiene_huella ? 'badge-green' : 'badge-amber'">
              {{ pasajero.tiene_huella ? 'Huella verificada' : 'Huella no verificada' }}
            </span>
            <RouterLink class="btn-primary w-full mt-4" to="/boleteria">Comprar con este registro</RouterLink>
          </div>
          <div v-else class="mt-4 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600 text-center">
            Completa el formulario para crear un registro.
          </div>
        </section>

        <section class="rounded-xl border border-blue-200 bg-blue-50 p-4">
          <p class="font-bold text-blue-900">Para verificar huella</p>
          <p class="mt-1 text-sm text-blue-800">
            El pasajero debe presentarse en plataforma con su CI. El vendedor registrara la huella.
          </p>
        </section>
      </aside>
    </main>
  </section>
</template>

<script setup>
import { Fingerprint, LoaderCircle, Ticket, UserPlus } from 'lucide-vue-next';
import { reactive, ref } from 'vue';
import { publicPreRegistrarPasajero } from '../../api/public';

const form = reactive({
  nombres: '',
  apellidos: '',
  numero_ci: '',
  complemento_ci: '',
  expedido_en: 'CB',
  fecha_nacimiento: '',
  telefono: '',
  email: ''
});

const pasajero = ref(null);
const guardando = ref(false);
const mensaje = ref('');
const error = ref('');

async function registrar() {
  error.value = '';
  mensaje.value = '';
  guardando.value = true;

  try {
    const response = await publicPreRegistrarPasajero({ ...form });
    pasajero.value = response.data;
    mensaje.value = pasajero.value.tiene_huella
      ? 'El pasajero ya estaba registrado y tiene huella verificada.'
      : 'Registro guardado. Estado: huella no verificada hasta pasar por plataforma.';
  } catch (err) {
    error.value = err.message || 'No se pudo guardar el registro.';
  } finally {
    guardando.value = false;
  }
}
</script>

<style scoped>
.card {
  @apply bg-white rounded-xl border border-slate-200;
}

.form-input {
  @apply w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.btn-primary {
  @apply flex items-center justify-center gap-2 py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors;
}

.btn-secondary {
  @apply flex items-center justify-center gap-2 py-3 px-6 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-colors;
}

.alert {
  @apply mt-4 p-3 rounded-xl text-sm font-medium;
}

.alert-error {
  @apply bg-red-50 text-red-700;
}

.alert-success {
  @apply bg-green-50 text-green-700;
}

.badge {
  @apply inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full mt-2;
}

.badge-green {
  @apply bg-green-100 text-green-700;
}

.badge-amber {
  @apply bg-amber-100 text-amber-700;
}
</style>
