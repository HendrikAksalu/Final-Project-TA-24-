<script setup>
import { computed, nextTick, ref, watch } from 'vue'
import { onBeforeRouteLeave, useRoute, useRouter } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'
import { apiFetch, getToken, logoutSession, normalizeMemoryFromApi, parseApiError } from '@/api/fototeekApi.js'

const route = useRoute()
const router = useRouter()
const editing = ref(false)
const story = ref('')
const storyInput = ref(null)
const whoInput = ref(null)
const whenInput = ref(null)
const whereInput = ref(null)
const titleInput = ref(null)
const whoNameInput = ref(null)
const who = ref('')
const when = ref('')
const where = ref('')
const title = ref('')
const editingTitle = ref(false)
const user = ref(null)
const imageUrl = ref('')
const imageThumbUrl = ref('')
const fileInput = ref(null)
const markingFace = ref(false)
const faceMarkers = ref([])
const pendingFaceName = ref('')
const draftWhoName = ref('')
const dragFaceStart = ref(null)
const draftFaceMarker = ref(null)
const memorySaveError = ref('')
/** Kui kasutaja valis pildi enne kui API mälestuse kirje laeb / vältib watch()-i tühjaks kirjutamist */
const pendingImageDraftForMemoryId = ref(null)

try {
  user.value = JSON.parse(localStorage.getItem('fototeek_user') || 'null')
} catch (error) {
  user.value = null
}

const isLoggedIn = computed(() => Boolean(user.value && getToken()))

function suggestedNameFromWho(rawWho) {
  const raw = String(rawWho || '').trim()
  if (!raw) return 'Nimi puudu'
  return raw.split(',')[0].trim() || 'Nimi puudu'
}

const memories = ref([])
const orderedMemories = computed(() =>
  [...memories.value].sort((a, b) => Number(a?.id || 0) - Number(b?.id || 0)),
)

const currentMemory = computed(() =>
  memories.value.find((item) => String(item.id) === String(route.query.memoryId)),
)
const currentMemoryIndex = computed(() =>
  orderedMemories.value.findIndex((item) => String(item.id) === String(route.query.memoryId)),
)

const albumMyRole = ref(null)

async function refreshMemoriesFromApi() {
  const aid = route.query.albumId
  if (!aid) {
    memories.value = []
    return
  }
  const res = await apiFetch(`/albums/${aid}/memories`)
  if (!res.ok) {
    memories.value = []
    return
  }
  const data = await res.json()
  memories.value = (data.memories || []).map(normalizeMemoryFromApi).filter(Boolean)
}

async function refreshAlbumAccess() {
  const aid = route.query.albumId
  if (!aid) {
    albumMyRole.value = null
    return
  }
  const res = await apiFetch(`/albums/${aid}`)
  if (!res.ok) {
    albumMyRole.value = null
    return
  }
  const data = await res.json()
  albumMyRole.value = data.album?.myRole ?? null
}

watch(
  () => route.query.albumId,
  () => {
    void Promise.all([refreshMemoriesFromApi(), refreshAlbumAccess()])
  },
  { immediate: true },
)

const canEditMemory = computed(() => albumMyRole.value === 'owner' || albumMyRole.value === 'editor')

let memorySaveTimer = null

