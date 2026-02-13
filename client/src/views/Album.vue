<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="albumStore.loading" class="text-center py-12">
        <p class="text-gray-500">Loading album...</p>
      </div>

      <div v-else-if="albumStore.error" class="text-center py-12">
        <p class="text-red-500">Error: {{ albumStore.error }}</p>
      </div>

      <div v-else-if="albumStore.currentAlbum">
        <div class="mb-8">
          <div class="flex justify-between items-start mb-4">
            <div>
              <button
                @click="$router.push('/')"
                class="text-indigo-600 hover:text-indigo-800 mb-2 flex items-center"
              >
                ← Back to Albums
              </button>
              <h1 class="text-3xl font-bold text-gray-900">
                {{ albumStore.currentAlbum.name }}
              </h1>
              <p v-if="albumStore.currentAlbum.description" class="text-gray-600 mt-2">
                {{ albumStore.currentAlbum.description }}
              </p>
            </div>
            <div class="flex space-x-3">
              <button
                @click="$router.push(`/upload/${$route.params.id}`)"
                class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors"
              >
                + Add Photos
              </button>
              <button
                @click="confirmDelete"
                class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors"
              >
                Delete Album
              </button>
            </div>
          </div>
        </div>

        <div v-if="photoStore.loading" class="text-center py-12">
          <p class="text-gray-500">Loading photos...</p>
        </div>

        <div v-else-if="photoStore.photos.length === 0" class="text-center py-12">
          <p class="text-gray-500 text-lg mb-4">No photos in this album yet.</p>
          <button
            @click="$router.push(`/upload/${$route.params.id}`)"
            class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700"
          >
            Upload Photos
          </button>
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
          <PhotoCard
            v-for="photo in photoStore.photos"
            :key="photo._id"
            :photo="photo"
            :show-delete="true"
            @click="openLightbox(photo)"
            @delete="deletePhoto"
          />
        </div>
      </div>
    </div>

    <Lightbox
      :is-open="lightboxOpen"
      :photo="currentPhoto"
      :has-previous="currentPhotoIndex > 0"
      :has-next="currentPhotoIndex < photoStore.photos.length - 1"
      @close="closeLightbox"
      @previous="previousPhoto"
      @next="nextPhoto"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAlbumStore } from '../stores/albumStore';
import { usePhotoStore } from '../stores/photoStore';
import Navbar from '../components/Navbar.vue';
import PhotoCard from '../components/PhotoCard.vue';
import Lightbox from '../components/Lightbox.vue';

const route = useRoute();
const router = useRouter();
const albumStore = useAlbumStore();
const photoStore = usePhotoStore();

const lightboxOpen = ref(false);
const currentPhoto = ref(null);
const currentPhotoIndex = ref(-1);

onMounted(() => {
  const albumId = route.params.id;
  albumStore.fetchAlbumById(albumId);
  photoStore.fetchPhotos(albumId);
});

const openLightbox = (photo) => {
  currentPhoto.value = photo;
  currentPhotoIndex.value = photoStore.photos.findIndex(p => p._id === photo._id);
  lightboxOpen.value = true;
};

const closeLightbox = () => {
  lightboxOpen.value = false;
  currentPhoto.value = null;
  currentPhotoIndex.value = -1;
};

const previousPhoto = () => {
  if (currentPhotoIndex.value > 0) {
    currentPhotoIndex.value--;
    currentPhoto.value = photoStore.photos[currentPhotoIndex.value];
  }
};

const nextPhoto = () => {
  if (currentPhotoIndex.value < photoStore.photos.length - 1) {
    currentPhotoIndex.value++;
    currentPhoto.value = photoStore.photos[currentPhotoIndex.value];
  }
};

const deletePhoto = async (photoId) => {
  if (confirm('Are you sure you want to delete this photo?')) {
    try {
      await photoStore.deletePhoto(photoId);
      if (currentPhoto.value?._id === photoId) {
        closeLightbox();
      }
    } catch (error) {
      alert('Failed to delete photo');
    }
  }
};

const confirmDelete = async () => {
  if (confirm('Are you sure you want to delete this album and all its photos?')) {
    try {
      await albumStore.deleteAlbum(route.params.id);
      router.push('/');
    } catch (error) {
      alert('Failed to delete album');
    }
  }
};
</script>
