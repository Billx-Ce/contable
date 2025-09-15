<script setup>
import { ref, computed, watchEffect } from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import { Link, usePage } from '@inertiajs/vue3'

const showingNavigationDropdown = ref(false)

// ====== props de Inertia (¡sin .value!) ======
const page = usePage()
const auth = computed(() => page.props.auth ?? {})

// Debug opcional: revisa en consola qué llega
watchEffect(() => {
  // quita esto cuando verifiques
  console.log('AUTH from Inertia:', auth.value)
})

const can = computed(() => auth.value?.can ?? [])
const isSuper = computed(() => !!(auth.value?.isSuperadmin || auth.value?.user?.is_admin))

// visibilidad individual
const canSeeUsuarios = computed(() =>
  isSuper.value || can.value.includes('usuarios.view')
)
const canSeeRoles = computed(() =>
  isSuper.value || can.value.includes('roles.view')
)
const canSeePermisos = computed(() =>
  isSuper.value || can.value.includes('permisos.view')
)

// ¿hay algo que mostrar dentro de Administración?
const showAdminMenu = computed(() =>
  isSuper.value || canSeeUsuarios.value || canSeeRoles.value || canSeePermisos.value
)
</script>

<template>
  <div>
    <div class="min-h-screen bg-gray-100">
      <nav class="border-b border-gray-100 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="flex h-16 justify-between">
            <!-- IZQUIERDA -->
            <div class="flex">
              <div class="flex shrink-0 items-center">
                <Link :href="route('dashboard')">
                  <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                </Link>
              </div>

              <!-- pestañas principales + submenú -->
              <div class="flex items-stretch sm:ms-10">
                <div class="flex flex-wrap gap-4 sm:-my-px">
                  <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                    Panel
                  </NavLink>

                  <NavLink :href="route('personas.index')" :active="route().current('personas.*')">
                    Persona
                  </NavLink>
                  <NavLink
                      v-if="canSeeUsuarios"
                      :href="route('usuarios.index')"
                      :class="{ 'font-semibold text-gray-900': route().current('usuarios.*') }"
                    >
                      Usuario
                  </NavLink>
                </div>

                <!-- Submenú Administración -->
                <div v-if="showAdminMenu" class="ms-2 flex items-center">
                  <Dropdown align="left" width="56">
                    <template #trigger>
                      <button
                        type="button"
                        class="inline-flex items-center rounded-md px-3 py-[18px] text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none"
                      >
                        Administración
                        <svg class="ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </template>

                    <template #content>
                      <div class="py-1">
                        <DropdownLink
                          v-if="canSeeRoles"
                          :href="route('roles.index')"
                          :class="{ 'font-semibold text-gray-900': route().current('roles.*') }"
                        >
                          Roles
                        </DropdownLink>

                        <DropdownLink
                          v-if="canSeePermisos"
                          :href="route('permisos.index')"
                          :class="{ 'font-semibold text-gray-900': route().current('permisos.*') }"
                        >
                          Permisos
                        </DropdownLink>
                      </div>
                    </template>
                  </Dropdown>
                </div>
              </div>
            </div>

            <!-- DERECHA: usuario -->
            <div class="hidden sm:ms-6 sm:flex sm:items-center">
              <div class="relative ms-3">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                      >
                        {{ auth.user?.name }}
                        <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <DropdownLink :href="route('profile.edit')">Perfil</DropdownLink>
                    <DropdownLink :href="route('logout')" method="post" as="button">Salir</DropdownLink>
                  </template>
                </Dropdown>
              </div>
            </div>

            <!-- HAMBURGUESA (móvil) -->
            <div class="-me-2 flex items-center sm:hidden">
              <button
                @click="showingNavigationDropdown = !showingNavigationDropdown"
                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
              >
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                  <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                  <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Menú responsive -->
        <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
          <div class="space-y-1 pb-3 pt-2">
            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
              Panel
            </ResponsiveNavLink>

            <ResponsiveNavLink :href="route('personas.index')" :active="route().current('personas.*')">
              Persona
            </ResponsiveNavLink>
            <ResponsiveNavLink
                v-if="canSeeUsuarios"
                :href="route('usuarios.index')"
                :active="route().current('usuarios.*')"
              >
                Usuario
            </ResponsiveNavLink>

            <div v-if="showAdminMenu" class="mt-2 border-t border-gray-200 pt-2">
              <div class="px-4 pb-1 text-xs uppercase text-gray-400">
                Administración
              </div>
              <ResponsiveNavLink
                v-if="canSeeRoles"
                :href="route('roles.index')"
                :active="route().current('roles.*')"
              >
                Roles
              </ResponsiveNavLink>

              <ResponsiveNavLink
                v-if="canSeePermisos"
                :href="route('permisos.index')"
                :active="route().current('permisos.*')"
              >
                Permisos
              </ResponsiveNavLink>
            </div>
          </div>

          <div class="border-t border-gray-200 pb-1 pt-4">
            <div class="px-4">
              <div class="text-base font-medium text-gray-800">
                {{ auth.user?.name }}
              </div>
              <div class="text-sm font-medium text-gray-500">
                {{ auth.user?.email }}
              </div>
            </div>

            <div class="mt-3 space-y-1">
              <ResponsiveNavLink :href="route('profile.edit')">Perfil</ResponsiveNavLink>
              <ResponsiveNavLink :href="route('logout')" method="post" as="button">Salir</ResponsiveNavLink>
            </div>
          </div>
        </div>
      </nav>

      <header class="bg-white shadow" v-if="$slots.header">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <main>
        <slot />
      </main>
    </div>
  </div>
</template>
