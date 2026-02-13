<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Photo Albums</h1>
        <button
          @click="showCreateModal = true"
          class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors"
        >
          + Create Album
        </button>
      </div>

      <div v-if="albumStore.loading" class="text-center py-12">
        <p class="text-gray-500">Loading albums...</p>
      </div>

      <div v-else-if="albumStore.error" class="text-center py-12">
        <p class="text-red-500">Error: {{ albumStore.error }}</p>
      </div>

      <div v-else-if="albumStore.albums.length === 0" class="text-center py-12">
        <p class="text-gray-500 text-lg mb-4">No albums yet. Create your first album!</p>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="album in albumStore.albums"
          :key="album._id"
          @click="goToAlbum(album._id)"
        >
          <AlbumCard :album="album" />
        </div>
      </div>
    </div>

    <!-- Create Album Modal -->
    <Teleport to="body">
      <div
        v-if="showCreateModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
        @click="showCreateModal = false"
      >
        <div
          class="bg-white rounded-lg p-6 max-w-md w-full"
          @click.stop
        >
          <h2 class="text-2xl font-bold mb-4">Create New Album</h2>
          <form @submit.prevent="createAlbum">
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2">
                Album Name *
              </label>
              <input
                v-model="newAlbum.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Enter album name"
              />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2">
                Description
              </label>
              <textarea
                v-model="newAlbum.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Enter album description"
              ></textarea>
            </div>
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="showCreateModal = false"
                class="px-4 py-2 text-gray-600 hover:text-gray-800"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700"
              >
                Create
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAlbumStore } from '../stores/albumStore';
import Navbar from '../components/Navbar.vue';
import AlbumCard from '../components/AlbumCard.vue';

const router = useRouter();
const albumStore = useAlbumStore();

const showCreateModal = ref(false);
const newAlbum = ref({
  name: '',
  description: ''
});

onMounted(() => {
  albumStore.fetchAlbums();
});

const createAlbum = async () => {
  try {
    await albumStore.createAlbum(newAlbum.value);
    showCreateModal.value = false;
    newAlbum.value = { name: '', description: '' };
  } catch (error) {
    alert('Failed to create album');
  }
};

const goToAlbum = (id) => {
  router.push(`/album/${id}`);
};
</script>
