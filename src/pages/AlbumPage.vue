<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'
import { apiFetch, getToken, logoutSession, normalizeMemoryFromApi, parseApiError } from '@/api/fototeekApi.js'

const route = useRoute()
const router = useRouter()
const user = ref(null)
try {
  user.value = JSON.parse(localStorage.getItem('fototeek_user') || 'null')
} catch (error) {
  user.value = null
}

const isLoggedIn = computed(() => Boolean(user.value && getToken()))

const albumMeta = ref(null)
const memories = ref([])
const pageLoading = ref(false)
const pageError = ref('')
const collaborators = ref([])
const shareEmail = ref('')
const shareNotice = ref('')

const canEdit = computed(() => {
  const role = albumMeta.value?.myRole
  return role === 'owner' || role === 'editor'
})

const canRenameAlbum = computed(() => {
  const a = albumMeta.value
  if (!a) return false
  if (!a.isSharedWithMe) return true
  return a.myRole === 'owner'
})
const editingAlbumTitle = ref(false)
const albumTitleDraft = ref('')
const renameNotice = ref('')

watch(albumMeta, () => {
  editingAlbumTitle.value = false
  albumTitleDraft.value = ''
  renameNotice.value = ''
})

function startRenameAlbum() {
  if (!albumMeta.value || !canRenameAlbum.value) return
  albumTitleDraft.value = albumMeta.value.title || ''
  renameNotice.value = ''
  editingAlbumTitle.value = true
}

function cancelRenameAlbum() {
  editingAlbumTitle.value = false
  albumTitleDraft.value = ''
  renameNotice.value = ''
}

async function saveAlbumTitle() {
  if (!albumMeta.value?.id || !canRenameAlbum.value) return
  const next = albumTitleDraft.value.trim()
  if (!next) {
    renameNotice.value = 'Albumi nimi ei tohi olla tühi.'
    return
  }
  renameNotice.value = ''
  const res = await apiFetch(`/albums/${albumMeta.value.id}`, {
    method: 'PATCH',
    body: { title: next },
  })
  if (!res.ok) {
    renameNotice.value = await parseApiError(res, 'Nime salvestamine ebaõnnestus.')
    return
  }
  const data = await res.json()
  if (data.album) {
    albumMeta.value = { ...albumMeta.value, ...data.album }
  }
  editingAlbumTitle.value = false
  albumTitleDraft.value = ''
}

async function loadCollaborators() {
  const id = route.query.albumId
  if (!id || albumMeta.value?.myRole !== 'owner') {
    collaborators.value = []
    return
  }
  const res = await apiFetch(`/albums/${id}/collaborators`)
  if (!res.ok) {
    collaborators.value = []
    return
  }
  const data = await res.json()
  collaborators.value = Array.isArray(data.collaborators) ? data.collaborators : []
}

async function loadAlbumPage() {
  const id = route.query.albumId
  pageLoading.value = true
  pageError.value = ''
  if (!id) {
    albumMeta.value = null
    memories.value = []
    collaborators.value = []
    pageLoading.value = false
    return
  }

  const [aRes, mRes] = await Promise.all([apiFetch(`/albums/${id}`), apiFetch(`/albums/${id}/memories`)])

  if (!aRes.ok) {
    albumMeta.value = null
    memories.value = []
    pageError.value = await parseApiError(aRes, 'Albumit ei leitud.')
    pageLoading.value = false
    return
  }

  const albumJson = await aRes.json()
  albumMeta.value = albumJson.album || null

  if (!mRes.ok) {
    memories.value = []
    pageError.value = await parseApiError(mRes, 'Mälestusi ei laaditud.')
    await loadCollaborators()
    pageLoading.value = false
    return
  }

  const memJson = await mRes.json()
  memories.value = (memJson.memories || []).map(normalizeMemoryFromApi)
  await loadCollaborators()
  pageLoading.value = false
}

