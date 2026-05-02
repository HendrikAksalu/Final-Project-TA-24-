<script setup>
import { computed, nextTick, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'

const route = useRoute()
const memoriesKey = computed(() => `fototeek_memories_${route.query.albumId || 'none'}`)
const liked = ref(false)
const editing = ref(false)
const story = ref('')
const storyInput = ref(null)
const whoInput = ref(null)
const whenInput = ref(null)
const whereInput = ref(null)
const who = ref('')
const when = ref('')
const where = ref('')
const title = ref('')

function loadMemories() {
  try {
    const parsed = JSON.parse(localStorage.getItem(memoriesKey.value) || '[]')
    return Array.isArray(parsed) ? parsed : []
  } catch (error) {
    return []
  }
}

const memories = ref(loadMemories())

const currentMemory = computed(() =>
  memories.value.find((item) => String(item.id) === String(route.query.memoryId)),
)

const memoryTitle = computed(() => title.value || route.query.title || 'Mälestus')

function shareMemory() {
  const url = window.location.href
  if (navigator.share) {
    navigator.share({ title: String(memoryTitle.value), url })
    return
  }
  navigator.clipboard.writeText(url)
}

function downloadPlaceholder() {
  alert('Allalaadimine lisatakse peagi.')
}

function saveCurrentMemory() {
  if (!currentMemory.value) return

  const index = memories.value.findIndex((item) => String(item.id) === String(currentMemory.value.id))
  if (index === -1) return

  memories.value[index] = {
    ...memories.value[index],
    story: story.value,
    who: who.value,
    when: when.value,
    where: where.value,
  }
  localStorage.setItem(memoriesKey.value, JSON.stringify(memories.value))
}

function toggleEditing() {
  if (editing.value) saveCurrentMemory()
  editing.value = !editing.value
}

function startEditing(field) {
  editing.value = true
  nextTick(() => {
    const fieldMap = {
      story: storyInput.value,
      who: whoInput.value,
      when: whenInput.value,
      where: whereInput.value,
    }
    fieldMap[field]?.focus()
  })
}

watch(
  currentMemory,
  (memory) => {
    story.value = memory?.story || ''
    who.value = memory?.who || ''
    when.value = memory?.when || ''
    where.value = memory?.where || ''
    title.value = memory?.title || ''
  },
  { immediate: true },
)
</script>

<template>
  <main class="page">
    <AppHeader :back-to="{ path: '/albumid', query: { albumId: route.query.albumId } }" />

    <section class="hero-card">
      <div class="photo" />
      <p>{{ memoryTitle }}</p>
    </section>

    <section class="title">
      <p>Sinu pärand</p>
      <h1>Säilitame sinu pereloo ajatuid niite.</h1>
      <p v-if="!editing && story" class="story editable-value" @click="startEditing('story')">{{ story }}</p>
      <p v-else-if="!editing" class="story placeholder editable-value" @click="startEditing('story')">
        Lisa siia mälestuse lugu...
      </p>
      <textarea
        v-else
        ref="storyInput"
        v-model="story"
        class="story-editor"
        rows="5"
        placeholder="Lisa siia mälestuse lugu..."
      />
    </section>

    <section class="info-list">
      <article class="info-card">
        <span class="icon">👥</span>
        <div>
          <p class="label">Kes</p>
          <p v-if="!editing && who" class="editable-value" @click="startEditing('who')">{{ who }}</p>
          <p v-else-if="!editing" class="editable-value placeholder-text" @click="startEditing('who')">
            Lisa siia, kes on fotol...
          </p>
          <input v-else ref="whoInput" v-model="who" type="text" class="field-input" />
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

    <section class="actions">
      <button type="button" class="action-btn" @click="liked = !liked">{{ liked ? '♥' : '♡' }}</button>
      <button type="button" class="action-btn" @click="shareMemory">↗</button>
      <button type="button" class="action-btn edit" @click="toggleEditing">
        {{ editing ? 'Salvesta' : 'Muuda' }}
      </button>
      <button type="button" class="action-btn" @click="downloadPlaceholder">⇩</button>
    </section>

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
  line-height: 1.02;
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

@media (min-width: 768px) {
  .page {
    padding: 28px 28px 40px;
  }

  .hero-card {
    max-width: 620px;
    margin-left: auto;
    margin-right: auto;
  }

  .photo {
    height: 480px;
  }

  .title h1 {
    font-size: 72px;
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
    padding: 34px 40px 52px;
    display: grid;
    grid-template-columns: minmax(300px, 0.44fr) minmax(0, 0.56fr);
    grid-template-areas:
      'header header'
      'hero title'
      'hero info'
      'hero actions'
      'footer footer';
    gap: 22px 40px;
    align-items: start;
  }

  .page > :first-child {
    grid-area: header;
  }

  .hero-card {
    grid-area: hero;
    margin-top: 8px;
    max-width: none;
  }

  .photo {
    height: 560px;
  }

  .title {
    grid-area: title;
    margin-top: 8px;
    text-align: left;
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
    grid-area: actions;
    justify-content: flex-start;
    margin-top: 0;
  }

  .footer {
    grid-area: footer;
    margin-top: 28px;
  }
}
</style>
