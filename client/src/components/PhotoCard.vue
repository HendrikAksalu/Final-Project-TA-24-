<template>
  <div 
    class="relative group cursor-pointer"
    @click="$emit('click', photo)"
  >
    <div class="aspect-w-1 aspect-h-1 bg-gray-200 rounded-lg overflow-hidden">
      <img
        :src="photo.url"
        :alt="photo.title || 'Photo'"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        @error="handleImageError"
      />
    </div>
    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-opacity duration-300 rounded-lg flex items-center justify-center">
      <span class="text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-4xl">
        🔍
      </span>
    </div>
    <div v-if="photo.title" class="mt-2">
      <p class="text-sm text-gray-700 truncate">{{ photo.title }}</p>
    </div>
    <button
      v-if="showDelete"
      @click.stop="$emit('delete', photo._id)"
      class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 hover:bg-red-600"
    >
      🗑️
    </button>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  photo: {
    type: Object,
    required: true
  },
  showDelete: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['click', 'delete']);

const handleImageError = (e) => {
  e.target.src = 'https://via.placeholder.com/400?text=Image+Not+Found';
};
</script>