watch(
  () => [route.path, route.query.albumId],
  () => {
    if (route.path !== '/album') return
    loadAlbumPage()
  },
  { immediate: true },
)
const orderedMemories = computed(() =>
  [...memories.value].sort((a, b) => Number(a.id || 0) - Number(b.id || 0)),
)
const searchQuery = ref('')
const visibleCount = ref(24)
const PAGE_SIZE = 24
const photoClasses = ['one', 'two', 'three', 'four']

function formatDate(value) {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  return date.toLocaleDateString('et-EE')
}

function getMemoryTags(memory) {
  const tags = []
  if (memory.who) tags.push(...memory.who.split(',').map((part) => part.trim()).filter(Boolean))
  if (memory.where) tags.push(...memory.where.split(',').map((part) => part.trim()).filter(Boolean))
  return [...new Set(tags)]
}

const filteredMemories = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  if (!query) return orderedMemories.value

  return orderedMemories.value.filter((memory) => {
    const searchable = [memory.title, memory.who, memory.where, ...getMemoryTags(memory)]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()
    return searchable.includes(query)
  })
})

const visibleMemories = computed(() => filteredMemories.value.slice(0, visibleCount.value))
const hasMoreMemories = computed(() => filteredMemories.value.length > visibleCount.value)
const hasAnyMemories = computed(() => memories.value.length > 0)
const galleryIndex = ref(-1)
const hasGallery = computed(() => filteredMemories.value.some((memory) => memory.imageUrl || memory.imageThumbUrl))
const galleryMemories = computed(() => filteredMemories.value.filter((memory) => memory.imageUrl || memory.imageThumbUrl))
const currentGalleryMemory = computed(() =>
  galleryIndex.value >= 0 && galleryIndex.value < galleryMemories.value.length
    ? galleryMemories.value[galleryIndex.value]
    : null,
)

watch(searchQuery, () => {
  visibleCount.value = PAGE_SIZE
})

async function addMemory() {
  if (!albumMeta.value?.id || !canEdit.value) return

  const nextIndex = memories.value.length + 1
  const res = await apiFetch(`/albums/${albumMeta.value.id}/memories`, {
    method: 'POST',
    body: {
      title: `Uus pilt ${nextIndex}`,
      photoClass: photoClasses[nextIndex % photoClasses.length],
      favorite: false,
      rotate: nextIndex % 2 ? 'rotate-right' : '',
      story: '',
      who: '',
      when: '',
      where: '',
      imageUrl: '',
      imageThumbUrl: '',
      faceMarkers: [],
    },
  })

  if (!res.ok) {
    alert(await parseApiError(res, 'Pildi loomine ebaõnnestus.'))
    return
  }

  const data = await res.json()
  const created = normalizeMemoryFromApi(data.memory)
  memories.value.push(created)
  memories.value.sort((a, b) => Number(a.id) - Number(b.id))

  if (albumMeta.value && data.album) {
    albumMeta.value.memories = data.album.memories
    albumMeta.value.coverThumbUrl = data.album.coverThumbUrl
  }

  router.push({
    path: '/malestus',
    query: {
      albumId: route.query.albumId,
      memoryId: created.id,
      title: created.title || 'Mälestus',
    },
  })
}

async function deleteLatestMemory() {
  if (!orderedMemories.value.length || !canEdit.value) return

  const options = orderedMemories.value
    .map((memory, index) => `${index + 1}. ${memory.title || `Pilt ${index + 1}`}`)
    .join('\n')
  const selected = window.prompt(`Vali kustutatav pilt (number):\n${options}`, '')
  if (selected === null) return
  const parsedIndex = Number.parseInt(selected.trim(), 10)
  if (!Number.isInteger(parsedIndex) || parsedIndex < 1 || parsedIndex > orderedMemories.value.length) return

  const target = orderedMemories.value[parsedIndex - 1]
  const shouldDelete = window.confirm(`Kas kustutada pilt "${target.title || 'Nimetu pilt'}"?`)
  if (!shouldDelete) return

  const res = await apiFetch(`/memories/${target.id}`, { method: 'DELETE' })
  if (!res.ok) {
    alert(await parseApiError(res, 'Kustutamine ebaõnnestus.'))
    return
  }

  memories.value = memories.value.filter((memory) => String(memory.id) !== String(target.id))
  const payload = await res.json()
  if (albumMeta.value && payload.album) {
    albumMeta.value.memories = payload.album.memories
    albumMeta.value.coverThumbUrl = payload.album.coverThumbUrl
  }
}