async function flushSaveCurrentMemory() {
  const rid = route.query.memoryId
  if (rid === undefined || rid === null || String(rid).trim() === '') return

  const id = rid
  const body = {
    title: title.value,
    story: story.value,
    who: who.value,
    when: when.value,
    where: where.value,
    imageUrl: imageUrl.value,
    imageThumbUrl: imageThumbUrl.value,
    faceMarkers: faceMarkers.value,
  }

  try {
    const res = await apiFetch(`/memories/${id}`, { method: 'PATCH', body })
    if (!res.ok) {
      if (res.status === 413) {
        memorySaveError.value = 'Pildi maht on serveri jaoks liiga suur. Proovi väiksemat või madalama kvaliteediga faili.'
      } else if (res.status >= 500) {
        memorySaveError.value = 'Serveri viga pildi salvestamisel. Proovi väiksemat JPG/PNG pilti.'
      } else {
        memorySaveError.value = await parseApiError(res, 'Salvestamine ebaõnnestus.')
      }
      return
    }

    memorySaveError.value = ''
    pendingImageDraftForMemoryId.value = null

    try {
      const json = await res.json()
      if (json?.memory) {
        const normalized = normalizeMemoryFromApi(json.memory)
        const idx = memories.value.findIndex((m) => String(m.id) === String(id))
        if (idx !== -1 && normalized) {
          memories.value[idx] = normalized
        }
      }
    } catch (error) {
      // ignore JSON errors
    }
  } catch (error) {
    memorySaveError.value = 'Serveriga ei saanud ühendust. Kontrolli võrku või API aadressi.'
  }
}

function scheduleSaveCurrentMemory() {
  clearTimeout(memorySaveTimer)
  memorySaveTimer = setTimeout(() => {
    void flushSaveCurrentMemory()
  }, 450)
}

onBeforeRouteLeave(async () => {
  clearTimeout(memorySaveTimer)
  memorySaveTimer = null
  await flushSaveCurrentMemory()
})

const memoryTitle = computed(() => title.value || route.query.title || 'Mälestus')
const whoNames = computed(() =>
  who.value
    .split(',')
    .map((name) => name.trim())
    .filter(Boolean),
)
function downloadPlaceholder() {
  if (!imageUrl.value) {
    alert('Allalaadimiseks lisa kõigepealt pilt.')
    return
  }

  const link = document.createElement('a')
  link.href = imageUrl.value
  const safeTitle = String(memoryTitle.value || 'malestus')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
  link.download = `${safeTitle || 'malestus'}.webp`
  document.body.appendChild(link)
  link.click()
  link.remove()
}

function openImagePicker() {
  if (!canEditMemory.value) return
  fileInput.value?.click()
}

function getPointerPercent(event) {
  const rect = event.currentTarget.getBoundingClientRect()
  const x = ((event.clientX - rect.left) / rect.width) * 100
  const y = ((event.clientY - rect.top) / rect.height) * 100
  return {
    x: Math.min(100, Math.max(0, x)),
    y: Math.min(100, Math.max(0, y)),
  }
}

function updateDraftFaceMarker(current) {
  if (!dragFaceStart.value) return
  const start = dragFaceStart.value
  const width = Math.abs(current.x - start.x)
  const height = Math.abs(current.y - start.y)
  const centerX = (start.x + current.x) / 2
  const centerY = (start.y + current.y) / 2
  draftFaceMarker.value = {
    x: centerX,
    y: centerY,
    width: Math.max(width, 4),
    height: Math.max(height, 4),
    name: pendingFaceName.value || 'Nimi puudu',
  }
}

function onFaceDragStart(event) {
  if (!canEditMemory.value || !markingFace.value || !imageUrl.value) return
  const point = getPointerPercent(event)
  dragFaceStart.value = point
  draftFaceMarker.value = {
    x: point.x,
    y: point.y,
    width: 4,
    height: 4,
    name: pendingFaceName.value || 'Nimi puudu',
  }
}

function onFaceDragMove(event) {
  if (!dragFaceStart.value) return
  updateDraftFaceMarker(getPointerPercent(event))
}

function onFaceDragEnd() {
  if (!dragFaceStart.value || !draftFaceMarker.value) return
  faceMarkers.value.push({
    id: Date.now(),
    ...draftFaceMarker.value,
  })
  dragFaceStart.value = null
  draftFaceMarker.value = null
  markingFace.value = false
  pendingFaceName.value = ''
  saveCurrentMemory()
}

function loadImageFromFile(file) {
  return new Promise((resolve, reject) => {
    const url = URL.createObjectURL(file)
    const img = new Image()
    img.onload = () => {
      URL.revokeObjectURL(url)
      resolve(img)
    }
    img.onerror = () => {
      URL.revokeObjectURL(url)
      reject(new Error('Pildi laadimine ebaõnnestus.'))
    }
    img.src = url
  })
}

