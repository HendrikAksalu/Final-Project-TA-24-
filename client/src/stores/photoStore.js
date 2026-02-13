import { defineStore } from 'pinia';
import { photoAPI } from '../services/api';

export const usePhotoStore = defineStore('photo', {
  state: () => ({
    photos: [],
    loading: false,
    error: null
  }),

  actions: {
    async fetchPhotos(albumId = null) {
      this.loading = true;
      this.error = null;
      try {
        const response = await photoAPI.getAll(albumId);
        this.photos = response.data;
      } catch (error) {
        this.error = error.message;
        console.error('Error fetching photos:', error);
      } finally {
        this.loading = false;
      }
    },

    async uploadPhoto(formData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await photoAPI.upload(formData);
        this.photos.unshift(response.data);
        return response.data;
      } catch (error) {
        this.error = error.message;
        console.error('Error uploading photo:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deletePhoto(id) {
      this.loading = true;
      this.error = null;
      try {
        await photoAPI.delete(id);
        this.photos = this.photos.filter(photo => photo._id !== id);
      } catch (error) {
        this.error = error.message;
        console.error('Error deleting photo:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});
