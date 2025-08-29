<script setup>
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  tiposDocumento: {
    type: Array,
    default: () => []
  },
  departamentos: {
    type: Array,
    default: () => []
  },
  sexos: {
    type: Array,
    default: () => []
  },
  discapacidades: {
    type: Array,
    default: () => []
  }
})

const form = useForm({
  nombre: '',
  pri_ape: '',
  seg_ape: '',
  tipo_doc: '',
  num_doc: '',
  ubigeo_com: '',
  sexo: '',
  discapacidad_id: '',
  fecha_nac: '',
  direccion: '',
  telefono: '',
})

// Datos para provincias y distritos
const provincias = ref([])
const distritos = ref([])

// Cargar provincias cuando se selecciona un departamento
watch(() => form.ubigeo_dep, async (ubigeoDep) => {
  if (ubigeoDep) {
    try {
      const response = await axios.get(route('personas.provincias', { ubigeoDep }))
      provincias.value = response.data
    } catch (error) {
      console.error('Error al cargar provincias:', error)
    }
    form.ubigeo_pro = null
    form.ubigeo_com = null
  }
})

// Cargar distritos cuando se selecciona una provincia
watch(() => form.ubigeo_pro, async (ubigeoPro) => {
  if (ubigeoPro) {
    try {
      const response = await axios.get(route('personas.distritos', { ubigeoPro }))
      distritos.value = response.data
    } catch (error) {
      console.error('Error al cargar distritos:', error)
    }
    form.ubigeo_com = null
  }
})

// Generar ubigeo_com cuando se selecciona un distrito
watch(() => form.ubigeo_com, (newVal) => {

})

const submit = () => {
  form.post(route('personas.store'), {
    onSuccess: () => {
      form.reset()
      setTimeout(() => {
        router.visit(route('personas.index'))
      }, 1500)
    },
    onError: (errors) => {
      console.error('Error al crear la persona:', errors)
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
          <form @submit.prevent="submit">
            <!-- Sección 1: Datos Personales -->
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

              <!-- Sección 2: Documentación -->
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
                    <input v-model="form.num_doc" type="text" :class="{'w-full px-4 py-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500': true, 'border-red-300': form.errors.num_doc, 'border-gray-300': !form.errors.num_doc}" required>
                    <p v-if="form.errors.num_doc" class="mt-1 text-sm text-red-600">{{ form.errors.num_doc }}</p>
                  </div>
                </div>
              </div>

              <!-- Sección 3: Ubicación -->
              <div class="md:col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Ubicación</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Departamento *</label>
                    <select v-model="form.ubigeo_dep" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                      <option value="" disabled>Seleccione departamento</option>
                      <option v-for="departamento in departamentos" :key="departamento.ubigeo_dep" :value="departamento.ubigeo_dep">
                        {{ departamento.text }}
                      </option>
                    </select>
                    <p v-if="form.errors.ubigeo_dep" class="mt-1 text-sm text-red-600">{{ form.errors.ubigeo_dep }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Provincia *</label>
                    <select v-model="form.ubigeo_pro" :disabled="!form.ubigeo_dep" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed" required>
                      <option value="" disabled>Seleccione provincia</option>
                      <option v-for="provincia in provincias" :key="provincia.ubigeo_pro" :value="provincia.ubigeo_pro">
                        {{ provincia.text }}
                      </option>
                    </select>
                    <p v-if="form.errors.ubigeo_pro" class="mt-1 text-sm text-red-600">{{ form.errors.ubigeo_pro }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Distrito *</label>
                    <select v-model="form.ubigeo_com" :disabled="!form.ubigeo_pro" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed" required>
                      <option value="" disabled>Seleccione distrito</option>
                      <option v-for="distrito in distritos" :key="distrito.ubigeo_com" :value="distrito.ubigeo_com">
                        {{ distrito.text }}
                      </option>
                    </select>
                    <p v-if="form.errors.ubigeo_com" class="mt-1 text-sm text-red-600">{{ form.errors.ubigeo_com }}</p>
                  </div>
                </div>
              </div>

              <!-- Sección 4: Información Adicional -->
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
                      <option v-for="discapacidad in discapacidades" :key="discapacidad.id" :value="discapacidad.id">
                        {{ discapacidad.text }}
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

              <!-- Sección 5: Datos de Contacto -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Datos de Contacto</h3>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <input v-model="form.direccion" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input v-model="form.telefono" type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <p v-if="form.errors.telefono" class="mt-1 text-sm text-red-600">{{ form.errors.telefono }}</p>
                  </div>
                  
                </div>
              </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-end mt-8 space-x-4">
              <Link :href="route('personas.index')" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancelar
              </Link>
              <button type="submit" :disabled="form.processing" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
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