function resizeImageToDataUrl(image, maxSide, quality = 0.82) {
  const largestSide = Math.max(image.naturalWidth, image.naturalHeight)
  const scale = largestSide > maxSide ? maxSide / largestSide : 1
  const width = Math.max(1, Math.round(image.naturalWidth * scale))
  const height = Math.max(1, Math.round(image.naturalHeight * scale))
  const canvas = document.createElement('canvas')
  canvas.width = width
  canvas.height = height
  const ctx = canvas.getContext('2d')
  if (!ctx) return ''
  ctx.drawImage(image, 0, 0, width, height)

  let webp = ''
  try {
    webp = canvas.toDataURL('image/webp', quality)
  } catch {
    webp = ''
  }
  if (webp.startsWith('data:image/webp') && webp.length > 120) {
    return webp
  }

  try {
    return canvas.toDataURL('image/jpeg', Math.min(0.92, quality + 0.06))
  } catch {
    return ''
  }
}

function buildOptimizedImage(image, { maxSide, quality, maxLength }) {
  let data = resizeImageToDataUrl(image, maxSide, quality)
  if (data && data.length <= maxLength) return data

  data = resizeImageToDataUrl(image, Math.max(620, Math.round(maxSide * 0.72)), Math.max(0.48, quality - 0.14))
  if (data && data.length <= maxLength) return data

  data = resizeImageToDataUrl(image, Math.max(520, Math.round(maxSide * 0.6)), 0.46)
  if (data && data.length <= maxLength) return data

  data = resizeImageToDataUrl(image, Math.max(460, Math.round(maxSide * 0.5)), 0.4)
  if (data && data.length <= maxLength) return data

  return ''
}

async function onImageSelected(event) {
  if (!canEditMemory.value) return
  const [file] = event.target.files || []
  if (!file) return
  if (!file.type.startsWith('image/')) return

  try {
    const image = await loadImageFromFile(file)
    imageUrl.value = buildOptimizedImage(image, { maxSide: 1024, quality: 0.68, maxLength: 700000 })
    imageThumbUrl.value = buildOptimizedImage(image, { maxSide: 300, quality: 0.56, maxLength: 120000 })
    if (!imageUrl.value || !imageThumbUrl.value) {
      memorySaveError.value = 'Pilt on liiga suur või formaati ei õnnestunud töödelda. Proovi väiksemat JPG/PNG faili.'
      return
    }
    pendingImageDraftForMemoryId.value = String(route.query.memoryId ?? '')
    saveCurrentMemory()
    clearTimeout(memorySaveTimer)
    memorySaveTimer = null
    await flushSaveCurrentMemory()
  } catch (error) {
    alert('Pildi lisamine ebaõnnestus. Proovi teise failiga.')
  } finally {
    event.target.value = ''
  }
}

async function logout() {
  await logoutSession()
  user.value = null
  router.push('/')
}

function startEditingTitle() {
  if (!canEditMemory.value) return
  editingTitle.value = true
  nextTick(() => {
    titleInput.value?.focus()
    titleInput.value?.select?.()
  })
}

function saveTitle() {
  title.value = title.value.trim() || 'Mälestus'
  editingTitle.value = false
  saveCurrentMemory()
}

function markFace() {
  if (!canEditMemory.value) return
  if (!imageUrl.value) {
    alert('Lisa enne pilt, siis saad nägu märkida.')
    return
  }
  if (whoNames.value.length) {
    const options = whoNames.value.map((name, index) => `${index + 1}. ${name}`).join('\n')
    const entered = window.prompt(`Kelle nägu märgid?\nSisesta nimi või number:\n${options}`, '')
    if (entered === null) return
    const cleaned = entered.trim()
    if (!cleaned) return
    const asIndex = Number.parseInt(cleaned, 10)
    if (Number.isInteger(asIndex) && asIndex >= 1 && asIndex <= whoNames.value.length) {
      pendingFaceName.value = whoNames.value[asIndex - 1]
    } else {
      pendingFaceName.value = cleaned
    }
  } else {
    const entered = window.prompt('Kelle nägu märgid?', '')
    if (entered === null) return
    pendingFaceName.value = entered.trim() || 'Nimi puudu'
  }
  markingFace.value = true
}

