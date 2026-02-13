import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/Home.vue';
import Album from '../views/Album.vue';
import Upload from '../views/Upload.vue';

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/album/:id',
    name: 'Album',
    component: Album
  },
  {
    path: '/upload/:albumId?',
    name: 'Upload',
    component: Upload
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