async function toggleFavorite(id) {
  const memory = memories.value.find((item) => item.id === id)
  if (!memory || !canEdit.value) return

  const next = !memory.favorite
  const res = await apiFetch(`/memories/${id}`, { method: 'PATCH', body: { favorite: next } })
  if (!res.ok) return

  memory.favorite = next
}

async function shareWithUser() {
  shareNotice.value = ''
  const email = shareEmail.value.trim()
  if (!email || !albumMeta.value?.id) return

  const res = await apiFetch(`/albums/${albumMeta.value.id}/share`, {
    method: 'POST',
    body: { email, role: 'editor' },
  })

  if (!res.ok) {
    shareNotice.value = await parseApiError(res, 'Jagamine ebaõnnestus.')
    return
  }

  shareNotice.value = 'Kasutaja sai kutse — ta saab nüüd samasse albumisse pilte lisada.'
  shareEmail.value = ''
  await loadCollaborators()
}

async function removeCollaborator(userId) {
  if (!albumMeta.value?.id) return
  const ok = window.confirm('Kas eemaldada see kasutaja albumilt?')
  if (!ok) return

  const res = await apiFetch(`/albums/${albumMeta.value.id}/share/${userId}`, { method: 'DELETE' })
  if (!res.ok) {
    alert(await parseApiError(res, 'Eemaldamine ebaõnnestus.'))
    return
  }
  await loadCollaborators()
}

async function leaveSharedAlbum() {
  const albumId = albumMeta.value?.id
  if (!albumId) return
  const ok = window.confirm('Kas soovid sellest jagatud albumist lahkuda?')
  if (!ok) return

  const res = await apiFetch(`/albums/${albumId}/leave`, { method: 'DELETE' })
  if (!res.ok) {
    alert(await parseApiError(res, 'Albumist lahkumine ebaõnnestus.'))
    return
  }

  await router.push('/albumid')
}

function showMoreMemories() {
  visibleCount.value += PAGE_SIZE
}

function openGallery() {
  if (!galleryMemories.value.length) return
  galleryIndex.value = 0
}

function closeGallery() {
  galleryIndex.value = -1
}

function prevGalleryImage() {
  if (galleryIndex.value <= 0) return
  galleryIndex.value -= 1
}

function nextGalleryImage() {
  if (galleryIndex.value >= galleryMemories.value.length - 1) return
  galleryIndex.value += 1
}

function handleGalleryKeydown(event) {
  if (galleryIndex.value < 0) return
  if (event.key === 'ArrowLeft') {
    event.preventDefault()
    prevGalleryImage()
  } else if (event.key === 'ArrowRight') {
    event.preventDefault()
    nextGalleryImage()
  } else if (event.key === 'Escape') {
    event.preventDefault()
    closeGallery()
  }
}

if (typeof window !== 'undefined') {
  window.addEventListener('keydown', handleGalleryKeydown)
}

onBeforeUnmount(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('keydown', handleGalleryKeydown)
  }
})

async function logout() {
  await logoutSession()
  user.value = null
  router.push('/')
}
</script>

