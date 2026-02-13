import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:5000/api';

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json'
  }
});

// Album API
export const albumAPI = {
  getAll: () => api.get('/albums'),
  getById: (id) => api.get(`/albums/${id}`),
  create: (data) => api.post('/albums', data),
  delete: (id) => api.delete(`/albums/${id}`)
};

// Photo API
export const photoAPI = {
  getAll: (albumId) => {
    const params = albumId ? { albumId } : {};
    return api.get('/photos', { params });
  },
  upload: (formData) => {
    return api.post('/photos', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  },
  delete: (id) => api.delete(`/photos/${id}`)
};

export default api;
