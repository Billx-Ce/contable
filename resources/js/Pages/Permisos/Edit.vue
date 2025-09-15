<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  permission: Object, // {id,nombre,grupo,descripcion}
})

const form = reactive({
  nombre: props.permission.nombre,
  grupo: props.permission.grupo ?? '',
  descripcion: props.permission.descripcion ?? '',
})

const submit = () => {
  router.put(route('permisos.update', props.permission.id), form)
}
</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold mb-4">Editar permiso</h1>

    <div class="bg-white p-4 rounded shadow max-w-xl">
      <div class="mb-3">
        <label class="block text-sm mb-1">Nombre *</label>
        <input v-model="form.nombre" class="w-full border rounded px-3 py-2" />
      </div>
      <div class="mb-3">
        <label class="block text-sm mb-1">Grupo</label>
        <input v-model="form.grupo" class="w-full border rounded px-3 py-2" />
      </div>
      <div class="mb-3">
        <label class="block text-sm mb-1">Descripción</label>
        <textarea v-model="form.descripcion" class="w-full border rounded px-3 py-2"></textarea>
      </div>

      <div class="flex gap-2">
        <button class="px-3 py-2 bg-blue-600 text-white rounded" @click="submit">Guardar</button>
        <button class="px-3 py-2 bg-gray-100 rounded" @click="$inertia.visit(route('permisos.index'))">Volver</button>
      </div>
    </div>
  </div>
</template>
