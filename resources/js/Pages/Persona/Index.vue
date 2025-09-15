<!-- resources/js/Pages/Persona/Index.vue -->
<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import Swal from 'sweetalert2'

// Heroicons (solid)
import {
  PlusIcon,
  XMarkIcon,
  PhoneIcon,
  EyeIcon,
  PencilSquareIcon,
  TrashIcon,
} from '@heroicons/vue/24/solid'

const props = defineProps({
  personas: { type: Object, required: true },
  filters:  { type: Object, default: () => ({}) },
  ubigeos:  { type: Array,  default: () => [] }
})

/* ========= permisos desde Inertia ========= */
const page = usePage()
const canList = computed(() => page?.props?.auth?.can ?? [])

/** Normaliza un nombre de permiso a ambas variantes ES/EN y
 *  devuelve true si el usuario tiene alguno de ellos.
 *  Ej.: has('personas.create') o has('personas.crear')
 */
const has = (...perms) => {
  const alt = (p) =>
    p.replace('.create', '.crear')
     .replace('.edit', '.editar')
     .replace('.delete', '.eliminar')
  return perms.some(p => canList.value.includes(p) || canList.value.includes(alt(p)))
}

/* ========= filtros de búsqueda ========= */
const filters = ref({
  search:    props.filters.search    || '',
  tipo_doc:  props.filters.tipo_doc  || ''
})

/* ========= modal ========= */
const showModal = ref(false)
const selectedPerson = ref(null)

const formatSexo = (sexo) => {
  switch (sexo) {
    case 'M': return 'Masculino'
    case 'F': return 'Femenino'
    default:  return sexo || 'N/A'
  }
}

const openModal = (persona) => {
  selectedPerson.value = JSON.parse(JSON.stringify(persona))
  showModal.value = true
}
const closeModal = () => { showModal.value = false; selectedPerson.value = null }

/* ========= acciones ========= */
const search = () => {
  router.get(route('personas.index'), filters.value, {
    preserveState: true,
    replace: true,
    only: ['personas'],
  })
}

const confirmDelete = (persona) => {
  const nombre = persona?.nombre_completo ?? 'esta persona'
  Swal.fire({
    title: '¿Eliminar a ' + nombre + '?',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true,
  }).then((result) => {
    if (!result.isConfirmed) return
    router.delete(route('personas.destroy', persona.id), {
      preserveScroll: true,
      onStart: () => Swal.showLoading(),
      onSuccess: () => {
        Swal.fire({ icon: 'success', title: 'Eliminado', text: 'La persona fue eliminada.', timer: 1500, showConfirmButton: false })
      },
      onError: () => {
        Swal.fire({ icon: 'error', title: 'No se pudo eliminar', text: 'Revisa dependencias o permisos.' })
      },
    })
  })
}

/* ========= limpiar filtros + debounce ========= */
const clearFilters = () => {
  filters.value = { search: '', tipo_doc: '', ubigeo_id: '' }
}

let searchTimeout = null
watch(filters, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(search, 300)
}, { deep: true })

const hasActiveFilters = computed(() =>
  !!(filters.value.search || filters.value.tipo_doc || filters.value.ubigeo_id)
)

/* ========= visibilidad por permisos ========= */
const canCreate = computed(() => has('personas.create', 'personas.crear'))
const canView   = computed(() => has('personas.view'))          // “view” lo mantuviste en inglés
const canEdit   = computed(() => has('personas.edit', 'personas.editar'))
const canDelete = computed(() => has('personas.delete', 'personas.eliminar'))

// mostrar encabezado/columna “Acciones” solo si hay algo que mostrar
const showActionsColumn = computed(() => canView.value || canEdit.value || canDelete.value)
</script>

