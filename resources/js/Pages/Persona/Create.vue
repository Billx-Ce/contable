<script setup>
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  tiposDocumento: { type: Array, default: () => [] },
  departamentos:  { type: Array, default: () => [] },
  sexos:          { type: Array, default: () => [] },
  discapacidads:  { type: Array, default: () => [] }
})

/* =========================
   Form principal
========================= */
const form = useForm({
  nombre: '',
  pri_ape: '',
  seg_ape: '',
  tipo_doc: '',
  num_doc: '',
  // UBIGEO (cascada)
  ubigeo_dep: '',   // <- lo añadimos explícitamente para el v-model
  ubigeo_pro: '',
  ubigeo_com: '',
  // Otros
  sexo: '',
  discapacidad_id: '',
  fecha_nac: '',
  direccion: '',
  telefono: '',     // <- al backend se envían SOLO 9 dígitos
})

/* =========================
   Estado auxiliar
========================= */
const provincias = ref([])
const distritos  = ref([])

const documentoError = ref('')
const telefonoError  = ref('')

/* =========================
   Teléfono: 9 dígitos crudos, sin +51
========================= */
const telefonoDigits = ref('')

const telefonoModel = computed({
  get() {
    return telefonoDigits.value
  },
  set(v) {
    const digits = (v || '').replace(/\D/g, '').slice(0, 9)
    telefonoDigits.value = digits
    form.telefono = digits
    telefonoError.value = digits && digits.length !== 9
      ? 'El teléfono debe tener exactamente 9 dígitos'
      : ''
  }
})

/* =========================
   Validación de documento
========================= */
watch([() => form.tipo_doc, () => form.num_doc], ([tipo, documento]) => {
  if (!tipo || !documento) {
    documentoError.value = ''
    return
  }
  const soloNumeros = documento.replace(/\D/g, '')

  switch (tipo) {
    case 'DNI':
      if (soloNumeros.length !== 8) {
        documentoError.value = 'El DNI debe tener exactamente 8 dígitos'
      } else {
        documentoError.value = ''
        if (form.num_doc !== soloNumeros) form.num_doc = soloNumeros
      }
      break
    case 'CE':
      if (soloNumeros.length < 9 || soloNumeros.length > 12) {
        documentoError.value = 'El Carné de Extranjería debe tener entre 9 y 12 dígitos'
      } else {
        documentoError.value = ''
        if (form.num_doc !== soloNumeros) form.num_doc = soloNumeros
      }
      break
    case 'Pasaporte':
      if (documento.length < 6 || documento.length > 12) {
        documentoError.value = 'El Pasaporte debe tener entre 6 y 12 caracteres'
      } else {
        documentoError.value = ''
      }
      break
    default:
      documentoError.value = ''
  }
})
// Longitud máxima por tipo de documento
const docMaxLen = computed(() => {
  switch (form.tipo_doc) {
    case 'DNI':       return 8
    case 'CE':        return 12   // CE: 9–12, usamos 12 como techo
    case 'Pasaporte': return 12   // Pasaporte: 6–12, usamos 12 como techo
    default:          return 12
  }
})

/** Normaliza y corta el valor del documento según el tipo */
const handleDocInput = (e) => {
  let v = e.target.value ?? ''
  if (form.tipo_doc === 'Pasaporte') {
    // alfanumérico en mayúsculas
    v = v.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, docMaxLen.value)
  } else {
    // DNI/CE solo números
    v = v.replace(/\D/g, '').slice(0, docMaxLen.value)
  }
  if (v !== form.num_doc) form.num_doc = v
}


// Al cambiar tipo de doc, limpia el campo
watch(() => form.tipo_doc, () => {
  form.num_doc = ''
  documentoError.value = ''
})

/* =========================
   Helpers de carga (axios o fetch)
========================= */
const getJSON = async (url) => {
  if (typeof window !== 'undefined' && window.axios) {
    const { data } = await window.axios.get(url)
    return data
  }
  const res = await fetch(url)
  return res.json()
}