async function goBackToAlbum() {
  clearTimeout(memorySaveTimer)
  memorySaveTimer = null
  await flushSaveCurrentMemory()
  await router.push({ path: '/album', query: { albumId: route.query.albumId } })
}

function goToAdjacentMemory(direction) {
  if (!orderedMemories.value.length || currentMemoryIndex.value === -1) return
  const nextIndex = currentMemoryIndex.value + direction
  if (nextIndex < 0 || nextIndex >= orderedMemories.value.length) return
  const target = orderedMemories.value[nextIndex]
  router.push({
    path: '/malestus',
    query: {
      albumId: route.query.albumId,
      memoryId: target.id,
      title: target.title || 'Mälestus',
    },
  })
}

function removeFaceMarker(markerId) {
  if (!canEditMemory.value) return
  faceMarkers.value = faceMarkers.value.filter((marker) => marker.id !== markerId)
  saveCurrentMemory()
}

function removeImage() {
  if (!canEditMemory.value) return
  pendingImageDraftForMemoryId.value = null
  imageUrl.value = ''
  imageThumbUrl.value = ''
  faceMarkers.value = []
  markingFace.value = false
  pendingFaceName.value = ''
  draftFaceMarker.value = null
  dragFaceStart.value = null
  saveCurrentMemory()
}

function saveCurrentMemory() {
  const rid = route.query.memoryId
  if (rid === undefined || rid === null || String(rid).trim() === '') return

  const index = memories.value.findIndex((item) => String(item.id) === String(rid))
  if (index !== -1) {
    memories.value[index] = {
      ...memories.value[index],
      title: title.value,
      story: story.value,
      who: who.value,
      when: when.value,
      where: where.value,
      imageUrl: imageUrl.value,
      imageThumbUrl: imageThumbUrl.value,
      faceMarkers: faceMarkers.value,
    }
  }
  if (canEditMemory.value) {
    scheduleSaveCurrentMemory()
  }
}

function toggleEditing() {
  if (!canEditMemory.value) return
  if (editing.value) saveCurrentMemory()
  editing.value = !editing.value
}

function startEditing(field) {
  if (!canEditMemory.value) return
  editing.value = true
  nextTick(() => {
    const fieldMap = {
      story: storyInput.value,
      who: whoNameInput.value,
      when: whenInput.value,
      where: whereInput.value,
    }
    fieldMap[field]?.focus()
  })
}

function syncWhoFromNames(names) {
  who.value = names.join(', ')
  saveCurrentMemory()
}

function addWhoName() {
  const nextName = draftWhoName.value.trim()
  if (!nextName) return
  syncWhoFromNames([...whoNames.value, nextName])
  draftWhoName.value = ''
  nextTick(() => whoNameInput.value?.focus())
}

function removeWhoName(nameToRemove) {
  const index = whoNames.value.findIndex((name) => name === nameToRemove)
  if (index === -1) return
  const next = [...whoNames.value]
  next.splice(index, 1)
  syncWhoFromNames(next)
}

watch(
  currentMemory,
  (memory) => {
    story.value = memory?.story || ''
    who.value = memory?.who || ''
    when.value = memory?.when || ''
    where.value = memory?.where || ''
    title.value = memory?.title || ''

    const routeMid = String(route.query.memoryId ?? '')
    const pendingHere =
      pendingImageDraftForMemoryId.value !== null && pendingImageDraftForMemoryId.value === routeMid
    const serverHasImage = Boolean(memory?.imageUrl || memory?.imageThumbUrl)
    if (!pendingHere || serverHasImage) {
      imageUrl.value = memory?.imageUrl || ''
      imageThumbUrl.value = memory?.imageThumbUrl || ''
    }

    if (Array.isArray(memory?.faceMarkers)) {
      faceMarkers.value = memory.faceMarkers.map((marker) => ({
        ...marker,
        size: Number(marker?.size) > 0 ? Number(marker.size) : 64,
      }))
    } else if (memory?.faceMarker) {
      faceMarkers.value = [
        { id: Date.now(), ...memory.faceMarker, name: suggestedNameFromWho(memory?.who), size: 64 },
      ]
    } else {
      faceMarkers.value = []
    }
    markingFace.value = false
    pendingFaceName.value = ''
    dragFaceStart.value = null
    draftFaceMarker.value = null
  },
  { immediate: true },
)
</script>