<template>
  <main class="page page-shell">
    <AppHeader :show-auth-links="!isLoggedIn" :show-menu="isLoggedIn" @logout="logout" />

    <section class="title">
      <p>Perearhiiv</p>
      <h1>{{ albumMeta?.title ?? 'Minu pildid' }}</h1>
      <div v-if="albumMeta && canRenameAlbum" class="rename-album-wrap rename-album-wrap--under-title">
        <template v-if="!editingAlbumTitle">
          <a
            href="#"
            role="button"
            class="back-to-albums-btn rename-under-title-btn"
            @click.prevent="startRenameAlbum"
            @keydown.enter.prevent="startRenameAlbum"
            @keydown.space.prevent="startRenameAlbum"
          >
            Muuda albumi nime
          </a>
        </template>
        <template v-else>
          <div class="rename-album-row">
            <input
              v-model="albumTitleDraft"
              type="text"
              class="rename-album-input"
              maxlength="255"
              autocomplete="off"
              aria-label="Albumi uus nimi"
              @keydown.enter.prevent="saveAlbumTitle"
            />
            <button type="button" class="rename-album-save" @click="saveAlbumTitle">Salvesta</button>
            <button type="button" class="rename-album-cancel" @click="cancelRenameAlbum">Loobu</button>
          </div>
          <p v-if="renameNotice" class="rename-album-notice">{{ renameNotice }}</p>
        </template>
      </div>
      <p class="subtitle">
        Säilitame sinu pere ajaloo puudutatava olemuse püsivas ja kaunis
        digitaalses arhiivis.
      </p>
      <p v-if="albumMeta" class="meta-line">
        Albumi lõi: {{ albumMeta.ownerName || 'Tundmatu' }} · Lisatud: {{ formatDate(albumMeta.createdAt) || '—' }}
      </p>
      <RouterLink to="/albumid" class="back-to-albums-btn">Tagasi albumitesse</RouterLink>
    </section>

    <section v-if="pageError && !albumMeta" class="empty-state">
      <p>{{ pageError }}</p>
      <RouterLink to="/albumid" class="back-to-albums-btn">Tagasi albumitesse</RouterLink>
    </section>

    <section v-else-if="!albumMeta" class="empty-state">
      <p>Albumit ei leitud.</p>
      <p>Mine tagasi ja loo album enne, kui lisad mälestusi.</p>
    </section>

    <div v-if="albumMeta" class="search-wrap">
      <input v-model="searchQuery" type="text" placeholder="Otsi nime või koha järgi..." class="search-input" />
    </div>
    <button
      v-if="albumMeta"
      type="button"
      class="view-large-btn"
      :disabled="!hasGallery"
      :title="hasGallery ? 'Vaata suurelt' : 'Lisa vähemalt üks pilt, et avada suur vaade'"
      @click="openGallery"
    >
      Vaata suurelt
    </button>

    <section v-if="albumMeta && pageLoading" class="skeleton-grid" aria-label="Laadin mälestusi">
      <div v-for="i in 6" :key="`skeleton-${i}`" class="skeleton-card"></div>
    </section>
    <section v-else-if="albumMeta && filteredMemories.length" class="album-grid">
      <article v-for="memory in visibleMemories" :key="memory.id" class="polaroid" :class="memory.rotate">
        <RouterLink
          :to="{ path: '/malestus', query: { title: memory.title, albumId: route.query.albumId, memoryId: memory.id } }"
          class="memory-link"
        >
          <div class="photo" :class="{ [memory.photoClass]: !memory.imageThumbUrl }">
            <img
              v-if="memory.imageThumbUrl"
              :src="memory.imageThumbUrl"
              alt=""
              class="photo-image"
              loading="lazy"
              decoding="async"
            />
          </div>
          <h2>{{ memory.title }}</h2>
          <p class="memory-meta">
            Lisas: {{ memory.authorName || 'Tundmatu' }} · {{ formatDate(memory.createdAt) || '—' }}
          </p>
          <div v-if="getMemoryTags(memory).length" class="tag-list">
            <span v-for="tag in getMemoryTags(memory)" :key="tag" class="tag-chip">#{{ tag }}</span>
          </div>
        </RouterLink>
        <button v-if="canEdit" type="button" class="fav-btn" @click="toggleFavorite(memory.id)">
          {{ memory.favorite ? '★' : '☆' }}
        </button>
      </article>
    </section>
    <button v-if="albumMeta && hasMoreMemories" type="button" class="load-more-btn" @click="showMoreMemories">
      Laadi juurde
    </button>
    <section v-else-if="albumMeta && !hasAnyMemories" class="empty-state">
      <p>Selles albumis pole veel mälestusi.</p>
      <p>Lisa esimene mälestus, et album täituma hakkaks.</p>
    </section>
    <div v-if="albumMeta && canEdit" class="album-actions">
      <button type="button" class="create-btn" @click="addMemory">Lisa pilt</button>
      <button v-if="hasAnyMemories" type="button" class="delete-btn" @click="deleteLatestMemory">Kustuta pilt</button>
    </div>
    <div v-if="albumMeta && albumMeta.myRole !== 'owner' && albumMeta.isSharedWithMe" class="leave-album-wrap">
      <button type="button" class="leave-album-btn" @click="leaveSharedAlbum">Lahku albumist</button>
    </div>

    <section v-if="albumMeta && albumMeta.myRole === 'owner'" class="share-panel">
      <h3 class="share-heading">Jaga albumit</h3>
      <p class="share-intro">
        Sisesta registreerunud kasutaja e-post. Ta saab lisada pilte ja mälestusi samasse albumisse.
      </p>
      <div class="share-row">
        <input v-model="shareEmail" type="email" class="share-input" placeholder="partner@example.com" />
        <button type="button" class="share-submit" @click="shareWithUser">Jaga</button>
      </div>
      <p v-if="shareNotice" class="share-notice">{{ shareNotice }}</p>
      <ul v-if="collaborators.length" class="collab-list">
        <li v-for="c in collaborators" :key="c.userId" class="collab-item">
          <span>{{ c.name }} ({{ c.email }}) — {{ c.role === 'editor' ? 'saab lisada pilte' : 'vaataja' }}</span>
          <button type="button" class="collab-remove" @click="removeCollaborator(c.userId)">Eemalda</button>
        </li>
      </ul>
    </section>

    <div v-if="currentGalleryMemory" class="gallery-overlay" @click.self="closeGallery">
      <button type="button" class="gallery-close" @click="closeGallery">×</button>
      <button type="button" class="gallery-arrow" :disabled="galleryIndex <= 0" @click="prevGalleryImage">←</button>
      <figure class="gallery-figure">
        <img
          :src="currentGalleryMemory.imageUrl || currentGalleryMemory.imageThumbUrl"
          alt=""
          class="gallery-image"
          loading="lazy"
        />
        <figcaption>{{ currentGalleryMemory.title || 'Pilt' }}</figcaption>
      </figure>
      <button
        type="button"
        class="gallery-arrow"
        :disabled="galleryIndex >= galleryMemories.length - 1"
        @click="nextGalleryImage"
      >
        →
      </button>
    </div>

    <footer class="footer">
      <nav>
        <RouterLink to="/meist">Meist</RouterLink>
        <RouterLink to="/privaatsus">Privaatsus</RouterLink>
        <RouterLink to="/eetika">Eetika</RouterLink>
      </nav>
      <p class="copyright">© 2025 Fototeek</p>
      <p class="note">Hoiame meie esivanemate lugusid.</p>
    </footer>
  </main>
