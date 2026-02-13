import { defineStore } from 'pinia';
import { albumAPI } from '../services/api';

export const useAlbumStore = defineStore('album', {
  state: () => ({
    albums: [],
    currentAlbum: null,
    loading: false,
    error: null
  }),

  actions: {
    async fetchAlbums() {
      this.loading = true;
      this.error = null;
      try {
        const response = await albumAPI.getAll();
        this.albums = response.data;
      } catch (error) {
        this.error = error.message;
        console.error('Error fetching albums:', error);
      } finally {
        this.loading = false;
      }
    },

    async fetchAlbumById(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await albumAPI.getById(id);
        this.currentAlbum = response.data;
      } catch (error) {
        this.error = error.message;
        console.error('Error fetching album:', error);
      } finally {
        this.loading = false;
      }
    },

    async createAlbum(albumData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await albumAPI.create(albumData);
        this.albums.unshift(response.data);
        return response.data;
      } catch (error) {
        this.error = error.message;
        console.error('Error creating album:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteAlbum(id) {
      this.loading = true;
      this.error = null;
      try {
        await albumAPI.delete(id);
        this.albums = this.albums.filter(album => album._id !== id);
      } catch (error) {
        this.error = error.message;
        console.error('Error deleting album:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});