<template>
  <main class="page page-shell">
    <div class="header-wrap">
      <AppHeader :show-auth-links="!isLoggedIn" :show-menu="isLoggedIn" @logout="logout" />
    </div>

    <section class="hero-stack">
      <section class="hero-card">
        <div
          class="hero-photo"
          :class="{ 'marking-face': markingFace, 'has-image': imageUrl }"
          @pointerdown="onFaceDragStart"
          @pointermove="onFaceDragMove"
          @pointerup="onFaceDragEnd"
          @pointercancel="onFaceDragEnd"
          @pointerleave="onFaceDragEnd"
        >
          <img v-if="imageUrl" :src="imageUrl" alt="" class="hero-photo-img" />
          <span
            v-for="marker in faceMarkers"
            :key="marker.id"
            class="face-marker"
            :class="{ visible: markingFace }"
            :style="{
              left: `${marker.x}%`,
              top: `${marker.y}%`,
              width: `${marker.width || 8}%`,
              height: `${marker.height || 8}%`,
            }"
          >
            <span class="face-marker-label">{{ marker.name }}</span>
            <button
              v-if="canEditMemory"
              type="button"
              class="face-marker-remove"
              aria-label="Eemalda see märge"
              @click.stop="removeFaceMarker(marker.id)"
            >
              ×
            </button>
          </span>
          <span
            v-if="draftFaceMarker"
            class="face-marker visible draft"
            :style="{
              left: `${draftFaceMarker.x}%`,
              top: `${draftFaceMarker.y}%`,
              width: `${draftFaceMarker.width}%`,
              height: `${draftFaceMarker.height}%`,
            }"
          >
            <span class="face-marker-label">{{ draftFaceMarker.name }}</span>
          </span>
        </div>
        <input ref="fileInput" type="file" accept="image/*" class="sr-only" @change="onImageSelected" />
        <p v-if="!editingTitle" class="memory-title editable-value" @click="startEditingTitle">{{ memoryTitle }}</p>
        <input
          v-else
          ref="titleInput"
          v-model="title"
          type="text"
          class="memory-title-input"
          @blur="saveTitle"
          @keydown.enter.prevent="saveTitle"
        />
      </section>
      <section v-if="orderedMemories.length > 1" class="gallery-nav">
        <button
          type="button"
          class="gallery-arrow"
          :disabled="currentMemoryIndex <= 0"
          @click="goToAdjacentMemory(-1)"
        >
          ← Eelmine
        </button>
        <p>{{ currentMemoryIndex + 1 }} / {{ orderedMemories.length }}</p>
        <button
          type="button"
          class="gallery-arrow"
          :disabled="currentMemoryIndex >= orderedMemories.length - 1"
          @click="goToAdjacentMemory(1)"
        >
          Järgmine →
        </button>
      </section>
    </section>

    <p v-if="albumMyRole === 'viewer'" class="viewer-banner">
      Oled selle jagatud albumi vaatajana — mälestuse teksti ja pilte sa muuta ei saa.
    </p>

    <section class="title">
      <p>Sinu pärand</p>
      <h1>Säilitame sinu pereloo ajatuid niite.</h1>
      <p v-if="!editing && story" class="story editable-value" @click="startEditing('story')">{{ story }}</p>
      <p v-else-if="!editing" class="story placeholder editable-value" @click="startEditing('story')">
        Lisa siia pildi lugu...
      </p>
      <textarea
        v-else
        ref="storyInput"
        v-model="story"
        class="story-editor"
        rows="5"
        placeholder="Lisa siia pildi lugu..."
      />
    </section>

    <section class="info-list">
      <article class="info-card">
        <span class="icon">👥</span>
        <div>
          <p class="label">Kes</p>
          <div v-if="!editing && whoNames.length" class="who-chips editable-value" @click="startEditing('who')">
            <span v-for="name in whoNames" :key="name" class="who-chip">{{ name }}</span>
          </div>
          <p v-else-if="!editing" class="editable-value placeholder-text" @click="startEditing('who')">
            Lisa siia, kes on fotol...
          </p>
          <div v-else class="who-edit-wrap">
            <div v-if="whoNames.length" class="who-chips">
              <span v-for="name in whoNames" :key="name" class="who-chip editing">
                {{ name }}
                <button type="button" class="chip-remove-btn" @click="removeWhoName(name)">×</button>
              </span>
            </div>
            <div class="who-input-row">
              <input
                ref="whoNameInput"
                v-model="draftWhoName"
                type="text"
                class="field-input"
                placeholder="Lisa nimi..."
                @keydown.enter.prevent="addWhoName"
              />
              <button type="button" class="add-name-btn" @click="addWhoName">Lisa</button>
            </div>
          </div>
        </div>
      </article>
      <article class="info-card">
        <span class="icon">📅</span>
        <div>
          <p class="label">Millal</p>
          <p v-if="!editing && when" class="editable-value" @click="startEditing('when')">{{ when }}</p>
          <p v-else-if="!editing" class="editable-value placeholder-text" @click="startEditing('when')">
            Lisa siia aeg...
          </p>
          <input v-else ref="whenInput" v-model="when" type="text" class="field-input" />
        </div>
      </article>
      <article class="info-card">
        <span class="icon">📍</span>
        <div>
          <p class="label">Kus</p>
          <p v-if="!editing && where" class="editable-value" @click="startEditing('where')">{{ where }}</p>
          <p v-else-if="!editing" class="editable-value placeholder-text" @click="startEditing('where')">
            Lisa siia asukoht...
          </p>
          <input v-else ref="whereInput" v-model="where" type="text" class="field-input" />
        </div>
      </article>
    </section>

    <section class="action-stack">
      <div class="actions">
        <button v-if="canEditMemory" type="button" class="action-btn edit" @click="toggleEditing">
          {{ editing ? 'Salvesta' : 'Muuda' }}
        </button>
        <button type="button" class="action-btn" @click="downloadPlaceholder">⇩</button>
      </div>
      <p v-if="memorySaveError" class="memory-save-error" role="alert">{{ memorySaveError }}</p>
      <div class="secondary-actions">
        <template v-if="canEditMemory">
          <button type="button" class="secondary-btn" @click="openImagePicker">Lisa pilt</button>
          <button v-if="imageUrl" type="button" class="secondary-btn" @click="removeImage">Eemalda pilt</button>
          <button type="button" class="secondary-btn" @click="markFace">Märgi nägu</button>
        </template>
        <button type="button" class="secondary-btn" @click="goBackToAlbum">Piltide juurde</button>
      </div>
    </section>

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
.header-wrap {
  position: relative;
}