</template>

<style scoped>
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
  line-height: 0.95;
  font-weight: 500;
}

.subtitle {
  margin: 16px auto 0;
  max-width: 280px;
  text-transform: none !important;
  letter-spacing: 0 !important;
  font-family: Georgia, 'Times New Roman', serif !important;
  font-style: italic;
  color: #53473f;
  font-size: 18px !important;
  line-height: 1.3;
}

.meta-line {
  margin: 10px auto 0;
  max-width: 640px;
  font-size: 12px;
  color: #655a52;
  font-family: var(--font-sans, 'Inter', sans-serif);
}

.rename-album-wrap {
  margin: 14px auto 0;
  max-width: 420px;
  padding: 0 8px;
}

.rename-album-wrap--under-title {
  margin-top: 10px;
  margin-bottom: 4px;
}

.rename-under-title-btn {
  margin-top: 8px;
  cursor: pointer;
}

.rename-album-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
  justify-content: center;
}

.rename-album-input {
  flex: 1;
  min-width: 160px;
  max-width: 100%;
  border: 1px solid #d8d2c5;
  border-radius: 10px;
  padding: 10px 12px;
  font-size: 15px;
  background: #fff;
  font-family: var(--font-serif, 'EB Garamond', Georgia, serif);
  color: #1c1714;
}

