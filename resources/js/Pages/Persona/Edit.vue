<!-- resources/js/Pages/Persona/Edit.vue -->
<script setup>
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref, watch, onMounted, computed } from 'vue'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  persona: { type: Object, required: true },
  // { ubigeo_dep, ubigeo_pro, ubigeo_com }
  initialData: { type: Object, default: () => null },
  // { tiposDocumento:[{value,text}], departamentos:[{ubigeo_dep,text}], sexos:[{value,text}], discapacidads:[{id,text}] }
  options: { type: Object, default: () => ({ tiposDocumento: [], departamentos: [], sexos: [], discapacidads: [] }) }
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

/* =========================
   Teléfono: 9 dígitos crudos
========================= */
const telefonoError  = ref('')
const telefonoDigits = ref('')

const telefonoModel = computed({
  get() {
    return telefonoDigits.value
  },
  set(v) {
    const digits = (v || '').replace(/\D/g, '').slice(0, 9)
    telefonoDigits.value = digits
    e.target.value = clean           // lo que ves en el input
    form.telefono = clean            // lo que se envía al servidor
    telefonoError.value = clean && clean.length !== 9 ? 'El teléfono debe tener exactamente 9 dígitos' : ''
  }
})

/* precargar teléfono del registro */
onMounted(() => {
  telefonoModel.value = (props.persona.telefono || '').replace(/\D/g, '').slice(0, 9)
})

/* =========================
   Documento: límite duro + validación
========================= */
const documentoError = ref('')

const docMaxLen = computed(() => {
  switch (form.tipo_doc) {
    case 'DNI':       return 8          // 8 exactos
    case 'CE':        return 12         // 9–12, techo 12
    case 'Pasaporte': return 12         // 6–12, techo 12
    default:          return 12
  }
})

/* Texto de ayuda dinámico bajo el input */
const docHelp = computed(() => {
  switch (form.tipo_doc) {
    case 'DNI':       return '8 dígitos exactos'
    case 'CE':        return '9–12 dígitos'
    case 'Pasaporte': return '6–12 caracteres (letras y números)'
    default:          return ''
  }
})

const handleDocInput = (e) => {
  let v = e.target.value ?? ''
  if (form.tipo_doc === 'Pasaporte') {
    v = v.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, docMaxLen.value)
  } else {
    v = v.replace(/\D/g, '').slice(0, docMaxLen.value)
  }
  if (v !== form.num_doc) form.num_doc = v
}

// Bloquea tecleo adicional (permite borrar/navegar/pegar con selección)
const allowDocKeydown = (e) => {
  const editingKeys = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Home','End']
  if (editingKeys.includes(e.key) || e.ctrlKey || e.metaKey) return
  const el = e.target
  const hasSelection = el.selectionStart !== el.selectionEnd
  if ((form.num_doc?.length || 0) >= docMaxLen.value && !hasSelection) {
    e.preventDefault()
  }
}

watch([() => form.tipo_doc, () => form.num_doc], ([tipo, documento]) => {
  if (!tipo || !documento) {
    documentoError.value = ''
    return
  }
  const soloNumeros = documento.replace(/\D/g, '')
  switch (tipo) {
    case 'DNI':
      documentoError.value = soloNumeros.length === 8 ? '' : 'El DNI debe tener exactamente 8 dígitos'
      break
    case 'CE':
      documentoError.value = (soloNumeros.length >= 9 && soloNumeros.length <= 12)
        ? '' : 'El Carné de Extranjería debe tener entre 9 y 12 dígitos'
      break
    case 'Pasaporte':
      documentoError.value = (documento.length >= 6 && documento.length <= 12)
        ? '' : 'El Pasaporte debe tener entre 6 y 12 caracteres'
      break
    default:
      documentoError.value = ''
  }
})

watch(() => form.tipo_doc, () => {
  form.num_doc = ''
  documentoError.value = ''
})

/* ---------- Fetch helpers ---------- */
const fetchProvincias = async (ubigeoDep) => {
  provincias.value = []
  distritos.value = []
  if (!ubigeoDep) return
  try {
    const url = (typeof route === 'function')
      ? route('personas.provincias', { ubigeoDep })
      : `/personas/provincias/${ubigeoDep}`
    const res = await fetch(url)
    provincias.value = await res.json()
  } catch (e) {
    console.error('Error fetchProvincias', e)
  }
}

const fetchDistritos = async (ubigeoPro) => {
  distritos.value = []
  if (!ubigeoPro) return
  try {
    const url = (typeof route === 'function')
      ? route('personas.distritos', { ubigeoPro })
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
function submit () {
  Swal.fire({
    title: '¿Deseas actualizar esta persona?',
    text: 'Confirma que los datos son correctos.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, actualizar',
    cancelButtonText: 'Cancelar',
  }).then((r) => {
    if (!r.isConfirmed) return
    form.put(route('personas.update', props.persona.id), {
      onStart: () => {
        Swal.fire({ title: 'Actualizando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() })
      },
      onSuccess: () => {
        Swal.fire({ icon: 'success', title: 'Actualizado', timer: 1500, showConfirmButton: false })
      },
      onError: (errors) => {
        if (errors.num_doc) {
          Swal.fire({ icon: 'error', title: 'Número de documento duplicado', text: errors.num_doc })
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: 'Revisa los campos del formulario.' })
        }
      },
    })
  })
}


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
          <form @submit.prevent="submit" class="space-y-8" autocomplete="off">

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
                  <input
                    v-model="form.num_doc"
                    @input="handleDocInput"
                    @keydown="allowDocKeydown"
                    :maxlength="docMaxLen"
                    :inputmode="form.tipo_doc === 'Pasaporte' ? 'text' : 'numeric'"
                    class="w-full px-3 py-2 border rounded-md"
                    :class="{'border-red-500': form.errors.num_doc || documentoError}"
                    required
                  >
                  <p v-if="documentoError" class="text-red-600 text-sm mt-1">{{ documentoError }}</p>
                  <p v-else-if="form.errors.num_doc" class="text-red-600 text-sm mt-1">{{ form.errors.num_doc }}</p>
                  <p v-else-if="form.tipo_doc" class="text-xs text-gray-500 mt-1">{{ docHelp }}</p>
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
                    <option :value="null">Ninguna</option>
                    <option v-for="d in options.discapacidads" :key="d.id" :value="d.id">
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
                  <input
                    v-model="form.telefono"
                    type="tel"
                    inputmode="numeric"
                    pattern="\d*"
                    maxlength="9"
                    @input="onTelInput"
                    autocomplete="off"
                    placeholder="9 dígitos (+51)"
                    :class="[
                      'w-full px-4 py-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500',
                      telefonoError || form.errors.telefono ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300'
                    ]"
                  />
                  <p v-if="telefonoError" class="mt-1 text-sm text-red-600">{{ telefonoError }}</p>
                  <p v-else-if="form.errors.telefono" class="mt-1 text-sm text-red-600">{{ form.errors.telefono }}</p>
                  <p class="mt-1 text-xs text-gray-500">Ingresa solo 9 dígitos (Ej: 987654321)</p>
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
