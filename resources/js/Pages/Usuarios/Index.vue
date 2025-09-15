<script setup>
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import Swal from 'sweetalert2'
import { computed } from 'vue'
import {
  PencilSquareIcon,
  TrashIcon,
  ShieldCheckIcon, // 👈 icono para “Asignar roles”
} from '@heroicons/vue/24/solid'

const props = defineProps({
  users: { type: Object, required: true },
})

const page = usePage()
const auth = computed(() => page?.props?.auth ?? {})

// visible si es superadmin o tiene permiso roles.administrar
const canAssignRoles = computed(() =>
  !!(auth.value?.isSuperadmin ||
     auth.value?.user?.is_admin ||
     (auth.value?.can ?? []).includes('roles.administrar'))
)

const form = useForm({})

const confirmarEliminacion = (user) => {
  Swal.fire({
    title: '¿Estás seguro?',
    text: `Se eliminará al usuario "${user.name}" y su acceso al sistema.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
  }).then((result) => {
    if (result.isConfirmed) {
      form.delete(route('usuarios.destroy', user.id), {
        onSuccess: () => Swal.fire('Eliminado', 'El usuario ha sido eliminado.', 'success'),
        onError:   () => Swal.fire('Error', 'No se pudo eliminar el usuario.', 'error'),
      })
    }
  })
}
</script>

<template>
  <Head title="Usuarios" />

  <AppLayout title="Usuarios">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Usuarios</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h1 class="text-2xl font-bold mb-4">Usuarios</h1>

          <div class="overflow-x-auto border rounded">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Identificación</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Correo electrónico</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Persona</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
              </thead>

              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="u in users.data" :key="u.id">
                  <td class="px-6 py-4 text-sm text-gray-900">{{ u.id }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900">{{ u.name }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900">{{ u.email }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    <span v-if="u.persona">{{ u.persona.nombre }}</span>
                    <span v-else class="text-gray-400">—</span>
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900 flex items-center gap-3">
                    <!-- Editar datos -->
                    <Link
                      :href="route('usuarios.edit', u.id)"
                      class="inline-flex items-center text-blue-600 hover:text-blue-800"
                      aria-label="Editar usuario"
                      title="Editar datos"
                    >
                      <PencilSquareIcon class="h-5 w-5" />
                    </Link>

                    <!-- Asignar roles (solo si puede administrar roles) -->
                    <Link
                      v-if="canAssignRoles"
                      :href="route('usuarios.roles.edit', u.id)"
                      class="inline-flex items-center text-emerald-600 hover:text-emerald-800"
                      aria-label="Asignar roles"
                      title="Asignar roles"
                    >
                      <ShieldCheckIcon class="h-5 w-5" />
                    </Link>

                    <!-- Eliminar (opcional) -->
                    <button
                      v-if="false"  <!-- cambia a true si habilitas eliminar -->
                      @click="confirmarEliminacion(u)"
                      class="inline-flex items-center text-red-600 hover:text-red-800"
                      aria-label="Eliminar usuario"
                      title="Eliminar usuario"
                    >
                      <TrashIcon class="h-5 w-5" />
                    </button>
                  </td>
                </tr>

                <tr v-if="users.data.length === 0">
                  <td colspan="5" class="px-6 py-8 text-center text-gray-500">Sin usuarios</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-6" v-if="users.links && users.data.length > 0">
            <Pagination
              :links="users.links"
              :from="users.from"
              :to="users.to"
              :total="users.total"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
