<!-- resources/js/Pages/Persona/Edit.vue -->
<script setup>
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref, watch, onMounted, computed } from 'vue'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  persona: { type: Object, required: true },
  // { ubigeo_dep, ubigeo_pro, ubigeo_com }
  initialData: { type: Object, default: () => null },
  // { tiposDocumento:[{value,text}], departamentos:[{ubigeo_dep,text}], sexos:[{value,text}], discapacidades:[{id,text}] }
  options: { type: Object, default: () => ({ tiposDocumento: [], departamentos: [], sexos: [], discapacidades: [] }) }
})

/* ---------- Utilidades de fecha ---------- */
const toYYYYMMDD = (val) => {
  if (!val || typeof val !== 'string') return ''
  if (val.includes('T')) val = val.split('T')[0]
  if (val.includes(' ')) val = val.split(' ')[0]
  if (/^\d{4}-\d{2}-\d{2}$/.test(val)) return val
  if (val.includes('/')) {
    const [d, m, y] = val.split('/').map(Number)
    if ([y, m, d].some(Number.isNaN)) return ''
    return `${y.toString().padStart(4,'0')}-${m.toString().padStart(2,'0')}-${d.toString().padStart(2,'0')}`
  }
  return ''
}

/* ---------- Estado selects por UBIGEO ---------- */
const provincias = ref([]) // [{ ubigeo_pro, text }]
const distritos  = ref([]) // [{ ubigeo_com, text }]

const selDep  = ref(props.initialData?.ubigeo_dep || '')
const selProv = ref(props.initialData?.ubigeo_pro || '')
const selDist = ref(props.initialData?.ubigeo_com || '')

/* ---------- Form (enviamos ubigeo_com) ---------- */
const form = useForm({
  nombre:          props.persona.nombre ?? '',
  pri_ape:         props.persona.pri_ape ?? '',
  seg_ape:         props.persona.seg_ape ?? '',
  tipo_doc:        props.persona.tipo_doc ?? 'DNI',
  num_doc:         props.persona.num_doc ?? '',
  sexo:            props.persona.sexo ?? 'M',
  discapacidad_id: props.persona.discapacidad_id ?? null,
  fecha_nac:       toYYYYMMDD(props.persona.fecha_nac),
  direccion:       props.persona.direccion ?? '',
  telefono:        props.persona.telefono ?? '',
  ubigeo_com:      selDist.value, // <- lo que valida tu update()
})

/* ---------- Edad ---------- */
const edad = computed(() => {
  const v = form.fecha_nac
  if (!/^\d{4}-\d{2}-\d{2}$/.test(v)) return null
  const [y, m, d] = v.split('-').map(Number)
  const today = new Date()
  let e = today.getFullYear() - y
  const md = (today.getMonth() + 1) - m
  const dd = today.getDate() - d
  if (md < 0 || (md === 0 && dd < 0)) e--
  return e
})

/* ---------- Fetch helpers ---------- */
const fetchProvincias = async (ubigeoDep) => {
  provincias.value = [];
  distritos.value = [];
  if (!ubigeoDep) return;
  try {
    if (typeof route !== 'function') {
      console.error("La función 'route' no está disponible.");
      return;
    }
    const url = route('personas.provincias', ubigeoDep);
    const res = await fetch(url);
    provincias.value = await res.json();
  } catch (e) {
    console.error('Error fetchProvincias', e);
  }
};


const fetchDistritos = async (ubigeoPro) => {
  distritos.value = []
  if (!ubigeoPro) return
  try {
    const url = (typeof route === 'function')
      ? route('personas.distritos', ubigeoPro)
      : `/personas/distritos/${ubigeoPro}`
    const res = await fetch(url)
    distritos.value = await res.json()
  } catch (e) { console.error('Error fetchDistritos', e) }
}

/* ---------- Precarga y cascada ---------- */
onMounted(async () => {
  if (selDep.value)  await fetchProvincias(selDep.value)
  if (selProv.value) await fetchDistritos(selProv.value)
})

