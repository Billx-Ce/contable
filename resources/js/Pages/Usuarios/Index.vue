<script setup>
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  users: { type: Object, required: true }
})
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
                </tr>
                <tr v-if="users.data.length === 0">
                  <td colspan="4" class="px-6 py-8 text-center text-gray-500">Sin usuarios</td>
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
