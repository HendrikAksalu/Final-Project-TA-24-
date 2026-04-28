<script setup>
import { ref } from 'vue'

const memories = ref([
  { id: 1, title: 'August, 1952', photoClass: 'one', favorite: false, rotate: '' },
  { id: 2, title: 'The old Mercury', photoClass: 'two', favorite: true, rotate: 'rotate-right' },
  { id: 3, title: 'Spring blossoms', photoClass: 'three', favorite: false, rotate: 'rotate-left' },
  { id: 4, title: 'Lake sunsets', photoClass: 'four', favorite: false, rotate: '' },
])

function toggleFavorite(id) {
  const memory = memories.value.find((item) => item.id === id)
  if (memory) memory.favorite = !memory.favorite
}
</script>

<template>
  <main class="page">
    <header class="topbar">
      <RouterLink to="/heritage" class="icon-link" aria-label="Go back">←</RouterLink>
      <div class="brand-wrap">
        <span class="book-icon">◧</span>
        <span class="brand-text">Fototeek</span>
      </div>
      <button class="icon-link" type="button" aria-label="More actions">⋮</button>
    </header>

    <section class="title">
      <p>Family archives</p>
      <h1>Miller Family Estate</h1>
      <p class="subtitle">
        Preserving the tactile essence of your family's history in a permanent, beautiful
        digital archive.
      </p>
    </section>

    <section class="album-grid">
      <article v-for="memory in memories" :key="memory.id" class="polaroid" :class="memory.rotate">
        <RouterLink
          :to="{ path: '/memory', query: { title: memory.title } }"
          class="memory-link"
        >
          <div class="photo" :class="memory.photoClass" />
          <h2>{{ memory.title }}</h2>
        </RouterLink>
        <button type="button" class="fav-btn" @click="toggleFavorite(memory.id)">
          {{ memory.favorite ? '★' : '☆' }}
        </button>
      </article>
    </section>

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