/* =========================
   Cascada de UBIGEO
========================= */
watch(() => form.ubigeo_dep, async (ubigeoDep) => {
  provincias.value = []
  distritos.value  = []
  form.ubigeo_pro  = ''
  form.ubigeo_com  = ''
  if (!ubigeoDep) return

  const url = typeof route === 'function'
    ? route('personas.provincias', { ubigeoDep })
    : `/personas/provincias/${ubigeoDep}`

  try {
    provincias.value = await getJSON(url) // [{ubigeo_pro,text}]
  } catch (e) {
    console.error('Error al cargar provincias:', e)
  }
})

watch(() => form.ubigeo_pro, async (ubigeoPro) => {
  distritos.value = []
  form.ubigeo_com = ''
  if (!ubigeoPro) return

  const url = typeof route === 'function'
    ? route('personas.distritos', { ubigeoPro })
    : `/personas/distritos/${ubigeoPro}`

  try {
    distritos.value = await getJSON(url) // [{ubigeo_com,text}]
  } catch (e) {
    console.error('Error al cargar distritos:', e)
  }
})

/* =========================
   Submit
========================= */
const puedeEnviar = computed(() =>
  !documentoError.value && !telefonoError.value && !form.processing
)

const submit = () => {
  Swal.fire({
    title: '¿Deseas guardar esta persona?',
    text: 'Confirma que los datos son correctos.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, guardar',
    cancelButtonText: 'Cancelar',
  }).then((result) => {
    if (result.isConfirmed) {
      form.post(route('personas.store'), {
        onStart: () => Swal.showLoading(),
        onSuccess: () =>
          Swal.fire({ icon: 'success', title: 'Guardado', timer: 1500, showConfirmButton: false }),
        onError: (errors) => {
          if (errors.num_doc) {
            Swal.fire({ icon: 'error', title: 'Número de documento duplicado', text: errors.num_doc })
          } else {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Verifica los campos del formulario.' })
          }
        },
      })
    }
  })
}
</script>