.rename-album-save {
  border: 1px solid #1e130c;
  border-radius: 999px;
  background: #1e130c;
  color: #fff;
  padding: 10px 16px;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  cursor: pointer;
  font-family: var(--font-sans, 'Inter', sans-serif);
}

.rename-album-cancel {
  border: 1px solid #d6ccbe;
  border-radius: 999px;
  background: #f8f4ed;
  color: #3f342d;
  padding: 10px 14px;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  cursor: pointer;
  font-family: var(--font-sans, 'Inter', sans-serif);
}

.rename-album-notice {
  margin: 8px 0 0;
  text-align: center;
  font-size: 13px;
  color: #8b2e2e;
}

.back-to-albums-btn {
  margin: 16px auto 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #d6ccbe;
  border-radius: 999px;
  background: #f8f4ed;
  color: #3f342d;
  text-decoration: none;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  padding: 10px 16px;
}

.back-to-albums-btn:hover {
  background: #f1ebdf;
}

/* <a> saab stiili vaikselt; <button> vajab appearance + tausta, muidu WebKit annab valge kasti */
button.back-to-albums-btn {
  appearance: none;
  -webkit-appearance: none;
  background: #f8f4ed;
  color: #3f342d;
}

button.back-to-albums-btn:hover {
  background: #f1ebdf;
}

