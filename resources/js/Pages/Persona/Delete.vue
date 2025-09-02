<!-- resources/js/Pages/Persona/ConfirmDelete.vue -->
<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  persona: { type: Object, required: true }
})

const deletePersona = () => {
  const nombre = props.persona?.nombre_completo || 'esta persona'
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

    router.delete(route('personas.destroy', props.persona.id), {
      preserveScroll: true,
      onStart: () => Swal.showLoading(),
      onSuccess: () => {
        Swal.fire({
          icon: 'success',
          title: 'Eliminado',
          text: 'La persona fue eliminada correctamente.',
          timer: 1500,
          showConfirmButton: false,
        })
      },
      onError: () => {
        Swal.fire({
          icon: 'error',
          title: 'No se pudo eliminar',
          text: 'Revisa dependencias o permisos (p. ej., usuarios vinculados).',
        })
      },
    })
  })
}
</script>

<template>
  <AppLayout title="Confirmar Eliminación">
    <div class="max-w-md mx-auto p-6 bg-white rounded-lg shadow">
      <h1 class="text-xl font-bold text-red-600 mb-4">Confirmar Eliminación</h1>

      <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500">
        <p>
          ¿Estás seguro de eliminar a
          <strong>{{ persona.nombre_completo }}</strong>?
        </p>
        <p class="text-sm text-gray-600 mt-1">Esta acción no se puede deshacer.</p>
      </div>

      <div class="flex justify-end space-x-3">
        <Link
          :href="route('personas.index')"
          class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-100"
        >
          Cancelar
        </Link>

        <button
          @click="deletePersona"
          class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
        >
          Eliminar Definitivamente
        </button>
      </div>
    </div>
  </AppLayout>
</template>