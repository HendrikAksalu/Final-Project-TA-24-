<script setup>
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'

const route = useRoute()
const ALBUMS_KEY = 'fototeek_albums'

function loadAlbums() {
  try {
    const parsed = JSON.parse(localStorage.getItem(ALBUMS_KEY) || '[]')
    return Array.isArray(parsed) ? parsed : []
  } catch (error) {
    return []
  }
}

const albums = ref(loadAlbums())

const currentAlbum = computed(() =>
  albums.value.find((album) => String(album.id) === String(route.query.albumId)),
)

const memoriesKey = computed(() => `fototeek_memories_${route.query.albumId || 'none'}`)

function loadMemories() {
  try {
    const parsed = JSON.parse(localStorage.getItem(memoriesKey.value) || '[]')
    return Array.isArray(parsed) ? parsed : []
  } catch (error) {
    return []
  }
}

const memories = ref(loadMemories())
const searchQuery = ref('')
const photoClasses = ['one', 'two', 'three', 'four']

function getMemoryTags(memory) {
  const tags = []
  if (memory.who) tags.push(...memory.who.split(',').map((part) => part.trim()).filter(Boolean))
  if (memory.where) tags.push(...memory.where.split(',').map((part) => part.trim()).filter(Boolean))
  return [...new Set(tags)]
}

const filteredMemories = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  if (!query) return memories.value

  return memories.value.filter((memory) => {
    const searchable = [memory.title, memory.who, memory.where, ...getMemoryTags(memory)]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()
    return searchable.includes(query)
  })
})

function saveMemories() {
  localStorage.setItem(memoriesKey.value, JSON.stringify(memories.value))
  if (!currentAlbum.value) return

  const index = albums.value.findIndex((album) => String(album.id) === String(currentAlbum.value.id))
  if (index === -1) return

  albums.value[index] = { ...albums.value[index], memories: memories.value.length }
  localStorage.setItem(ALBUMS_KEY, JSON.stringify(albums.value))
}

function addMemory() {
  const nextIndex = memories.value.length + 1
  memories.value.unshift({
    id: Date.now(),
    title: `Uus mälestus ${nextIndex}`,
    photoClass: photoClasses[nextIndex % photoClasses.length],
    favorite: false,
    rotate: nextIndex % 2 ? 'rotate-right' : '',
    story: '',
    who: '',
    when: '',
    where: '',
  })
  saveMemories()
}

function toggleFavorite(id) {
  const memory = memories.value.find((item) => item.id === id)
  if (memory) {
    memory.favorite = !memory.favorite
    saveMemories()
  }
}
</script>

<template>
  <main class="page">
    <AppHeader :back-to="'/parand'" />

    <section class="title">
      <p>Perearhiiv</p>
      <h1>{{ currentAlbum?.title || 'Album' }}</h1>
      <p class="subtitle">
        Säilitame sinu pere ajaloo puudutatava olemuse püsivas ja kaunis
        digitaalses arhiivis.
      </p>
    </section>

    <section v-if="!currentAlbum" class="empty-state">
      <p>Albumit ei leitud.</p>
      <p>Mine tagasi ja loo album enne, kui lisad mälestusi.</p>
    </section>

    <div v-if="currentAlbum" class="search-wrap">
      <input v-model="searchQuery" type="text" placeholder="Otsi nime või koha järgi..." class="search-input" />
    </div>

    <section v-if="currentAlbum && filteredMemories.length" class="album-grid">
      <article v-for="memory in filteredMemories" :key="memory.id" class="polaroid" :class="memory.rotate">
        <RouterLink
          :to="{ path: '/malestus', query: { title: memory.title, albumId: route.query.albumId, memoryId: memory.id } }"
          class="memory-link"
        >
          <div class="photo" :class="memory.photoClass" />
          <h2>{{ memory.title }}</h2>
          <div v-if="getMemoryTags(memory).length" class="tag-list">
            <span v-for="tag in getMemoryTags(memory)" :key="tag" class="tag-chip">#{{ tag }}</span>
          </div>
        </RouterLink>
        <button type="button" class="fav-btn" @click="toggleFavorite(memory.id)">
          {{ memory.favorite ? '★' : '☆' }}
        </button>
      </article>
    </section>
    <section v-else-if="currentAlbum" class="empty-state">
      <p>Selles albumis pole veel mälestusi.</p>
      <p>Lisa esimene mälestus, et album täituma hakkaks.</p>
    </section>
    <button v-if="currentAlbum" type="button" class="create-btn" @click="addMemory">
      Lisa mälestus
    </button>

    <footer class="footer">
      <nav>
        <a href="#">Meist</a>
        <a href="#">Privaatsus</a>
        <a href="#">Eetika</a>
      </nav>
      <p class="bookmark">◫</p>
      <p class="copyright">© 2025 Fototeek</p>
      <p class="note">Hoiame meie esivanemate lugusid.</p>
    </footer>
  </main>
</template>

<style scoped>
.page {
  max-width: 1120px;
  margin: 0 auto;
  padding: 16px 14px 28px;
  color: #1c1714;
  font-family: Georgia, 'Times New Roman', serif;
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

.album-grid {
  margin-top: 24px;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
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

.footer {
  margin-top: 52px;
  text-align: center;
}

.footer nav {
  display: flex;
  justify-content: center;
  gap: 24px;
  text-transform: uppercase;
  letter-spacing: 0.13em;
  font-size: 10px;
  font-family: Arial, sans-serif;
}

.footer a {
  color: #1c1714;
  text-decoration: none;
}

.bookmark {
  margin-top: 14px;
  color: #8e8276;
}

.copyright {
  margin-top: 12px;
  text-transform: uppercase;
  letter-spacing: 0.11em;
  font-size: 9px;
  font-family: Arial, sans-serif;
  color: #7f7266;
}

.note {
  margin-top: 6px;
  font-style: italic;
  font-size: 12px;
  color: #938578;
}

@media (min-width: 900px) {
  .page {
    padding: 28px 28px 40px;
  }

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

  .photo {
    height: 210px;
  }
}
</style>
