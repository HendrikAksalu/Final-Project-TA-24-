<template>
  <nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex">
          <div class="flex-shrink-0 flex items-center">
            <router-link to="/" class="text-2xl font-bold text-indigo-600">
              📸 Photo Album
            </router-link>
          </div>
          <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
            <router-link
              to="/"
              class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 hover:text-indigo-600"
              :class="{ 'border-b-2 border-indigo-500': $route.path === '/' }"
            >
              🏠 Home
            </router-link>
            <router-link
              to="/upload"
              class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 hover:text-indigo-600"
              :class="{ 'border-b-2 border-indigo-500': $route.path.startsWith('/upload') }"
            >
              ⬆️ Upload
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
</script>

<style scoped>
</style>
