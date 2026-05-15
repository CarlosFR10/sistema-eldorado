<template>
  <section class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white">
      <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3">
        <RouterLink class="text-lg font-black text-eldorado-ink" to="/">Terminal El Dorado</RouterLink>
        <nav class="flex flex-wrap items-center gap-2 text-sm font-bold">
          <RouterLink class="rounded-md px-3 py-2 text-slate-700 hover:bg-teal-50" to="/boleteria">Comprar boletos</RouterLink>
          <RouterLink class="rounded-md px-3 py-2 text-slate-700 hover:bg-teal-50" to="/consulta">Mis boletos</RouterLink>
          <RouterLink class="rounded-md px-3 py-2 text-slate-700 hover:bg-teal-50" to="/rastrear">Rastrear bus</RouterLink>
          <RouterLink class="btn btn-secondary" to="/login">Iniciar sesion</RouterLink>
        </nav>
      </div>
    </header>

    <main class="mx-auto grid max-w-7xl gap-5 px-4 py-6 lg:grid-cols-[1fr_380px]">
      <section class="panel rounded-lg p-5">
        <p class="text-sm font-black uppercase text-eldorado-teal">Registro publico</p>
        <h1 class="text-3xl font-black text-slate-950">Registrar pasajero</h1>
        <p class="mt-2 max-w-3xl font-semibold text-slate-600">
          Este registro permite comprar pasajes en linea. La huella queda como no verificada hasta que el pasajero pase por plataforma en la terminal.
        </p>

        <form class="mt-5 grid gap-3 md:grid-cols-2" @submit.prevent="registrar">
          <label class="space-y-1">
            <span class="text-sm font-bold text-slate-600">Nombres</span>
            <input v-model.trim="form.nombres" class="field" required />
          </label>
          <label class="space-y-1">
            <span class="text-sm font-bold text-slate-600">Apellidos</span>
            <input v-model.trim="form.apellidos" class="field" required />
          </label>
          <label class="space-y-1">
            <span class="text-sm font-bold text-slate-600">Carnet de identidad</span>
            <input v-model.trim="form.numero_ci" class="field" required />
          </label>
          <label class="space-y-1">
            <span class="text-sm font-bold text-slate-600">Complemento CI</span>
            <input v-model.trim="form.complemento_ci" class="field" maxlength="5" placeholder="Opcional" />
          </label>
          <label class="space-y-1">
            <span class="text-sm font-bold text-slate-600">Expedido en</span>
            <select v-model="form.expedido_en" class="field" required>
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
          </label>
          <label class="space-y-1">
            <span class="text-sm font-bold text-slate-600">Fecha de nacimiento</span>
            <input v-model="form.fecha_nacimiento" class="field" required type="date" />
          </label>
          <label class="space-y-1">
            <span class="text-sm font-bold text-slate-600">Telefono</span>
            <input v-model.trim="form.telefono" class="field" />
          </label>
          <label class="space-y-1">
            <span class="text-sm font-bold text-slate-600">Correo</span>
            <input v-model.trim="form.email" class="field" type="email" />
          </label>

          <div class="md:col-span-2">
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
              <p class="flex items-center gap-2 font-black text-amber-950">
                <Fingerprint :size="18" />
                Huella no verificada
              </p>
              <p class="mt-1 text-sm font-semibold text-amber-900">
                El registro publico no captura huella. En el panel del vendedor aparecera como pendiente hasta que se verifique en plataforma.
              </p>
            </div>
          </div>

          <div class="flex flex-wrap gap-2 md:col-span-2">
            <button class="btn btn-primary" :disabled="guardando" type="submit">
              <LoaderCircle v-if="guardando" class="animate-spin" :size="18" />
              <UserPlus v-else :size="18" />
              {{ guardando ? 'Guardando...' : 'Guardar registro' }}
            </button>
            <RouterLink class="btn btn-secondary" to="/boleteria">
              <Ticket :size="18" />
              Ir a comprar boleto
            </RouterLink>
          </div>
        </form>

        <p v-if="error" class="mt-4 rounded-md bg-red-50 p-3 text-sm font-bold text-red-800">{{ error }}</p>
        <p v-if="mensaje" class="mt-4 rounded-md bg-emerald-50 p-3 text-sm font-bold text-emerald-800">{{ mensaje }}</p>
      </section>

      <aside class="space-y-4">
        <section class="panel rounded-lg p-5">
          <h2 class="font-black text-slate-900">Estado del registro</h2>
          <div v-if="pasajero" class="mt-4 rounded-lg border border-slate-200 bg-white p-4">
            <p class="font-black text-slate-950">{{ pasajero.nombres }} {{ pasajero.apellidos }}</p>
            <p class="text-sm font-semibold text-slate-600">CI {{ pasajero.numero_ci }} {{ pasajero.expedido_en }}</p>
            <span class="chip mt-3" :class="pasajero.tiene_huella ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900'">
              {{ pasajero.tiene_huella ? 'Huella verificada' : 'Huella no verificada' }}
            </span>
            <RouterLink class="btn btn-primary mt-4 w-full" to="/boleteria">Comprar con este registro</RouterLink>
          </div>
          <div v-else class="mt-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 text-sm font-semibold text-slate-600">
            Completa el formulario para crear un registro publico.
          </div>
        </section>

        <section class="rounded-lg border border-cyan-200 bg-cyan-50 p-4">
          <p class="font-black text-cyan-950">Para verificar huella</p>
          <p class="mt-1 text-sm font-semibold text-cyan-900">
            El pasajero debe presentarse en plataforma con su CI. El vendedor registrara la huella y el estado cambiara a verificada.
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
