<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'
import homeHeroPhotoSrc from '@/assets/home-hero-photo.png'
import { getToken, logoutSession } from '@/api/fototeekApi.js'

const user = ref(null)
const menuOpen = ref(false)
const router = useRouter()

try {
  user.value = JSON.parse(localStorage.getItem('fototeek_user') || 'null')
} catch (error) {
  user.value = null
}

const isLoggedIn = computed(() => Boolean(user.value && getToken()))
const beginArchiveRoute = computed(() => (isLoggedIn.value ? '/albumid' : '/registreeru'))
const beginArchiveLabel = computed(() => (isLoggedIn.value ? 'Vaata oma albumeid' : 'Alusta oma arhiivi'))

async function logout() {
  await logoutSession()
  user.value = null
  menuOpen.value = false
  router.push('/')
}
</script>

<template>
  <main class="page home-page">
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

    <div class="hero-left">
      <section class="photo-card">
        <div class="photo-frame">
          <img class="photo-placeholder" :src="homeHeroPhotoSrc" alt='AS-i "Lääne Ehitus" töömehed, 2000' />
          <p>AS-i "Lääne Ehitus" töömehed, 2000</p>
        </div>
      </section>

      <section class="intro">
        <p class="eyebrow">Perearhiiv</p>
        <h1>Digitaalne kodu sinu elavatele mälestustele.</h1>
        <p class="copy">
          Hoia pere tähtsad hetked alles ka tulevaste põlvkondade jaoks.
        </p>
      </section>
    </div>

    <div class="hero-right">
      <section class="cta-box">
        <RouterLink :to="beginArchiveRoute" class="cta-button">{{ beginArchiveLabel }}</RouterLink>
        <p>Iga mälestus väärib oma kohta.</p>
      </section>

      <section class="feature-list">
        <article class="feature-card">
          <h2>Digitaliseeri</h2>
          <p>Muuda paberfotod kestvateks digitaalseteks päranditeks.</p>
        </article>
        <article class="feature-card">
          <h2>Dokumenteeri</h2>
          <p>Lisa fotodele lood, mida tead ainult sina.</p>
        </article>
        <article class="feature-card">
          <h2>Pärand</h2>
          <p>Jaga oma hoolikalt hoitud pärandit järgmise põlvkonnaga.</p>
        </article>
      </section>
    </div>

    <footer class="footer">
      <nav>
        <a href="#">Meist</a>
        <a href="#">Privaatsus</a>
        <a href="#">Eetika</a>
      </nav>
      <p class="copyright">© 2025 Fototeek</p>
      <p class="note">Hoitud homsete põlvkondade jaoks.</p>
    </footer>
  </main>
</template>

<style scoped>
.page {
  max-width: 1240px;
  margin: 0 auto;
  padding: 20px 20px 30px;
  color: var(--ink, #231f20);
  font-family: var(--font-serif, 'EB Garamond', Georgia, serif);
}

/* Real wrappers (avoid display: contents — more reliable grid + avoids Safari quirks). */
.hero-left {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.hero-right {
  display: flex;
  flex-direction: column;
  gap: 18px;
  min-width: 0;
}

.hero-right .feature-list {
  margin-top: 0;
}

.header-wrap {
  position: relative;
  grid-area: header;
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

.photo-card {
  margin-top: 10px;
  display: flex;
  justify-content: center;
  overflow: visible;
}

.photo-frame {
  width: min(100%, 220px);
  background: var(--surface-strong, #fff);
  box-shadow: 0 4px 24px rgba(35, 31, 32, 0.1);
  border-radius: 3px;
  padding: 12px 12px 18px;
  transform: rotate(-3.5deg);
  transform-origin: center center;
}

.photo-placeholder {
  height: 170px;
  width: 100%;
  object-fit: cover;
  border: 1px solid var(--line-soft, #d6d0c3);
  display: block;
}

.photo-frame p {
  text-align: center;
  margin-top: 14px;
  font-style: italic;
  font-size: 14px;
  color: #4a423c;
}

.intro {
  margin-top: 24px;
  text-align: center;
}

.eyebrow {
  margin-bottom: 14px;
  text-transform: uppercase;
  letter-spacing: 0.24em;
  font-size: 10px;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-weight: 500;
  color: var(--ink, #231f20);
}

h1 {
  font-size: clamp(36px, 9vw, 50px);
  line-height: 1.04;
  font-weight: 600;
  max-width: 8.8em;
}

.copy {
  margin: 20px auto 0;
  max-width: 19em;
  font-style: italic;
  color: #3a3330;
  line-height: 1.4;
  font-size: clamp(17px, 4.2vw, 19px);
}

.cta-box {
  margin-top: 20px;
  background: var(--surface, #f8f7f4);
  border-radius: 20px;
  padding: 24px 26px 20px;
  text-align: center;
  border: 1px solid var(--line-soft, #efebe5);
}

.cta-button {
  display: block;
  background: #231f20;
  color: #faf8f5;
  border-radius: 999px;
  text-decoration: none;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 11px;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-weight: 700;
  min-height: 44px;
  padding: 13px 28px;
  text-align: center;
}

.cta-box p {
  margin-top: 14px;
  font-style: italic;
  font-size: 16px;
  font-family: var(--font-serif, 'EB Garamond', Georgia, serif);
  color: #5c534d;
}

.feature-list {
  margin-top: 14px;
  display: grid;
  gap: 14px;
}

.feature-card {
  background: var(--surface, #f8f7f4);
  border-radius: 20px;
  padding: 20px 24px;
  border: 1px solid var(--line-soft, #efebe5);
}

.feature-card h2 {
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 10px;
  margin: 0;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-weight: 600;
  color: var(--ink, #231f20);
}

.feature-card p {
  margin-top: 8px;
  font-style: italic;
  color: #3a3330;
  line-height: 1.35;
  font-size: clamp(15px, 4.4vw, 22px);
  font-family: var(--font-serif, 'EB Garamond', Georgia, serif);
}

.footer {
  margin-top: 72px;
  text-align: center;
  grid-area: footer;
  border-top: 1px solid #ded7cb;
  padding-top: 30px;
}

.footer nav {
  display: flex;
  justify-content: center;
  gap: 24px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 10px;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-weight: 500;
}

.footer a {
  color: var(--ink, #231f20);
  text-decoration: none;
}

.copyright {
  margin-top: 24px;
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
</style>
