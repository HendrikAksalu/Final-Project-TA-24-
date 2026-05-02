<script setup>
import { ref } from 'vue'
import AppHeader from '@/components/AppHeader.vue'

const STORAGE_KEY = 'fototeek_albums'

function loadAlbums() {
  try {
    const parsed = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]')
    return Array.isArray(parsed) ? parsed : []
  } catch (error) {
    return []
  }
}

const albums = ref(loadAlbums())

function saveAlbums() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(albums.value))
}

function createAlbum() {
  const index = albums.value.length + 1
  albums.value.unshift({
    id: Date.now(),
    title: `Uus album ${index}`,
    memories: 0,
    photoClass: 'beach',
    rotate: index % 2 ? 'rotate-right' : '',
  })
  saveAlbums()
}
</script>

<template>
  <main class="page">
    <AppHeader :back-to="'/'" />

    <section class="title">
      <p>Sinu pärand</p>
      <h1>
        Säilitame sinu pereloo ajatuid niite.
      </h1>
    </section>

    <section v-if="albums.length" class="album-grid">
      <RouterLink
        v-for="album in albums"
        :key="album.id || album.title"
        :to="{ path: '/albumid', query: { albumId: album.id } }"
        class="polaroid"
        :class="album.rotate"
      >
        <div class="photo" :class="album.photoClass" />
        <h2>{{ album.title }}</h2>
        <span>{{ album.memories }} mälestust</span>
      </RouterLink>
    </section>
    <section v-else class="empty-state">
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
  line-height: 1.02;
  font-weight: 500;
}

.album-grid {
  margin-top: 24px;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
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

@media (min-width: 768px) {
  .page {
    padding: 28px 28px 40px;
  }

  .title h1 {
    font-size: 72px;
  }

  .album-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
  }
}

@media (min-width: 1024px) {
  .page {
    padding: 34px 40px 52px;
  }

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