.hero-card {
  margin-top: 24px;
  background: #f3f0e8;
  box-shadow: 0 6px 14px rgba(19, 11, 8, 0.14);
  padding: 12px 12px 16px;
  width: min(100%, 560px);
}

.hero-stack {
  margin-top: 24px;
}

.hero-photo {
  position: relative;
  width: 100%;
  aspect-ratio: 3 / 4;
  border: 1px solid #d9d3c6;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  overflow: hidden;
}

.hero-photo.has-image {
  aspect-ratio: auto;
  height: auto;
  background: transparent;
  display: block;
}

.hero-photo.marking-face {
  outline: 2px dashed rgba(60, 44, 35, 0.55);
  outline-offset: -6px;
  cursor: crosshair;
}

.hero-photo-img {
  width: 100%;
  height: auto;
  object-fit: initial;
  display: block;
}

.hero-photo-placeholder {
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: rgba(60, 44, 35, 0.78);
  background: rgba(248, 244, 237, 0.82);
  border: 1px solid rgba(214, 204, 190, 0.9);
  border-radius: 999px;
  padding: 10px 14px;
}

.face-marker {
  position: absolute;
  transform: translate(-50%, -50%);
  border: 2px solid #fff;
  border-radius: 6px;
  background: rgba(30, 19, 12, 0.14);
  box-shadow: 0 0 0 2px rgba(30, 19, 12, 0.35);
  pointer-events: auto;
  opacity: 0;
  transition: opacity 0.18s ease;
}

