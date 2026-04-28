<script setup>
import { ref } from 'vue'

const albums = ref([
  { title: 'Summer of 64', memories: 24, photoClass: 'beach', rotate: '' },
  { title: "Grandpa's shop", memories: 18, photoClass: 'workshop', rotate: 'rotate-right' },
  { title: 'Wedding day', memories: 42, photoClass: 'portrait', rotate: 'rotate-left' },
  { title: 'The old farm', memories: 56, photoClass: 'barn', rotate: '' },
])

function createAlbum() {
  const index = albums.value.length + 1
  albums.value.unshift({
    title: `New Album ${index}`,
    memories: 0,
    photoClass: 'beach',
    rotate: index % 2 ? 'rotate-right' : '',
  })
}
</script>

<template>
  <main class="page">
    <header class="topbar">
      <RouterLink to="/" class="icon-link" aria-label="Go back">←</RouterLink>
      <div class="brand-wrap">
        <span class="book-icon">◧</span>
        <span class="brand-text">Fototeek</span>
      </div>
      <button class="icon-link" type="button" aria-label="More actions">⋮</button>
    </header>

    <section class="title">
      <p>Your heritage</p>
      <h1>
        Curating the timeless threads of your family's tapestry.
      </h1>
    </section>

    <section class="album-grid">
      <RouterLink
        v-for="album in albums"
        :key="album.title"
        to="/album"
        class="polaroid"
        :class="album.rotate"
      >
        <div class="photo" :class="album.photoClass" />
        <h2>{{ album.title }}</h2>
        <span>{{ album.memories }} Memories</span>
      </RouterLink>
    </section>

    <button type="button" class="create-btn" @click="createAlbum">Create new album</button>

    <footer class="footer">
      <nav>
        <a href="#">About</a>
        <a href="#">Privacy</a>
        <a href="#">Ethics</a>
      </nav>
      <p class="bookmark">◫</p>
      <p class="copyright">© 2024 Fototeek Physical Archives</p>
      <p class="note">Preserving the narrative of our ancestors.</p>
    </footer>
  </main>
</template>

<style scoped>
.page {
  max-width: 390px;
  margin: 0 auto;
  padding: 16px 14px 28px;
  color: #1c1714;
  font-family: Georgia, 'Times New Roman', serif;
}

.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.icon-link {
  border: none;
  background: transparent;
  text-decoration: none;
  color: #1c1714;
  font-size: 24px;
  line-height: 1;
  width: 28px;
  text-align: center;
  cursor: pointer;
}

.brand-wrap {
  display: flex;
  align-items: center;
  gap: 6px;
}

.book-icon {
  font-size: 15px;
}

.brand-text {
  font-size: 38px;
  line-height: 1;
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

.album-grid {
  margin-top: 24px;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
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
</style>
