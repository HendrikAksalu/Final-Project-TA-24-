import { createRouter, createWebHistory } from 'vue-router'
import AlbumPage from '@/pages/AlbumPage.vue'
import HeritagePage from '@/pages/HeritagePage.vue'
import HomePage from '@/pages/HomePage.vue'
import LoginPage from '@/pages/LoginPage.vue'
import MemoryPage from '@/pages/MemoryPage.vue'
import RegisterPage from '@/pages/RegisterPage.vue'
import UserSettingsPage from '@/pages/UserSettingsPage.vue'
import { getToken } from '@/api/fototeekApi.js'

function getStoredUser() {
  try {
    return JSON.parse(localStorage.getItem('fototeek_user') || 'null')
  } catch (error) {
    return null
  }
}

function hasAuthSession() {
  return Boolean(getStoredUser()) && Boolean(getToken())
}

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomePage,
    },
    {
      path: '/registreeru',
      name: 'register',
      component: RegisterPage,
      meta: { guestOnly: true },
    },
    {
      path: '/logi-sisse',
      name: 'login',
      component: LoginPage,
      meta: { guestOnly: true },
    },
    {
      path: '/login',
      redirect: '/logi-sisse',
    },
    {
      path: '/albumid',
      name: 'heritage',
      component: HeritagePage,
      meta: { requiresAuth: true },
    },
    {
      path: '/kasutaja-seaded',
      name: 'user-settings',
      component: UserSettingsPage,
      meta: { requiresAuth: true },
    },
    {
      path: '/album',
      name: 'album',
      component: AlbumPage,
      meta: { requiresAuth: true },
    },
    {
      path: '/parand',
      redirect: '/albumid',
    },
    {
      path: '/malestus',
      name: 'memory',
      component: MemoryPage,
      meta: { requiresAuth: true },
    },
  ],
})

router.beforeEach((to) => {
  const isLoggedIn = hasAuthSession()

  if (to.meta.requiresAuth && !isLoggedIn) {
    return { path: '/logi-sisse' }
  }

  if (to.meta.guestOnly && isLoggedIn) {
    return { path: '/' }
  }

  return true
})

export default router
