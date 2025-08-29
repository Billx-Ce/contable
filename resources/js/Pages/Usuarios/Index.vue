<script setup>
import { Head, Link } from '@inertiajs/vue3'
defineProps({ users: Object })
</script>

<template>
  <Head title="Usuarios" />

  <div class="py-6">
    <h1 class="text-2xl font-bold mb-4">Usuarios</h1>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-xl">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900/50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold">ID</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">Nombre</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">Email</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">Persona</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-for="u in users.data" :key="u.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
            <td class="px-4 py-2">{{ u.id }}</td>
            <td class="px-4 py-2">{{ u.name }}</td>
            <td class="px-4 py-2">{{ u.email }}</td>
            <td class="px-4 py-2">
              <span v-if="u.persona">
                {{ u.persona.nombre }} (ID: {{ u.persona.id }})
              </span>
              <span v-else class="text-gray-400">—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- paginación simple -->
    <div class="mt-4 flex gap-2">
      <Link v-if="users.prev_page_url" :href="users.prev_page_url" class="px-3 py-1 border rounded">Anterior</Link>
      <Link v-if="users.next_page_url" :href="users.next_page_url" class="px-3 py-1 border rounded">Siguiente</Link>
    </div>
  </div>
</template>
