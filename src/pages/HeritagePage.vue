<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'
import { apiFetch, getToken, logoutSession, parseApiError } from '@/api/fototeekApi.js'

const router = useRouter()
const user = ref(null)
const menuOpen = ref(false)

try {
  user.value = JSON.parse(localStorage.getItem('fototeek_user') || 'null')
} catch (error) {
  user.value = null
}

const isLoggedIn = computed(() => Boolean(user.value && getToken()))

const albums = ref([])
const listError = ref('')
const listLoading = ref(false)

const orderedAlbums = computed(() =>
  [...albums.value].sort((a, b) => Number(a.id || 0) - Number(b.id || 0)),
)

async function refreshAlbums() {
  if (!getToken()) return
  listError.value = ''
  listLoading.value = true
  try {
    const res = await apiFetch('/albums')
    if (!res.ok) {
      listError.value = await parseApiError(res, 'Albumite laadimine ebaõnnestus.')
      albums.value = []
      return
    }
    const data = await res.json()
    albums.value = Array.isArray(data.albums) ? data.albums : []
  } finally {
    listLoading.value = false
  }
}

onMounted(() => {
  refreshAlbums()
})

async function createAlbum() {
  const index = albums.value.length + 1
  const res = await apiFetch('/albums', {
    method: 'POST',
    body: {
      title: `Uus album ${index}`,
      photoClass: 'beach',
      rotate: index % 2 ? 'rotate-right' : '',
    },
  })
  if (!res.ok) {
    alert(await parseApiError(res, 'Albumi loomine ebaõnnestus.'))
    return
  }
  await refreshAlbums()
}

async function deleteAlbum(albumId) {
  const shouldDelete = window.confirm('Kas soovid selle albumi kustutada?')
  if (!shouldDelete) return

  const res = await apiFetch(`/albums/${albumId}`, { method: 'DELETE' })
  if (!res.ok) {
    alert(await parseApiError(res, 'Albumi kustutamine ebaõnnestus.'))
    return
  }
  await refreshAlbums()
}

async function renameAlbum(albumId, currentTitle) {
  const nextTitle = window.prompt('Sisesta uus albumi nimi:', currentTitle || '')
  if (nextTitle === null) return

  const cleanedTitle = nextTitle.trim()
  if (!cleanedTitle) return

  const res = await apiFetch(`/albums/${albumId}`, {
    method: 'PATCH',
    body: { title: cleanedTitle },
  })
  if (!res.ok) {
    alert(await parseApiError(res, 'Nime muutmine ebaõnnestus.'))
    return
  }
  await refreshAlbums()
}

async function logout() {
  await logoutSession()
  user.value = null
  menuOpen.value = false
  albums.value = []
  router.push('/')
}
</script>

