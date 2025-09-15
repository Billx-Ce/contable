<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  user: Object,
})

const profileForm = useForm({
  name: props.user.name,
  email: props.user.email,
})

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const submitProfile = () => {
  profileForm.put(route('usuarios.update', props.user.id), {
    onSuccess: () => profileForm.reset(),
  })
}

const submitPassword = () => {
  passwordForm.put(route('usuarios.password.update', props.user.id), {
    onSuccess: () => passwordForm.reset(),
  })
}
</script>

<template>
  <AppLayout title="Editar Usuario">
    <!-- Header con botón al lado -->
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Usuario</h2>
        <Link
          :href="route('usuarios.index')"
          class="px-4 py-1.5 bg-gray-300 text-gray-800 rounded-md text-sm font-medium hover:bg-gray-400"
        >
          Volver
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Información del Perfil -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h3 class="text-base font-semibold leading-7 text-gray-900">Información del Perfil</h3>
          <p class="mt-1 text-sm leading-6 text-gray-600">Actualiza el correo electrónico de la cuenta.</p>

          <form @submit.prevent="submitProfile" class="mt-6 grid grid-cols-1 gap-y-6">
            <!-- Persona Asociada -->
            <div>
              <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Persona Asociada</label>
              <input
                id="name"
                v-model="profileForm.name"
                type="text"
                readonly
                class="mt-2 block w-full bg-gray-100 cursor-not-allowed rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm sm:leading-6"
              />
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email</label>
              <input
                id="email"
                v-model="profileForm.email"
                type="email"
                required
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
              />
              <p v-if="profileForm.errors.email" class="mt-2 text-sm text-red-600">{{ profileForm.errors.email }}</p>
            </div>

            <!-- Guardar -->
            <div class="flex justify-end">
              <button
                type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md shadow-sm text-sm font-medium hover:bg-indigo-700"
              >
                Guardar
              </button>
            </div>
          </form>
        </div>

        <!-- Actualizar contraseña -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h3 class="text-base font-semibold leading-7 text-gray-900">Actualizar contraseña</h3>
          <p class="mt-1 text-sm leading-6 text-gray-600">Asegúrate de usar una contraseña larga y segura.</p>

          <form @submit.prevent="submitPassword" class="mt-6 grid grid-cols-1 gap-y-6">
            <!-- Actual -->
            <div>
              <label for="current_password" class="block text-sm font-medium leading-6 text-gray-900">Contraseña actual</label>
              <input
                id="current_password"
                v-model="passwordForm.current_password"
                type="password"
                required
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
              />
              <p v-if="passwordForm.errors.current_password" class="mt-2 text-sm text-red-600">{{ passwordForm.errors.current_password }}</p>
            </div>

            <!-- Nueva -->
            <div>
              <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Nueva contraseña</label>
              <input
                id="password"
                v-model="passwordForm.password"
                type="password"
                required
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
              />
              <p v-if="passwordForm.errors.password" class="mt-2 text-sm text-red-600">{{ passwordForm.errors.password }}</p>
            </div>

            <!-- Confirmar -->
            <div>
              <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">Confirmar contraseña</label>
              <input
                id="password_confirmation"
                v-model="passwordForm.password_confirmation"
                type="password"
                required
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
              />
              <p v-if="passwordForm.errors.password_confirmation" class="mt-2 text-sm text-red-600">{{ passwordForm.errors.password_confirmation }}</p>
            </div>

            <!-- Guardar -->
            <div class="flex justify-end">
              <button
                type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md shadow-sm text-sm font-medium hover:bg-indigo-700"
              >
                Guardar
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </AppLayout>
</template>
