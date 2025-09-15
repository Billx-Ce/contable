<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  user: { type: Object, required: true },
  roles: { type: Array, required: true },          // [{id,nombre,descripcion,is_protected}]
  currentRoleIds: { type: Array, required: true }, // [1,2,...]
})

const form = useForm({
  roles: [...props.currentRoleIds], // inicial
})

const toggle = (id) => {
  const i = form.roles.indexOf(id)
  if (i === -1) form.roles.push(id)
  else form.roles.splice(i, 1)
}

const submit = () => {
  form.put(route('usuarios.roles.update', props.user.id))
}
</script>

<template>
  <Head title="Asignar roles" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Asignar roles: {{ user.name }}
        </h2>
        <Link :href="route('usuarios.index')" class="text-sm text-gray-600 hover:underline">
          ← Volver
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
          <div class="border-b border-gray-200 px-6 py-4">
            <p class="text-sm text-gray-600">
              Selecciona los roles que tendrá el usuario. (Los cambios reemplazan los roles actuales.)
            </p>
          </div>

          <div class="px-6 py-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <label
                v-for="r in roles"
                :key="r.id"
                class="flex cursor-pointer items-start gap-3 rounded-md border p-3 hover:bg-gray-50"
              >
                <input
                  type="checkbox"
                  class="mt-1"
                  :value="r.id"
                  :checked="form.roles.includes(r.id)"
                  @change="toggle(r.id)"
                />
                <div>
                  <div class="font-medium text-gray-800">
                    {{ r.nombre }}
                    <span
                      v-if="r.is_protected"
                      class="ml-2 rounded bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700"
                    >protegido</span>
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ r.descripcion || '—' }}
                  </div>
                </div>
              </label>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 border-t px-6 py-4">
            <button
              @click="submit"
              :disabled="form.processing"
              class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
            >
              Guardar cambios
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