<template>
  <main class="page page-shell">
    <div class="header-wrap">
      <AppHeader
        :show-auth-links="!isLoggedIn"
        :show-menu="isLoggedIn"
        @menu-click="menuOpen = !menuOpen"
      />
      <div v-if="isLoggedIn && menuOpen" class="menu-popover">
        <RouterLink to="/albumid" @click="menuOpen = false">Minu albumid</RouterLink>
        <RouterLink to="/kasutaja-seaded" @click="menuOpen = false">Kasutaja sätted</RouterLink>
        <button type="button" @click="logout">Logi välja</button>
      </div>
    </div>

    <section class="title">
      <p>Sinu pärand</p>
      <h1>
        Säilitame sinu pereloo ajatuid niite.
      </h1>
    </section>

    <p v-if="listError" class="list-error">{{ listError }}</p>
    <p v-else-if="listLoading" class="list-loading">Laadin albumeid…</p>

    <section v-if="albums.length" class="album-grid">
      <article v-for="album in orderedAlbums" :key="album.id || album.title" class="album-card">
        <RouterLink :to="{ path: '/album', query: { albumId: album.id } }" class="polaroid" :class="album.rotate">
          <div class="photo" :class="{ 'empty-photo': !album.coverThumbUrl }">
            <img v-if="album.coverThumbUrl" :src="album.coverThumbUrl" alt="" class="album-cover-image" />
            <span v-else class="empty-photo-label">Tühi</span>
          </div>
          <h2>{{ album.title }}</h2>
          <span>{{ album.memories }} pilti</span>
          <span v-if="album.isSharedWithMe" class="shared-tag">Jagatud sinuga</span>
        </RouterLink>
        <div v-if="album.myRole === 'owner'" class="album-actions">
          <button type="button" class="rename-album-btn" @click.prevent="renameAlbum(album.id, album.title)">
            Muuda nime
          </button>
          <button type="button" class="delete-album-btn" @click.prevent="deleteAlbum(album.id)">Kustuta</button>
        </div>
      </article>
    </section>
    <section v-else-if="!listLoading" class="empty-state">
      <p>Sul pole veel ühtegi albumit.</p>
      <p>Loo esimene album, et alustada mälestuste kogumist.</p>
    </section>

    <button type="button" class="create-btn" @click="createAlbum">Loo uus album</button>

    <footer class="footer">
      <nav>
        <a href="#">Meist</a>
        <a href="#">Privaatsus</a>
        <a href="#">Eetika</a>
      </nav>
      <p class="copyright">© 2025 Fototeek</p>
      <p class="note">Hoiame meie esivanemate lugusid.</p>
    </footer>
  </main>
</template>

<style scoped>
.header-wrap {
  position: relative;
}

