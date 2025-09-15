<script setup>
import { reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  role: Object,          // {id, nombre, descripcion, is_protected}
  selectedIds: Array,    // ids de permisos actuales
  permissions: Array,    // [{grupo, items:[{id,nombre,descripcion}]}]
})

const form = reactive({
  nombre: props.role.nombre,
  descripcion: props.role.descripcion ?? '',
  permission_ids: [...props.selectedIds],
})

const toggle = (id) => {
  const i = form.permission_ids.indexOf(id)
  if (i === -1) form.permission_ids.push(id)
  else form.permission_ids.splice(i, 1)
}

const isChecked = (id) => form.permission_ids.includes(id)

const submit = () => {
  router.put(route('roles.update', props.role.id), form)
}
</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold mb-4">Editar rol: {{ role.nombre }}</h1>

    <div class="grid md:grid-cols-2 gap-6">
      <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-3">Datos básicos</h2>
        <div class="mb-3">
          <label class="block text-sm mb-1">Nombre *</label>
          <input v-model="form.nombre" class="w-full border rounded px-3 py-2" />
        </div>
        <div class="mb-3">
          <label class="block text-sm mb-1">Descripción</label>
          <textarea v-model="form.descripcion" class="w-full border rounded px-3 py-2"></textarea>
        </div>
        <div class="text-xs text-gray-500">
          Protegido: <b>{{ role.is_protected ? 'Sí' : 'No' }}</b>
        </div>
      </div>

      <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-3">Permisos</h2>

        <div class="space-y-4 max-h-[480px] overflow-auto pr-2">
          <div v-for="group in permissions" :key="group.grupo" class="border rounded">
            <div class="px-3 py-2 bg-gray-50 font-medium">
              {{ group.grupo }}
            </div>
            <div class="p-3 grid md:grid-cols-2 gap-2">
              <label v-for="p in group.items" :key="p.id" class="inline-flex items-start gap-2">
                <input type="checkbox" :checked="isChecked(p.id)" @change="toggle(p.id)" />
                <span>
                  <div class="font-medium">{{ p.nombre }}</div>
                  <div class="text-xs text-gray-500">{{ p.descripcion ?? '' }}</div>
                </span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-4 flex gap-2">
      <button class="px-3 py-2 bg-blue-600 text-white rounded" @click="submit">Guardar</button>
      <button class="px-3 py-2 bg-gray-100 rounded" @click="$inertia.visit(route('roles.index'))">Volver</button>
    </div>
  </div>
</template>