.album-grid {
  margin-top: 24px;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.skeleton-grid {
  margin-top: 24px;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.skeleton-card {
  width: 100%;
  aspect-ratio: 1;
  border-radius: 8px;
  background: linear-gradient(90deg, #eee 25%, #f5f5f5 50%, #eee 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
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

.search-wrap {
  margin-top: 18px;
}

.view-large-btn {
  margin: 12px auto 0;
  display: block;
  border: 1px solid #d6ccbe;
  border-radius: 999px;
  background: #f8f4ed;
  color: #3f342d;
  padding: 10px 20px;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  cursor: pointer;
}

.view-large-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.search-input {
  width: 100%;
  border: 1px solid #ddd4c6;
  border-radius: 12px;
  padding: 12px 14px;
  background: #f8f6f0;
  font-size: 14px;
}

.polaroid {
  position: relative;
  background: #f3f0e8;
  box-shadow: 0 6px 14px rgba(19, 11, 8, 0.14);
  padding: 9px 9px 12px;
  color: #1c1714;
}

.memory-link {
  text-decoration: none;
  color: #1c1714;
}

.fav-btn {
  border: 0;
  background: transparent;
  width: 100%;
  margin-top: 4px;
  color: #6f6257;
  cursor: pointer;
  font-size: 16px;
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
  height: 145px;
  border: 1px solid #d9d3c6;
}

.photo-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.one {
  background: linear-gradient(180deg, #8d8d8d 0%, #c6c6c6 50%, #989898 100%);
}

.two {
  background: linear-gradient(180deg, #8f8b84 0%, #c6c0b6 40%, #6f6a62 100%);
}

.three {
  background: radial-gradient(circle at 45% 30%, #cecece 0%, #9f9f9f 50%, #707070 100%);
}

.four {
  background: radial-gradient(circle at 50% 60%, #777 0%, #555 45%, #3f3f3f 100%);
}

.polaroid h2 {
  margin-top: 10px;
  text-align: center;
  font-size: 29px;
  line-height: 0.95;
  font-weight: 500;
}

.memory-meta {
  margin-top: 6px;
  text-align: center;
  color: #6d6158;
  font-size: 11px;
  font-family: var(--font-sans, 'Inter', sans-serif);
}

.tag-list {
  margin-top: 8px;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.tag-chip {
  font-family: Arial, sans-serif;
  font-size: 10px;
  background: #e9e2d6;
  color: #5d4f45;
  padding: 4px 8px;
  border-radius: 999px;
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

.album-actions {
  margin-top: 22px;
  display: flex;
  justify-content: center;
  gap: 10px;
  flex-wrap: wrap;
}

.album-actions .create-btn {
  margin-top: 0;
  width: auto;
  min-width: 200px;
  padding: 17px 16px;
}

.share-panel {
  margin: 28px auto 0;
  max-width: 420px;
  padding: 16px 18px;
  border-radius: 16px;
  background: #f5f2eb;
  border: 1px solid #ddd4c6;
}

.share-heading {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #2d2622;
}

.share-intro {
  margin: 8px 0 12px;
  font-size: 13px;
  line-height: 1.45;
  color: #53473f;
}

.share-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.share-input {
  flex: 1;
  min-width: 180px;
  border: 1px solid #d8d2c5;
  border-radius: 10px;
  padding: 10px 12px;
  font-size: 14px;
  background: #fff;
}

.share-submit {
  border: 1px solid #1e130c;
  border-radius: 999px;
  background: #1e130c;
  color: #fff;
  padding: 10px 18px;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  cursor: pointer;
}

.share-notice {
  margin: 10px 0 0;
  font-size: 13px;
  color: #3d5a40;
}

.collab-list {
  margin: 14px 0 0;
  padding: 0;
  list-style: none;
}

.collab-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  padding: 8px 0;
  border-top: 1px solid #e5dfd4;
  font-size: 12px;
}

.collab-remove {
  flex-shrink: 0;
  border: 1px solid #d6ccbe;
  border-radius: 999px;
  background: #fff;
  padding: 6px 10px;
  font-size: 10px;
  cursor: pointer;
}

.delete-btn {
  border: 1px solid #d6ccbe;
  border-radius: 12px;
  background: #f8f4ed;
  color: #3f342d;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 11px;
  font-weight: 700;
  min-width: 200px;
  padding: 17px 16px;
  cursor: pointer;
}

.leave-album-wrap {
  margin-top: 12px;
  display: flex;
  justify-content: center;
}

.leave-album-btn {
  border: 1px solid #d6ccbe;
  border-radius: 999px;
  background: #fff;
  color: #6d2d2d;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 10px;
  padding: 10px 14px;
  cursor: pointer;
}

.load-more-btn {
  margin: 16px auto 0;
  display: block;
  border: 1px solid #d6ccbe;
  border-radius: 999px;
  background: #f8f4ed;
  color: #3f342d;
  padding: 10px 20px;
  font-family: Arial, sans-serif;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  cursor: pointer;
}

.gallery-overlay {
  position: fixed;
  inset: 0;
  background: rgba(20, 16, 14, 0.82);
  z-index: 50;
  display: grid;
  grid-template-columns: auto minmax(0, 1fr) auto;
  align-items: center;
  gap: 12px;
  padding: 20px;
}

.gallery-close {
  position: absolute;
  right: 16px;
  top: 14px;
  width: 34px;
  height: 34px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.4);
  background: rgba(0, 0, 0, 0.2);
  color: #fff;
  font-size: 24px;
  line-height: 1;
  cursor: pointer;
}

.gallery-arrow {
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  align-self: center;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.5);
  background: rgba(0, 0, 0, 0.2);
  color: #fff;
  font-size: 22px;
  line-height: 1;
  font-family: var(--font-sans, 'Inter', sans-serif);
  padding: 0;
  cursor: pointer;
}

.gallery-arrow:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.gallery-figure {
  margin: 0;
}

.gallery-image {
  width: 100%;
  max-height: min(80vh, 920px);
  object-fit: contain;
  display: block;
}

.gallery-figure figcaption {
  margin-top: 10px;
  text-align: center;
  color: #f3ece2;
  font-family: var(--font-serif, 'EB Garamond', Georgia, serif);
  font-size: 24px;
}

.footer {
  margin-top: 62px;
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

  .subtitle {
    max-width: 640px;
  }

  .album-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
  }

  .skeleton-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
  }

  .photo {
    height: 210px;
  }
}

@keyframes loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

</style>
