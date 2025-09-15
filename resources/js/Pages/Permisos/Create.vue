<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const form = reactive({
  nombre: '',
  grupo: '',
  descripcion: '',
})

const submit = () => {
  router.post(route('permisos.store'), form)
}
</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold mb-4">Nuevo permiso</h1>

    <div class="bg-white p-4 rounded shadow max-w-xl">
      <div class="mb-3">
        <label class="block text-sm mb-1">Nombre *</label>
        <input v-model="form.nombre" placeholder="personas.view" class="w-full border rounded px-3 py-2" />
      </div>
      <div class="mb-3">
        <label class="block text-sm mb-1">Grupo</label>
        <input v-model="form.grupo" placeholder="personas" class="w-full border rounded px-3 py-2" />
      </div>
      <div class="mb-3">
        <label class="block text-sm mb-1">Descripción</label>
        <textarea v-model="form.descripcion" class="w-full border rounded px-3 py-2"></textarea>
      </div>

      <div class="flex gap-2">
        <button class="px-3 py-2 bg-blue-600 text-white rounded" @click="submit">Guardar</button>
        <button class="px-3 py-2 bg-gray-100 rounded" @click="$inertia.visit(route('permisos.index'))">Cancelar</button>
      </div>
    </div>
  </div>
</template>