watch(selDep, async (val) => {
  selProv.value = ''; selDist.value = ''
  form.ubigeo_com = ''
  await fetchProvincias(val)
})

watch(selProv, async (val) => {
  selDist.value = ''
  form.ubigeo_com = ''
  await fetchDistritos(val)
})

watch(selDist, (val) => { form.ubigeo_com = val })

/* ---------- Submit ---------- */
// En Edit.vue
const submit = () => {
  if (!props.persona?.id) {
    console.error("El ID de la persona no está definido.");
    return;
  }
  form.fecha_nac = toYYYYMMDD(form.fecha_nac);
  form.put(route('personas.update', props.persona.id), {
    preserveScroll: true,
    onSuccess: () => {
      router.visit(route('personas.index'), { replace: true, preserveState: false });
    }
  });
};


const resetForm = () => form.reset()

</script>

<template>
  <AppLayout title="Editar Persona">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Persona</h2>
    </template>

    <div class="py-10">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow p-6">
          <form @submit.prevent="submit" class="space-y-8">

            <!-- Datos personales -->
            <section>
              <h3 class="text-lg font-semibold mb-4 border-b pb-2">Datos personales</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium mb-1">Nombre *</label>
                  <input v-model="form.nombre" type="text"
                         class="w-full px-3 py-2 border rounded-md"
                         :class="{'border-red-500': form.errors.nombre}" required>
                  <p v-if="form.errors.nombre" class="text-red-600 text-sm mt-1">{{ form.errors.nombre }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium mb-1">Primer apellido *</label>
                  <input v-model="form.pri_ape" type="text"
                         class="w-full px-3 py-2 border rounded-md"
                         :class="{'border-red-500': form.errors.pri_ape}" required>
                  <p v-if="form.errors.pri_ape" class="text-red-600 text-sm mt-1">{{ form.errors.pri_ape }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium mb-1">Segundo apellido *</label>
                  <input v-model="form.seg_ape" type="text"
                         class="w-full px-3 py-2 border rounded-md"
                         :class="{'border-red-500': form.errors.seg_ape}" required>
                  <p v-if="form.errors.seg_ape" class="text-red-600 text-sm mt-1">{{ form.errors.seg_ape }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium mb-1">Fecha de nacimiento *</label>
                  <input v-model="form.fecha_nac" type="date"
                         class="w-full px-3 py-2 border rounded-md"
                         :class="{'border-red-500': form.errors.fecha_nac}" required>
                  <p class="text-sm text-gray-500 mt-1">Edad: <b>{{ edad ?? '—' }}</b></p>
                  <p v-if="form.errors.fecha_nac" class="text-red-600 text-sm mt-1">{{ form.errors.fecha_nac }}</p>
                </div>
              </div>
            </section>

            <!-- Documento -->
            <section>
              <h3 class="text-lg font-semibold mb-4 border-b pb-2">Documento</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium mb-1">Tipo de documento *</label>
                  <select v-model="form.tipo_doc"
                          class="w-full px-3 py-2 border rounded-md"
                          :class="{'border-red-500': form.errors.tipo_doc}" required>
                    <option v-for="t in options.tiposDocumento" :key="t.value" :value="t.value">
                      {{ t.text }}
                    </option>
                  </select>
                  <p v-if="form.errors.tipo_doc" class="text-red-600 text-sm mt-1">{{ form.errors.tipo_doc }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium mb-1">Número de documento *</label>
                  <input v-model="form.num_doc" type="text"
                         class="w-full px-3 py-2 border rounded-md"
                         :class="{'border-red-500': form.errors.num_doc}" required>
                  <p v-if="form.errors.num_doc" class="text-red-600 text-sm mt-1">{{ form.errors.num_doc }}</p>
                </div>
              </div>
            </section>

            <!-- Ubicación (cascada por UBIGEO) -->
            <section>
              <h3 class="text-lg font-semibold mb-4 border-b pb-2">Ubicación</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <label class="block text-sm font-medium mb-1">Departamento *</label>
                  <select v-model="selDep" class="w-full px-3 py-2 border rounded-md" required>
                    <option value="" disabled>Seleccionar…</option>
                    <option v-for="d in options.departamentos" :key="d.ubigeo_dep" :value="d.ubigeo_dep">
                      {{ d.text }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium mb-1">Provincia *</label>
                  <select v-model="selProv" class="w-full px-3 py-2 border rounded-md" :disabled="!selDep" required>
                    <option value="" disabled>Seleccionar…</option>
                    <option v-for="p in provincias" :key="p.ubigeo_pro" :value="p.ubigeo_pro">
                      {{ p.text }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium mb-1">Distrito *</label>
                  <select v-model="selDist" class="w-full px-3 py-2 border rounded-md" :disabled="!selProv" required>
                    <option value="" disabled>Seleccionar…</option>
                    <option v-for="di in distritos" :key="di.ubigeo_com" :value="di.ubigeo_com">
                      {{ di.text }}
                    </option>
                  </select>
                  <p v-if="form.errors.ubigeo_com" class="text-red-600 text-sm mt-1">{{ form.errors.ubigeo_com }}</p>
                </div>
              </div>
            </section>

            <!-- Sexo y Discapacidad -->
            <section>
              <h3 class="text-lg font-semibold mb-4 border-b pb-2">Sexo y discapacidad</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium mb-1">Sexo *</label>
                  <select v-model="form.sexo"
                          class="w-full px-3 py-2 border rounded-md"
                          :class="{'border-red-500': form.errors.sexo}" required>
                    <option v-for="s in options.sexos" :key="s.value" :value="s.value">
                      {{ s.text }}
                    </option>
                  </select>
                  <p v-if="form.errors.sexo" class="text-red-600 text-sm mt-1">{{ form.errors.sexo }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium mb-1">Discapacidad</label>
                  <select v-model="form.discapacidad_id"
                          class="w-full px-3 py-2 border rounded-md"
                          :class="{'border-red-500': form.errors.discapacidad_id}">
                  
                    <option v-for="d in options.discapacidades" :key="d.id" :value="d.id">
                      {{ d.text }}
                    </option>
                  </select>
                  <p v-if="form.errors.discapacidad_id" class="text-red-600 text-sm mt-1">{{ form.errors.discapacidad_id }}</p>
                </div>
              </div>
            </section>

            <!-- Contacto -->
            <section>
              <h3 class="text-lg font-semibold mb-4 border-b pb-2">Contacto</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium mb-1">Teléfono</label>
                  <input v-model="form.telefono" type="tel"
                         class="w-full px-3 py-2 border rounded-md"
                         :class="{'border-red-500': form.errors.telefono}">
                  <p v-if="form.errors.telefono" class="text-red-600 text-sm mt-1">{{ form.errors.telefono }}</p>
                </div>
                
              </div>
            </section>

            <!-- Dirección -->
            <section>
              <h3 class="text-lg font-semibold mb-4 border-b pb-2">Dirección</h3>
              <input v-model="form.direccion" type="text"
                     class="w-full px-3 py-2 border rounded-md"
                     :class="{'border-red-500': form.errors.direccion}" placeholder="Calle, número, referencia…">
              <p v-if="form.errors.direccion" class="text-red-600 text-sm mt-1">{{ form.errors.direccion }}</p>
            </section>

            <!-- Botones -->
            <div class="flex justify-between items-center pt-2">
              <Link :href="route('personas.index')"
                    class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-100">
                Cancelar
              </Link>
              <div class="space-x-3">
                <button type="button" @click="resetForm"
                        class="px-4 py-2 border border-yellow-500 text-yellow-700 rounded-md hover:bg-yellow-50">
                  Restablecer
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-60"
                        :disabled="form.processing">
                  <span v-if="form.processing">Guardando…</span>
                  <span v-else>Guardar cambios</span>
                </button>
              </div>
            </div>

          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
