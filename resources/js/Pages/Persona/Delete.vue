<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue';
//import { Inertia } from '@inertiajs/inertia';


const props = defineProps({
  persona: Object
})

const form = useForm({})

const deletePersona = () => {
  if (!props.persona?.id) {
    console.error("El ID de la persona no está definido.");
    return;
  }
  form.delete(route('personas.destroy', props.persona.id), {
    onFinish: () => {
      Inertia.visit(route('personas.index'));
    }
  });
};


const processing = form.processing
</script>
<template>
  <Layout>
    <div class="max-w-md mx-auto p-6 bg-white rounded-lg shadow">
      <h1 class="text-xl font-bold text-red-600 mb-4">Confirmar Eliminación</h1>
      
      <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500">
        <p>¿Estás seguro de eliminar a <strong>{{ persona.nombre_completo }}</strong>?</p>
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
          :disabled="processing"
        >
          Eliminar Definitivamente
        </button>
      </div>
    </div>
  </Layout>
</template>
