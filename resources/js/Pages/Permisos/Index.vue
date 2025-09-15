<script setup>
import { Link, router } from '@inertiajs/vue3'

defineProps({
  permissions: Object, // paginator
})

const destroyPermission = (id) => {
  if (!confirm('¿Eliminar este permiso?')) return
  router.delete(route('permisos.destroy', id))
}
</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold mb-4">Permisos</h1>

    <div class="mb-3">
      <Link class="inline-block px-3 py-2 bg-blue-600 text-white rounded" :href="route('permisos.create')">
        Nuevo permiso
      </Link>
    </div>

    <div class="bg-white shadow rounded p-4">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b">
            <th class="text-left p-2">Nombre</th>
            <th class="text-left p-2">Grupo</th>
            <th class="text-left p-2">Descripción</th>
            <th class="text-left p-2">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in permissions.data" :key="p.id" class="border-b">
            <td class="p-2">{{ p.nombre }}</td>
            <td class="p-2">{{ p.grupo ?? '—' }}</td>
            <td class="p-2">{{ p.descripcion ?? '—' }}</td>
            <td class="p-2 space-x-2">
              <Link class="text-blue-600 hover:underline" :href="route('permisos.edit', p.id)">Editar</Link>
              <button class="text-red-600 hover:underline" @click="destroyPermission(p.id)">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="mt-4 flex gap-2 flex-wrap">
        <button
          v-for="(link, i) in permissions.links"
          :key="i"
          class="px-2 py-1 rounded"
          :class="link.active ? 'bg-gray-800 text-white' : 'bg-gray-100'"
          v-html="link.label"
          @click="link.url && router.visit(link.url)"
        />
      </div>
    </div>
  </div>
</template>
