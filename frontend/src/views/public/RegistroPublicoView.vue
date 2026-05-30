<template>
  <section class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900">
    <main class="max-w-7xl mx-auto px-6 py-8">
      <section class="card p-6">
        <p class="text-sm font-black uppercase text-blue-400">Registro publico</p>
        <h1 class="mt-2 text-3xl font-black text-white">Registrar pasajero</h1>
        <p class="mt-3 max-w-2xl text-white/60">
          Este registro permite comprar pasajes en linea. La huella queda como no verificada hasta que el pasajero pase por plataforma en la terminal.
        </p>

        <form class="mt-6 grid gap-4 md:grid-cols-2" @submit.prevent="registrar">
          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-white/80">Nombres</label>
            <input v-model.trim="form.nombres" class="form-input" required />
          </div>
          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-white/80">Apellidos</label>
            <input v-model.trim="form.apellidos" class="form-input" required />
          </div>
          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-white/80">Carnet de identidad</label>
            <input v-model.trim="form.numero_ci" class="form-input" required />
          </div>
          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-white/80">Complemento CI</label>
            <input v-model.trim="form.complemento_ci" class="form-input" maxlength="5" placeholder="Opcional" />
          </div>
          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-white/80">Expedido en</label>
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
            <label class="text-sm font-semibold text-white/80">Fecha de nacimiento</label>
            <input v-model="form.fecha_nacimiento" class="form-input" required type="date" />
          </div>
          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-white/80">Telefono</label>
            <input v-model.trim="form.telefono" class="form-input" />
          </div>
          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-white/80">Correo</label>
            <input v-model.trim="form.email" class="form-input" type="email" />
          </div>

          <div class="md:col-span-2">
            <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 p-4">
              <p class="flex items-center gap-2 font-bold text-amber-400">
                <Fingerprint :size="18" />
                Huella no verificada
              </p>
              <p class="mt-1 text-sm text-amber-300/80">
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
          <h2 class="font-bold text-lg text-white">Estado del registro</h2>
          <div v-if="pasajero" class="mt-4 rounded-xl border border-white/10 bg-white/5 p-4">
            <p class="font-black text-white">{{ pasajero.nombres }} {{ pasajero.apellidos }}</p>
            <p class="text-sm text-white/60">CI {{ pasajero.numero_ci }} {{ pasajero.expedido_en }}</p>
            <span class="badge" :class="pasajero.tiene_huella ? 'badge-green' : 'badge-amber'">
              {{ pasajero.tiene_huella ? 'Huella verificada' : 'Huella no verificada' }}
            </span>
            <RouterLink class="btn-primary w-full mt-4" to="/boleteria">Comprar con este registro</RouterLink>
          </div>
          <div v-else class="mt-4 rounded-xl border-2 border-dashed border-white/20 bg-white/5 p-4 text-sm text-white/60 text-center">
            Completa el formulario para crear un registro.
          </div>
        </section>

        <section class="rounded-xl border border-blue-500/30 bg-blue-500/10 p-4">
          <p class="font-bold text-blue-400">Para verificar huella</p>
          <p class="mt-1 text-sm text-blue-300/80">
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
  @apply bg-white/5 backdrop-blur rounded-xl border border-white/10;
}

.form-input {
  @apply w-full px-4 py-3 rounded-xl border border-white/10 bg-white/10 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.btn-primary {
  @apply flex items-center justify-center gap-2 py-3 px-6 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl transition-colors;
}

.btn-secondary {
  @apply flex items-center justify-center gap-2 py-3 px-6 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl transition-colors;
}

.alert {
  @apply mt-4 p-3 rounded-xl text-sm font-medium;
}

.alert-error {
  @apply bg-red-500/20 text-red-400 border border-red-500/30;
}

.alert-success {
  @apply bg-green-500/20 text-green-400 border border-green-500/30;
}

.badge {
  @apply inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full mt-2;
}

.badge-green {
  @apply bg-green-500/20 text-green-400;
}

.badge-amber {
  @apply bg-amber-500/20 text-amber-400;
}
</style>
