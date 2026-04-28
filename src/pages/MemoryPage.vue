<script setup>
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const liked = ref(false)
const editing = ref(false)
const story = ref(
  "This was the first summer after Uncle Arthur returned. Grandma Rose spent all morning preparing the basket. You can still see a faint smudge of jam on the original print. We talked for hours under the apple trees...",
)
const who = ref('Grandma Rose, Uncle Arthur')
const when = ref('September, 1954')
const where = ref('Sussex, Old Orchard Lane')

const memoryTitle = computed(() => route.query.title || 'Harvest in the orchard, 1952')

function shareMemory() {
  const url = window.location.href
  if (navigator.share) {
    navigator.share({ title: String(memoryTitle.value), url })
    return
  }
  navigator.clipboard.writeText(url)
}

function downloadPlaceholder() {
  alert('Download will be connected next.')
}
</script>

<template>
  <main class="page">
    <header class="topbar">
      <RouterLink to="/album" class="icon-link" aria-label="Go back">←</RouterLink>
      <div class="brand-wrap">
        <span class="book-icon">◧</span>
        <span class="brand-text">Fototeek</span>
      </div>
      <button class="icon-link" type="button" aria-label="More actions">⋮</button>
    </header>

    <section class="hero-card">
      <div class="photo" />
      <p>{{ memoryTitle }}</p>
    </section>

    <section class="title">
      <p>Your heritage</p>
      <h1>Curating the timeless threads of your family's tapestry.</h1>
      <p v-if="!editing" class="story">{{ story }}</p>
      <textarea v-else v-model="story" class="story-editor" rows="5" />
    </section>

    <section class="info-list">
      <article class="info-card">
        <span class="icon">👥</span>
        <div>
          <p class="label">Who</p>
          <p v-if="!editing">{{ who }}</p>
          <input v-else v-model="who" type="text" class="field-input" />
        </div>
      </article>
      <article class="info-card">
        <span class="icon">📅</span>
        <div>
          <p class="label">When</p>
          <p v-if="!editing">{{ when }}</p>
          <input v-else v-model="when" type="text" class="field-input" />
        </div>
      </article>
      <article class="info-card">
        <span class="icon">📍</span>
        <div>
          <p class="label">Where</p>
          <p v-if="!editing">{{ where }}</p>
          <input v-else v-model="where" type="text" class="field-input" />
        </div>
      </article>
    </section>

    <section class="actions">
      <button type="button" class="action-btn" @click="liked = !liked">{{ liked ? '♥' : '♡' }}</button>
      <button type="button" class="action-btn" @click="shareMemory">↗</button>
      <button type="button" class="action-btn edit" @click="editing = !editing">
        {{ editing ? 'Save' : 'Edit' }}
      </button>
      <button type="button" class="action-btn" @click="downloadPlaceholder">⇩</button>
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

.hero-card {
  margin-top: 24px;
  background: #f3f0e8;
  box-shadow: 0 6px 14px rgba(19, 11, 8, 0.14);
  padding: 11px 11px 16px;
}

.photo {
  height: 350px;
  border: 1px solid #d9d3c6;
  background: linear-gradient(180deg, #cc9a7d 0%, #d8a587 45%, #e2b08f 100%);
}

.hero-card p {
  margin-top: 12px;
  text-align: center;
  font-style: italic;
  color: #5f5349;
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
  font-size: 52px;
  line-height: 0.96;
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
  padding: 13px 14px;
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

.actions {
  margin-top: 18px;
  display: flex;
  justify-content: center;
  gap: 8px;
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