<template>
  <AppLayout title="Listado de Personas">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Gestión de personas
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

          <!-- Encabezado -->
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Listado de personas</h1>
              <p class="text-sm text-gray-600 mt-1">
                Total: {{ personas.total || 0 }} persona(s)
              </p>
            </div>

            <!-- NUEVA PERSONA (solo si tiene personas.create/crear) -->
            <Link
              v-if="canCreate"
              :href="route('personas.create')"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-700 transition"
            >
              <PlusIcon class="w-5 h-5 mr-2" />
              Nueva Persona
            </Link>
          </div>

          <!-- Filtros -->
          <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Búsqueda</label>
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="Nombre, apellido o documento..."
                  class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de documento</label>
                <select
                  v-model="filters.tipo_doc"
                  class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="">Todos</option>
                  <option value="DNI">DNI</option>
                  <option value="CE">Carné Extranjería</option>
                  <option value="Pasaporte">Pasaporte</option>
                </select>
              </div>

              <div class="flex items-end">
                <button
                  v-if="hasActiveFilters"
                  @click="clearFilters"
                  class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition"
                >
                  <XMarkIcon class="w-5 h-5" />
                  Limpiar
                </button>
              </div>
            </div>
          </div>

          <!-- Tabla -->
          <div class="overflow-x-auto bg-white shadow-sm rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nombre y edad
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Documento
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ubicación
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Contacto
                  </th>
                  <th
                    v-if="showActionsColumn"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Acciones
                  </th>
                </tr>
              </thead>

              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="persona in personas.data"
                  :key="persona.id"
                  class="hover:bg-gray-50 transition-colors"
                >
                  <!-- Nombre y edad -->
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ persona.nombre_completo }}</div>
                    <div class="text-sm text-gray-500">{{ persona.fecha_y_edad }}</div>
                  </td>

                  <!-- Documento -->
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ persona.tipo_doc }}: {{ persona.num_doc }}
                    </div>
                    <div class="text-xs text-gray-500">
                      {{ formatSexo(persona.sexo) }}
                    </div>
                  </td>

                  <!-- Ubicación -->
                  <td class="px-6 py-4">
                    <div v-if="persona.ubigeo" class="text-sm text-gray-900">
                      {{ persona.ubigeo.departamento }}
                    </div>
                    <div v-if="persona.ubigeo" class="text-sm text-gray-500">
                      {{ persona.ubigeo.provincia }}, {{ persona.ubigeo.distrito }}
                    </div>
                    <div v-else class="text-sm text-gray-400">Sin ubicación</div>
                  </td>

                  <!-- Contacto -->
                  <td class="px-6 py-4">
                    <div v-if="persona.telefono" class="flex items-center text-sm text-gray-900">
                      <PhoneIcon class="w-4 h-4 mr-1" />
                      {{ persona.telefono }}
                    </div>
                    <div v-if="!persona.telefono && !persona.email" class="text-sm text-gray-400">
                      Sin contacto
                    </div>
                  </td>

                  <!-- Acciones (según permiso) -->
                  <td v-if="showActionsColumn" class="px-6 py-4">
                    <div class="flex items-center gap-2">
                      <!-- Ver -->
                      <button
                        v-if="canView"
                        @click="openModal(persona)"
                        class="p-2 text-green-600 hover:text-white hover:bg-green-600 rounded-full transition"
                        title="Ver"
                        aria-label="Ver"
                      >
                        <EyeIcon class="w-5 h-5" />
                      </button>

                      <!-- Editar -->
                      <Link
                        v-if="canEdit"
                        :href="route('personas.edit', persona.id)"
                        class="p-2 text-blue-600 hover:text-white hover:bg-blue-600 rounded-full transition"
                        title="Editar"
                        aria-label="Editar"
                      >
                        <PencilSquareIcon class="w-5 h-5" />
                      </Link>

                      <!-- Eliminar -->
                      <button
                        v-if="canDelete"
                        @click="confirmDelete(persona)"
                        class="p-2 text-red-600 hover:text-white hover:bg-red-600 rounded-full transition"
                        title="Eliminar"
                        aria-label="Eliminar"
                      >
                        <TrashIcon class="w-5 h-5" />
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="personas.data.length === 0">
                  <td :colspan="showActionsColumn ? 5 : 4" class="px-6 py-8 text-center text-gray-500">
                    No se encontraron personas
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div class="mt-6" v-if="personas.links && personas.data.length > 0">
            <Pagination
              :links="personas.links"
              :from="personas.from"
              :to="personas.to"
              :total="personas.total"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Detalle (para botón Ver) -->
    <teleport to="body">
      <div
        v-if="showModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
        @click.self="closeModal"
        aria-modal="true"
        role="dialog"
      >
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-xl font-bold text-gray-900">Detalles de Persona</h3>
              <button @click="closeModal" class="text-gray-500 hover:text-gray-700" aria-label="Cerrar">
                <XMarkIcon class="w-6 h-6" />
              </button>
            </div>

            <div class="space-y-4" v-if="selectedPerson">
              <div>
                <h4 class="text-lg font-semibold text-gray-800 mb-2">Datos personales</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm text-gray-500">Nombre completo</p>
                    <p class="font-medium">
                      {{ selectedPerson.nombre }} {{ selectedPerson.pri_ape }} {{ selectedPerson.seg_ape || '' }}
                    </p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Documento</p>
                    <p class="font-medium">{{ selectedPerson.tipo_doc }}: {{ selectedPerson.num_doc }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Fecha Nacimiento y Edad</p>
                    <p class="font-medium">{{ selectedPerson.fecha_y_edad }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Sexo</p>
                    <p class="font-medium">{{ formatSexo(selectedPerson.sexo) }}</p>
                  </div>
                </div>
              </div>

              <div>
                <h4 class="text-lg font-semibold text-gray-800 mb-2">Ubicación</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <p class="text-sm text-gray-500">Departamento</p>
                    <p class="font-medium">{{ selectedPerson.ubigeo?.departamento || 'N/A' }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Provincia</p>
                    <p class="font-medium">{{ selectedPerson.ubigeo?.provincia || 'N/A' }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Distrito</p>
                    <p class="font-medium">{{ selectedPerson.ubigeo?.distrito || 'N/A' }}</p>
                  </div>
                  <div class="md:col-span-3">
                    <p class="text-sm text-gray-500">Dirección</p>
                    <p class="font-medium">{{ selectedPerson.direccion || 'N/A' }}</p>
                  </div>
                </div>
              </div>

              <div>
                <h4 class="text-lg font-semibold text-gray-800 mb-2">Contacto</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm text-gray-500">Teléfono</p>
                    <p class="font-medium">{{ selectedPerson.telefono || 'N/A' }}</p>
                  </div>
                </div>
              </div>

              <div>
                <h4 class="text-lg font-semibold text-gray-800 mb-2">Discapacidad</h4>
                <p class="font-medium">
                  {{ selectedPerson.discapacidad ? selectedPerson.discapacidad.nombre_discapacidad : 'Ninguna' }}
                </p>
              </div>
            </div>

            <div class="mt-6 flex justify-end">
              <button
                @click="closeModal"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition"
              >
                Cerrar
              </button>
            </div>
          </div>
        </div>
      </div>
    </teleport>
  </AppLayout>
</template>
