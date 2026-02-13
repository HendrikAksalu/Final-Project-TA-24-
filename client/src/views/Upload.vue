<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8">
        <button
          @click="$router.back()"
          class="text-indigo-600 hover:text-indigo-800 mb-4 flex items-center"
        >
          ← Back
        </button>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Upload Photos</h1>
        <p class="text-gray-600">Add new photos to your album</p>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6">
        <form @submit.prevent="handleUpload">
          <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">
              Select Album *
            </label>
            <select
              v-model="selectedAlbumId"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">Choose an album...</option>
              <option
                v-for="album in albumStore.albums"
                :key="album._id"
                :value="album._id"
              >
                {{ album.name }}
              </option>
            </select>
          </div>

          <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">
              Photo Title
            </label>
            <input
              v-model="photoTitle"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="Enter photo title (optional)"
            />
          </div>

          <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">
              Select Photo *
            </label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-indigo-500 transition-colors">
              <div class="space-y-1 text-center">
                <svg
                  v-if="!previewUrl"
                  class="mx-auto h-12 w-12 text-gray-400"
                  stroke="currentColor"
                  fill="none"
                  viewBox="0 0 48 48"
                >
                  <path
                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
                <div v-if="previewUrl" class="mb-4">
                  <img :src="previewUrl" alt="Preview" class="mx-auto max-h-64 rounded-lg" />
                </div>
                <div class="flex text-sm text-gray-600">
                  <label
                    for="file-upload"
                    class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none"
                  >
                    <span>{{ selectedFile ? 'Change file' : 'Upload a file' }}</span>
                    <input
                      id="file-upload"
                      name="file-upload"
                      type="file"
                      accept="image/*"
                      required
                      class="sr-only"
                      @change="handleFileSelect"
                    />
                  </label>
                  <p class="pl-1">or drag and drop</p>
                </div>
                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
              </div>
            </div>
          </div>

          <div v-if="uploadError" class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
            {{ uploadError }}
          </div>

          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="$router.back()"
              class="px-6 py-2 text-gray-600 hover:text-gray-800"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="uploading || !selectedFile || !selectedAlbumId"
              class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              {{ uploading ? 'Uploading...' : 'Upload Photo' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAlbumStore } from '../stores/albumStore';
import { usePhotoStore } from '../stores/photoStore';
import Navbar from '../components/Navbar.vue';

const route = useRoute();
const router = useRouter();
const albumStore = useAlbumStore();
const photoStore = usePhotoStore();

const selectedAlbumId = ref('');
const photoTitle = ref('');
const selectedFile = ref(null);
const previewUrl = ref('');
const uploading = ref(false);
const uploadError = ref('');

onMounted(() => {
  albumStore.fetchAlbums();
  if (route.params.albumId) {
    selectedAlbumId.value = route.params.albumId;
  }
});

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    selectedFile.value = file;
    previewUrl.value = URL.createObjectURL(file);
    uploadError.value = '';
  }
};

const handleUpload = async () => {
  if (!selectedFile.value || !selectedAlbumId.value) {
    uploadError.value = 'Please select both an album and a photo';
    return;
  }

  uploading.value = true;
  uploadError.value = '';

  try {
    const formData = new FormData();
    formData.append('photo', selectedFile.value);
    formData.append('albumId', selectedAlbumId.value);
    if (photoTitle.value) {
      formData.append('title', photoTitle.value);
    }

    await photoStore.uploadPhoto(formData);
    
    // Reset form
    selectedFile.value = null;
    photoTitle.value = '';
    previewUrl.value = '';
    
    // Navigate to the album
    router.push(`/album/${selectedAlbumId.value}`);
  } catch (error) {
    uploadError.value = error.response?.data?.message || 'Failed to upload photo';
  } finally {
    uploading.value = false;
  }
};
</script>

<style scoped>
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}
</style>
