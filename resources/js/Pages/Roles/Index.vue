<script setup>
import { Link, router } from '@inertiajs/vue3'

defineProps({
  roles: Object, // paginator
})

const destroyRole = (id) => {
  if (!confirm('¿Eliminar este rol?')) return
  router.delete(route('roles.destroy', id))
}
</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold mb-4">Roles</h1>

    <div class="mb-3">
      <Link class="inline-block px-3 py-2 bg-blue-600 text-white rounded" :href="route('roles.create')">
        Nuevo rol
      </Link>
    </div>

    <div class="bg-white shadow rounded p-4">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b">
            <th class="text-left p-2">Nombre</th>
            <th class="text-left p-2">Descripción</th>
            <th class="text-left p-2">Protegido</th>
            <th class="text-left p-2">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in roles.data" :key="r.id" class="border-b">
            <td class="p-2">{{ r.nombre }}</td>
            <td class="p-2">{{ r.descripcion ?? '—' }}</td>
            <td class="p-2">
              <span class="px-2 py-1 rounded text-xs" :class="r.is_protected ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'">
                {{ r.is_protected ? 'Sí' : 'No' }}
              </span>
            </td>
            <td class="p-2 space-x-2">
              <Link class="text-blue-600 hover:underline" :href="route('roles.edit', r.id)">Editar</Link>
              <button
                v-if="!r.is_protected"
                class="text-red-600 hover:underline"
                @click="destroyRole(r.id)"
              >
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- paginación -->
      <div class="mt-4 flex gap-2 flex-wrap">
        <button
          v-for="(link, i) in roles.links"
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
