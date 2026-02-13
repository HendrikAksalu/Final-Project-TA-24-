<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 cursor-pointer">
    <div class="aspect-w-16 aspect-h-9 bg-gray-200">
      <img
        v-if="album.coverImage"
        :src="album.coverImage"
        :alt="album.name"
        class="w-full h-48 object-cover"
        @error="handleImageError"
      />
      <div v-else class="w-full h-48 flex items-center justify-center bg-gradient-to-br from-indigo-400 to-purple-500">
        <span class="text-6xl">📷</span>
      </div>
    </div>
    <div class="p-4">
      <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ album.name }}</h3>
      <p v-if="album.description" class="text-sm text-gray-600 line-clamp-2">
        {{ album.description }}
      </p>
      <p class="text-xs text-gray-500 mt-2">
        {{ formatDate(album.createdAt) }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  album: {
    type: Object,
    required: true
  }
});

const handleImageError = (e) => {
  e.target.style.display = 'none';
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