.hero-photo:hover .face-marker,
.face-marker.visible {
  opacity: 1;
}

.face-marker-label {
  position: absolute;
  left: 50%;
  top: calc(100% + 6px);
  transform: translateX(-50%);
  background: rgba(30, 19, 12, 0.9);
  color: #fff;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 10px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  padding: 4px 8px;
  border-radius: 999px;
  white-space: nowrap;
}

.face-marker-remove {
  position: absolute;
  right: -10px;
  top: -10px;
  width: 20px;
  height: 20px;
  border: 1px solid #fff;
  border-radius: 999px;
  background: rgba(30, 19, 12, 0.95);
  color: #fff;
  font-size: 14px;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 0;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.hero-card p {
  margin-top: 12px;
  text-align: center;
  font-style: italic;
  color: #5f5349;
}

.memory-title {
  cursor: text;
}

.memory-title-input {
  margin-top: 12px;
  width: 100%;
  border: 1px solid #d8d2c5;
  border-radius: 10px;
  background: #f8f6f0;
  padding: 8px 10px;
  text-align: center;
  font-family: var(--font-serif, 'EB Garamond', Georgia, serif);
  font-size: 18px;
  font-style: italic;
  color: #5f5349;
}

.gallery-nav {
  margin-top: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  flex-wrap: wrap;
}

.gallery-nav p {
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 11px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #5e5348;
}

.gallery-arrow {
  border: 1px solid #d6ccbe;
  border-radius: 999px;
  background: #f8f4ed;
  color: #3f342d;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  padding: 8px 12px;
  cursor: pointer;
}

.gallery-arrow:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.title {
  margin-top: 22px;
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
  font-size: 46px;
  line-height: 1.04;
  font-weight: 500;
}

.story {
  margin: 16px auto 0;
  max-width: 290px;
  text-transform: none !important;
  letter-spacing: 0 !important;
  font-family: Georgia, 'Times New Roman', serif !important;
  font-style: italic;
  color: #53473f;
  font-size: 18px !important;
  line-height: 1.3;
}

.story.placeholder {
  color: #8f8478;
}

.editable-value {
  cursor: text;
}

.placeholder-text {
  color: #8f8478;
}

.who-chips {
  margin-top: 4px;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.who-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: 1px solid #d8d2c5;
  border-radius: 999px;
  background: #f8f6f0;
  padding: 4px 9px;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 11px;
  color: #3f342d;
}

.who-chip.editing {
  background: #efe8dc;
}

.who-edit-wrap {
  margin-top: 4px;
}

.who-input-row {
  margin-top: 6px;
  display: flex;
  gap: 6px;
}

.chip-remove-btn {
  border: 0;
  background: transparent;
  color: #5b4f45;
  font-size: 14px;
  line-height: 1;
  cursor: pointer;
  padding: 0;
}

.add-name-btn {
  border: 1px solid #d6ccbe;
  border-radius: 999px;
  background: #f8f4ed;
  color: #3f342d;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  padding: 0 10px;
  cursor: pointer;
}

.story-editor {
  margin: 16px auto 0;
  display: block;
  width: 100%;
  max-width: 300px;
  border: 1px solid #d8d2c5;
  border-radius: 10px;
  background: #f8f6f0;
  padding: 10px;
  font-family: Georgia, 'Times New Roman', serif;
}

.info-list {
  margin-top: 18px;
  display: grid;
  gap: 10px;
}

.info-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #f4f1ea;
  border-radius: 12px;
  padding: 11px 12px;
}

