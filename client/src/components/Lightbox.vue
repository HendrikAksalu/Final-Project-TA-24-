<template>
  <Teleport to="body">
    <Transition name="lightbox">
      <div 
        v-if="isOpen && photo"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90 p-4"
        @click="close"
      >
        <button
          @click="close"
          class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300 transition-colors z-10"
        >
          ✕
        </button>
        
        <button
          v-if="hasPrevious"
          @click.stop="$emit('previous')"
          class="absolute left-4 text-white text-4xl hover:text-gray-300 transition-colors z-10"
        >
          ‹
        </button>

        <div 
          class="max-w-5xl max-h-full flex flex-col items-center"
          @click.stop
        >
          <img
            :src="photo.url"
            :alt="photo.title || 'Photo'"
            class="max-w-full max-h-[80vh] object-contain rounded-lg"
          />
          <div v-if="photo.title" class="mt-4 text-white text-xl text-center">
            {{ photo.title }}
          </div>
          <div class="mt-2 text-gray-300 text-sm">
            {{ formatDate(photo.createdAt) }}
          </div>
        </div>

        <button
          v-if="hasNext"
          @click.stop="$emit('next')"
          class="absolute right-4 text-white text-4xl hover:text-gray-300 transition-colors z-10"
        >
          ›
        </button>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  photo: {
    type: Object,
    default: null
  },
  hasPrevious: {
    type: Boolean,
    default: false
  },
  hasNext: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'previous', 'next']);

const close = () => {
  emit('close');
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

<style scoped>
.lightbox-enter-active,
.lightbox-leave-active {
  transition: opacity 0.3s ease;
}

.lightbox-enter-from,
.lightbox-leave-to {
  opacity: 0;
}
</style>