.menu-popover {
  position: absolute;
  right: 0;
  top: 28px;
  min-width: 130px;
  background: var(--surface-strong, #fff);
  border: 1px solid var(--line-soft, #ddd4c6);
  border-radius: 10px;
  box-shadow: 0 8px 18px rgba(20, 12, 8, 0.16);
  overflow: hidden;
  z-index: 10;
}

.menu-popover a,
.menu-popover button {
  display: block;
  width: 100%;
  text-align: left;
  padding: 10px 12px;
  background: transparent;
  border: 0;
  color: var(--ink, #231f20);
  text-decoration: none;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 12px;
  cursor: pointer;
}

.menu-popover a:hover,
.menu-popover button:hover {
  background: var(--paper-bg, #f5f2ee);
}

.list-error,
.list-loading {
  margin-top: 16px;
  text-align: center;
  font-size: 14px;
  color: #6f6257;
}

.list-error {
  color: #8b3a3a;
}

.shared-tag {
  display: block;
  margin-top: 4px;
  font-size: 8px !important;
  letter-spacing: 0.06em !important;
  color: #6b7f9e !important;
}

.title {
  margin-top: 26px;
  text-align: center;
}

.title p {
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-family: Arial, sans-serif;
  font-size: 10px;
}

.title h1 {
  margin-top: 16px;
  font-size: 58px;
  line-height: 1.02;
  font-weight: 500;
}

.album-grid {
  margin-top: 24px;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.album-card {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.empty-state {
  margin-top: 24px;
  padding: 20px 16px;
  border-radius: 16px;
  background: #f5f2eb;
  text-align: center;
}

.empty-state p:first-child {
  font-size: 20px;
}

.empty-state p:last-child {
  margin-top: 8px;
  color: #6f6257;
  font-style: italic;
}

.polaroid {
  position: relative;
  background: #f3f0e8;
  box-shadow: 0 6px 14px rgba(19, 11, 8, 0.14);
  padding: 9px 9px 12px;
  text-decoration: none;
  color: #1c1714;
  transition: transform 0.2s ease;
}

.polaroid:active {
  transform: scale(0.98);
}

.delete-album-btn {
  border: 1px solid #d6ccbe;
  border-radius: 999px;
  background: #f8f4ed;
  color: #3f342d;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  padding: 8px 12px;
  cursor: pointer;
  align-self: center;
}

.delete-album-btn:hover {
  background: #f1ebdf;
}

.album-actions {
  display: flex;
  justify-content: center;
  gap: 8px;
}

.rename-album-btn {
  border: 1px solid #d6ccbe;
  border-radius: 999px;
  background: #f8f4ed;
  color: #3f342d;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  padding: 8px 12px;
  cursor: pointer;
}

.rename-album-btn:hover {
  background: #f1ebdf;
}

.polaroid::before {
  content: '';
  position: absolute;
  width: 34px;
  height: 10px;
  background: #ebe6db;
  left: 50%;
  top: -6px;
  transform: translateX(-50%);
}

.rotate-right {
  transform: rotate(1.5deg);
}

.rotate-left {
  transform: rotate(-1.5deg);
}

.photo {
  height: 130px;
  border: 1px solid #d9d3c6;
  display: flex;
  align-items: center;
  justify-content: center;
}

.album-cover-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.empty-photo {
  background: #fff;
}

.empty-photo-label {
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 30px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #4b4037;
}

.beach {
  background: linear-gradient(180deg, #bcb7ac 0%, #d8d2c5 55%, #a6a193 55%, #d1cabd 100%);
}

.workshop {
  background: linear-gradient(180deg, #8f8a82 0%, #c2bcb0 40%, #6d685f 40%, #aca495 100%);
}

.portrait {
  background: radial-gradient(circle at 48% 42%, #bdb8ad 0%, #8f887f 45%, #5f5952 100%);
}

.barn {
  background: linear-gradient(180deg, #bdb8ae 0%, #dad3c7 52%, #8a8376 52%, #c4bcad 100%);
}

.polaroid h2 {
  margin-top: 9px;
  text-align: center;
  font-size: 33px;
  line-height: 0.95;
  font-weight: 500;
}

.polaroid span {
  display: block;
  margin-top: 6px;
  text-align: center;
  text-transform: uppercase;
  letter-spacing: 0.09em;
  font-size: 9px;
  color: #8f8478;
  font-family: Arial, sans-serif;
}

.create-btn {
  margin-top: 22px;
  width: 100%;
  display: block;
  text-align: center;
  border: none;
  border-radius: 12px;
  background: #1e130c;
  color: #fff;
  text-transform: uppercase;
  letter-spacing: 0.18em;
  font-family: Arial, sans-serif;
  font-weight: 700;
  padding: 17px 14px;
  text-decoration: none;
  cursor: pointer;
}

.footer {
  margin-top: 62px;
  border-top: 1px solid var(--line-soft, #dad6cd);
  padding-top: 28px;
  text-align: center;
}

.footer nav {
  display: flex;
  justify-content: center;
  gap: 24px;
  text-transform: uppercase;
  letter-spacing: 0.13em;
  font-size: 10px;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-weight: 500;
}

.footer a {
  color: var(--ink, #231f20);
  text-decoration: none;
}

.copyright {
  margin-top: 20px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 9px;
  font-family: var(--font-sans, 'Inter', sans-serif);
  color: #5c534d;
}

.note {
  margin-top: 8px;
  font-style: italic;
  font-size: 13px;
  font-family: var(--font-serif, 'EB Garamond', Georgia, serif);
  color: #655a52;
}

@media (min-width: 640px) {
  .title h1 {
    font-size: 72px;
  }

  .album-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
  }
}

@media (min-width: 1200px) {
  .title h1 {
    font-size: 76px;
  }

  .album-grid {
    margin-top: 30px;
    grid-template-columns: repeat(4, minmax(210px, 1fr));
    gap: 24px;
  }

  .photo {
    height: 180px;
  }

  .create-btn {
    width: auto;
    min-width: 260px;
    margin-left: auto;
    margin-right: auto;
    padding-left: 28px;
    padding-right: 28px;
  }
}
</style>