.icon {
  width: 26px;
  height: 26px;
  border-radius: 999px;
  background: #e5dfd4;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
}

.label {
  text-transform: uppercase;
  letter-spacing: 0.15em;
  font-family: Arial, sans-serif;
  font-size: 9px;
  color: #8f8478;
}

.info-card p:last-child {
  margin-top: 2px;
  color: #3f342d;
}

.field-input {
  margin-top: 4px;
  width: 100%;
  border: 1px solid #d8d2c5;
  border-radius: 8px;
  padding: 6px 8px;
  background: #f8f6f0;
  font-family: Georgia, 'Times New Roman', serif;
}

.viewer-banner {
  margin: 16px auto 0;
  max-width: 420px;
  padding: 10px 14px;
  text-align: center;
  font-size: 13px;
  color: #3d4a63;
  background: #edf2f7;
  border-radius: 10px;
}

.actions {
  margin-top: 18px;
  display: flex;
  justify-content: center;
  gap: 8px;
}

.action-stack {
  margin-top: 0;
}

.memory-save-error {
  margin: 12px auto 0;
  max-width: 520px;
  padding: 10px 12px;
  border-radius: 8px;
  background: #fdecef;
  color: #7a1f2e;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 12px;
  text-align: center;
}

.secondary-actions {
  margin-top: 10px;
  display: flex;
  justify-content: center;
  gap: 8px;
  flex-wrap: wrap;
}

.secondary-btn {
  border: 1px solid #d6ccbe;
  border-radius: 999px;
  background: #f8f4ed;
  color: #3f342d;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  padding: 9px 12px;
  cursor: pointer;
}

.secondary-btn:hover {
  background: #f1ebdf;
}

.action-btn {
  border: 0;
  background: #1e130c;
  color: #fff;
  border-radius: 999px;
  width: 42px;
  height: 32px;
  font-size: 16px;
  cursor: pointer;
}

.action-btn.edit {
  width: 82px;
  font-family: Arial, sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 11px;
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

@media (min-width: 768px) {
  .hero-card {
    max-width: 620px;
    margin-left: auto;
    margin-right: auto;
  }

  .hero-photo {
    aspect-ratio: 3 / 4;
  }

  .title h1 {
    font-size: 60px;
  }

  .story,
  .story-editor {
    max-width: 640px;
  }

  .info-list {
    max-width: 760px;
    margin-left: auto;
    margin-right: auto;
  }
}

@media (min-width: 1024px) {
  .page {
    display: grid;
    grid-template-columns: minmax(360px, 0.42fr) minmax(0, 0.58fr);
    grid-template-areas:
      'header header'
      'hero title'
      'hero info'
      'hero actions'
      'footer footer';
    gap: 26px 48px;
    align-items: start;
  }

  .page > :first-child {
    grid-area: header;
  }

  .hero-stack {
    grid-area: hero;
    margin-top: 8px;
    width: min(100%, 560px);
  }

  .hero-card {
    margin-top: 0;
    width: min(100%, 560px);
  }

  .hero-photo {
    aspect-ratio: 3 / 4;
  }

  .title {
    grid-area: title;
    margin-top: 8px;
    text-align: left;
  }

  .title h1 {
    font-size: 58px;
    line-height: 1.03;
    max-width: 12ch;
  }

  .story,
  .story-editor {
    max-width: none;
    margin-left: 0;
    margin-right: 0;
  }

  .info-list {
    grid-area: info;
    max-width: none;
    margin: 0;
  }

  .actions {
    justify-content: flex-start;
    margin-top: 0;
  }

  .action-stack {
    grid-area: actions;
  }

  .secondary-actions {
    justify-content: flex-start;
    margin-top: 10px;
  }

  .footer {
    grid-area: footer;
    margin-top: 28px;
  }
}
</style>
