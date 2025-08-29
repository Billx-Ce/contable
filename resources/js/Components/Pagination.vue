<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  // Array de objetos: [{ url, label, active }, ...]
  links: { type: Array, required: true },
  // Estos vienen del paginador de Laravel
  from:  { type: Number, default: 0 },
  to:    { type: Number, default: 0 },
  total: { type: Number, default: 0 },
})

const visibleLinks = computed(() =>
  props.links.filter(link => {
    if (link.label?.includes('Previous') || link.label?.includes('Next') || link.active) return true
    if (!isNaN(parseInt(link.label))) return true
    if (link.label?.includes('...')) return true
    return false
  })
)
</script>

<template>
  <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
    <!-- Móvil: Prev / Next -->
    <div class="flex flex-1 justify-between sm:hidden">
      <!-- Prev -->
      <Link
        v-if="links[0] && links[0].url"
        :href="links[0].url"
        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >Anterior</Link>
      <span
        v-else
        class="relative inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-400 opacity-50 cursor-not-allowed"
      >Anterior</span>

      <!-- Next -->
      <Link
        v-if="links[links.length - 1] && links[links.length - 1].url"
        :href="links[links.length - 1].url"
        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >Siguiente</Link>
      <span
        v-else
        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-400 opacity-50 cursor-not-allowed"
      >Siguiente</span>
    </div>

    <!-- Desktop -->
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-gray-700">
          Mostrando <span class="font-medium">{{ from }}</span>
          a <span class="font-medium">{{ to }}</span>
          de <span class="font-medium">{{ total }}</span> resultados
        </p>
      </div>

      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Paginación">
          <template v-for="(link, index) in visibleLinks" :key="index">
            <!-- Previous -->
            <template v-if="link.label?.includes('Previous')">
              <Link
                v-if="link.url"
                :href="link.url"
                class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 hover:text-gray-500 focus:z-20"
              >
                <span class="sr-only">Anterior</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                </svg>
              </Link>
              <span
                v-else
                class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 opacity-50 cursor-not-allowed"
              >
                <span class="sr-only">Anterior</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                </svg>
              </span>
            </template>

            <!-- Next -->
            <template v-else-if="link.label?.includes('Next')">
              <Link
                v-if="link.url"
                :href="link.url"
                class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 hover:text-gray-500 focus:z-20"
              >
                <span class="sr-only">Siguiente</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                </svg>
              </Link>
              <span
                v-else
                class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 opacity-50 cursor-not-allowed"
              >
                <span class="sr-only">Siguiente</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                </svg>
              </span>
            </template>

            <!-- Números -->
            <template v-else-if="!isNaN(parseInt(link.label))">
              <span
                v-if="link.active"
                class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20"
              >{{ link.label }}</span>

              <Link
                v-else-if="link.url"
                :href="link.url"
                class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20"
              >{{ link.label }}</Link>

              <span
                v-else
                class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-400 ring-1 ring-inset ring-gray-300 opacity-50 cursor-not-allowed"
              >{{ link.label }}</span>
            </template>

            <!-- Ellipsis -->
            <span
              v-else
              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300"
            >…</span>
          </template>
        </nav>
      </div>
    </div>
  </div>
</template>