<template>
  <AppLayout title="Crear Persona">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Registrar Nueva Persona
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <form @submit.prevent="submit" autocomplete="off">
            <!-- Datos Personales -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Datos Personales</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input v-model="form.nombre" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-600">{{ form.errors.nombre }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Primer Apellido *</label>
                    <input v-model="form.pri_ape" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    <p v-if="form.errors.pri_ape" class="mt-1 text-sm text-red-600">{{ form.errors.pri_ape }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Segundo Apellido</label>
                    <input v-model="form.seg_ape" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                  </div>
                </div>
              </div>

              <!-- Documentación -->
              <div class="md:col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Documentación</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Documento *</label>
                    <select v-model="form.tipo_doc" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                      <option value="" disabled>Seleccione tipo</option>
                      <option value="DNI">DNI</option>
                      <option value="CE">Carné de Extranjería</option>
                      <option value="Pasaporte">Pasaporte</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número Documento *</label>
                    <input

                      v-model="form.num_doc"
                      @input="handleDocInput"
                      :maxlength="docMaxLen"
                      :inputmode="form.tipo_doc === 'Pasaporte' ? 'text' : 'numeric'"
                      :placeholder="form.tipo_doc === 'DNI'
                                      ? '12345678'
                                      : form.tipo_doc === 'CE'
                                        ? '123456789…'
                                        : form.tipo_doc === 'Pasaporte'
                                          ? 'AB123456'
                                          : 'Seleccione tipo primero'"
                      :class="{
                        'w-full px-4 py-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500': true, 
                        'border-red-300 focus:border-red-500 focus:ring-red-500': documentoError || form.errors.num_doc, 
                        'border-gray-300': !documentoError && !form.errors.num_doc
                      }"
                      :disabled="!form.tipo_doc"
                      required
                    />

                    <p v-if="documentoError" class="mt-1 text-sm text-red-600">{{ documentoError }}</p>
                    <p v-else-if="form.errors.num_doc" class="mt-1 text-sm text-red-600">{{ form.errors.num_doc }}</p>
                    <p v-else-if="form.tipo_doc" class="mt-1 text-xs text-gray-500">
                      {{
                        form.tipo_doc === 'DNI'
                          ? '8 dígitos exactos'
                          : form.tipo_doc === 'CE'
                            ? '9-12 dígitos'
                            : '6-12 caracteres (letras y números)'
                      }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Ubicación -->
              <div class="md:col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Ubicación</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Departamento *</label>
                    <select v-model="form.ubigeo_dep" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                      <option value="" disabled>Seleccione departamento</option>
                      <option v-for="d in departamentos" :key="d.ubigeo_dep" :value="d.ubigeo_dep">
                        {{ d.text }}
                      </option>
                    </select>
                    <p v-if="form.errors.ubigeo_dep" class="mt-1 text-sm text-red-600">{{ form.errors.ubigeo_dep }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Provincia *</label>
                    <select v-model="form.ubigeo_pro" :disabled="!form.ubigeo_dep" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed" required>
                      <option value="" disabled>Seleccione provincia</option>
                      <option v-for="p in provincias" :key="p.ubigeo_pro" :value="p.ubigeo_pro">
                        {{ p.text }}
                      </option>
                    </select>
                    <p v-if="form.errors.ubigeo_pro" class="mt-1 text-sm text-red-600">{{ form.errors.ubigeo_pro }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Distrito *</label>
                    <select v-model="form.ubigeo_com" :disabled="!form.ubigeo_pro" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed" required>
                      <option value="" disabled>Seleccione distrito</option>
                      <option v-for="di in distritos" :key="di.ubigeo_com" :value="di.ubigeo_com">
                        {{ di.text }}
                      </option>
                    </select>
                    <p v-if="form.errors.ubigeo_com" class="mt-1 text-sm text-red-600">{{ form.errors.ubigeo_com }}</p>
                  </div>
                </div>
              </div>

              <!-- Información Adicional -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Información Adicional</h3>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sexo *</label>
                    <select v-model="form.sexo" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                      <option value="" disabled>Seleccione sexo</option>
                      <option value="M">Masculino</option>
                      <option value="F">Femenino</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discapacidad</label>
                    <select v-model="form.discapacidad_id" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                      <option value="" disabled>Seleccionar</option>
                      <option v-for="d in discapacidads" :key="d.id" :value="d.id">
                        {{ d.text }}
                      </option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Nacimiento *</label>
                    <input v-model="form.fecha_nac" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    <p v-if="form.errors.fecha_nac" class="mt-1 text-sm text-red-600">{{ form.errors.fecha_nac }}</p>
                  </div>
                </div>
              </div>

              <!-- Contacto -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Datos de Contacto</h3>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <input v-model="form.direccion" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input
                      v-model="telefonoModel"
                      type="tel"
                      inputmode="numeric"
                      pattern="\d{9}"
                      autocomplete="off"
                      placeholder="9 dígitos (+51)"
                      @keydown.enter.prevent
                      :class="{
                        'w-full px-4 py-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500': true,
                        'border-red-300 focus:border-red-500 focus:ring-red-500': telefonoError || form.errors.telefono,
                        'border-gray-300': !telefonoError && !form.errors.telefono
                      }"
                    />
                    <p v-if="telefonoError" class="mt-1 text-sm text-red-600">{{ telefonoError }}</p>
                    <p v-else-if="form.errors.telefono" class="mt-1 text-sm text-red-600">{{ form.errors.telefono }}</p>
                    <p v-else class="mt-1 text-xs text-gray-500">Ingresa solo 9 dígitos (Ej: 987654321)</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end mt-8 space-x-4">
              <Link :href="route('personas.index')" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancelar
              </Link>

              <button
                type="submit"
                :disabled="!puedeEnviar"
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="form.processing">
                  <i class="fas fa-spinner fa-spin mr-2"></i> Registrando...
                </span>
                <span v-else>
                  <i class="fas fa-save mr-2"></i> Registrar
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
