import { createRouter, createWebHistory } from 'vue-router'
import AlbumPage from '@/pages/AlbumPage.vue'
import HeritagePage from '@/pages/HeritagePage.vue'
import HomePage from '@/pages/HomePage.vue'
import LoginPage from '@/pages/LoginPage.vue'
import MemoryPage from '@/pages/MemoryPage.vue'
import RegisterPage from '@/pages/RegisterPage.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomePage,
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterPage,
    },
    {
      path: '/login',
      name: 'login',
      component: LoginPage,
    },
    {
      path: '/heritage',
      name: 'heritage',
      component: HeritagePage,
    },
    {
      path: '/album',
      name: 'album',
      component: AlbumPage,
    },
    {
      path: '/memory',
      name: 'memory',
      component: MemoryPage,
    },
  ],
})

export default router
