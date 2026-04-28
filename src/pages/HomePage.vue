<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'

const user = ref(null)
const menuOpen = ref(false)
const router = useRouter()

try {
  user.value = JSON.parse(localStorage.getItem('fototeek_user') || 'null')
} catch (error) {
  user.value = null
}

const isLoggedIn = computed(() => Boolean(user.value))
const beginArchiveRoute = computed(() => (isLoggedIn.value ? '/heritage' : '/register'))

function logout() {
  localStorage.removeItem('fototeek_user')
  user.value = null
  menuOpen.value = false
  router.push('/')
}
</script>

<template>
  <main class="page">
    <header class="topbar">
      <div class="brand">
        <span class="brand-dot" />
        <span class="brand-text">Fototeek</span>
      </div>
      <nav v-if="!isLoggedIn" class="auth-links">
        <RouterLink to="/login">Login</RouterLink>
        <RouterLink class="register" to="/register">Register</RouterLink>
      </nav>
      <div v-else class="menu-wrap">
        <button class="menu-button" type="button" aria-label="Open menu" @click="menuOpen = !menuOpen">
          ⋮
        </button>
        <div v-if="menuOpen" class="menu-popover">
          <RouterLink to="/heritage" @click="menuOpen = false">My Heritage</RouterLink>
          <button type="button" @click="logout">Log out</button>
        </div>
      </div>
    </header>

    <section class="photo-card">
      <div class="photo-frame">
        <div class="photo-placeholder" />
        <p>Sunday Picnic, 1954</p>
      </div>
    </section>

    <section class="intro">
      <p class="eyebrow">Family archives</p>
      <h1>
        A digital home for
        <em>living memories.</em>
      </h1>
      <p class="copy">
        Preserve the tactile essence of your family's history in a permanent, beautiful
        digital archive.
      </p>
    </section>

    <section class="cta-box">
      <RouterLink :to="beginArchiveRoute" class="cta-button">Begin your archive</RouterLink>
      <p>Complimentary for your first 500 precious memories.</p>
    </section>

    <section class="feature-list">
      <article class="feature-card">
        <h2>Digitize</h2>
        <p>Transform physical prints into lasting digital heirlooms.</p>
      </article>
      <article class="feature-card">
        <h2>Document</h2>
        <p>Annotate your photos with the stories only you know.</p>
      </article>
      <article class="feature-card">
        <h2>Heritage</h2>
        <p>Share your curated legacy with the next generation.</p>
      </article>
    </section>

    <footer class="footer">
      <nav>
        <a href="#">About</a>
        <a href="#">Privacy</a>
        <a href="#">Ethics</a>
      </nav>
      <p class="copyright">© 2024 Fototeek Physical Archives</p>
      <p class="note">Preserved for tomorrow's generations.</p>
    </footer>
  </main>
</template>

<style scoped>
.page {
  max-width: 390px;
  margin: 0 auto;
  padding: 20px 14px 28px;
  color: #1c1714;
  font-family: Georgia, 'Times New Roman', serif;
}

.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.brand {
  display: flex;
  align-items: center;
  gap: 7px;
}

.brand-dot {
  width: 10px;
  height: 10px;
  border-radius: 999px;
  background: #1c1714;
}

.brand-text {
  font-size: 30px;
  line-height: 1;
}

.auth-links {
  display: flex;
  align-items: center;
  gap: 10px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-family: Arial, sans-serif;
  font-size: 10px;
}

.auth-links a {
  color: #1c1714;
  text-decoration: none;
}

.auth-links .register {
  background: #1e130c;
  color: #f8f5ee;
  border-radius: 999px;
  padding: 7px 12px;
}

.menu-button {
  border: 0;
  background: transparent;
  color: #1c1714;
  font-size: 24px;
  line-height: 1;
  cursor: pointer;
}

.menu-wrap {
  position: relative;
}

.menu-popover {
  position: absolute;
  right: 0;
  top: 28px;
  min-width: 130px;
  background: #fff;
  border: 1px solid #ddd4c6;
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
  color: #1c1714;
  text-decoration: none;
  font-family: Arial, sans-serif;
  font-size: 12px;
  cursor: pointer;
}

.menu-popover a:hover,
.menu-popover button:hover {
  background: #f5f2eb;
}

.photo-card {
  margin-top: 18px;
  display: flex;
  justify-content: center;
}

.photo-frame {
  width: 203px;
  background: #f3f0e8;
  box-shadow: 0 6px 16px rgba(20, 12, 8, 0.14);
  border-radius: 2px;
  padding: 10px 10px 16px;
}

.photo-placeholder {
  height: 170px;
  background: linear-gradient(180deg, #d8cfbe 0%, #d6c9b0 45%, #8d857b 45%, #b9a88f 100%);
  border: 1px solid #d6d0c3;
}

.photo-frame p {
  text-align: center;
  margin-top: 14px;
  font-style: italic;
  color: #5e5349;
}

.intro {
  margin-top: 26px;
  text-align: center;
}

.eyebrow {
  margin-bottom: 12px;
  text-transform: uppercase;
  letter-spacing: 0.22em;
  font-size: 10px;
  font-family: Arial, sans-serif;
}

h1 {
  font-size: 52px;
  line-height: 0.96;
  font-weight: 500;
}

h1 em {
  display: block;
  font-style: italic;
  font-size: 0.92em;
}

.copy {
  margin: 14px auto 0;
  max-width: 290px;
  font-style: italic;
  color: #3e322a;
  line-height: 1.35;
}

.cta-box {
  margin-top: 24px;
  background: #f5f2eb;
  border-radius: 18px;
  padding: 22px 16px 20px;
  text-align: center;
}

.cta-button {
  display: inline-block;
  background: #1e130c;
  color: #f8f5ee;
  border-radius: 999px;
  text-decoration: none;
  text-transform: uppercase;
  letter-spacing: 0.09em;
  font-size: 12px;
  font-family: Arial, sans-serif;
  font-weight: 700;
  padding: 15px 26px;
}

.cta-box p {
  margin-top: 16px;
  font-style: italic;
  font-size: 13px;
  color: #8b7d6f;
}

.feature-list {
  margin-top: 18px;
  display: grid;
  gap: 11px;
}

.feature-card {
  background: #f5f2eb;
  border-radius: 18px;
  padding: 15px 18px;
}

.feature-card h2 {
  text-transform: uppercase;
  letter-spacing: 0.11em;
  font-size: 11px;
  margin: 0;
  font-family: Arial, sans-serif;
}

.feature-card p {
  margin-top: 6px;
  font-style: italic;
  color: #4f443c;
  line-height: 1.3;
}

.footer {
  margin-top: 44px;
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

.copyright {
  margin-top: 24px;
  text-transform: uppercase;
  letter-spacing: 0.11em;
  font-size: 9px;
  font-family: Arial, sans-serif;
  color: #7f7266;
}

.note {
  margin-top: 8px;
  font-style: italic;
  font-size: 12px;
  color: #938578;
}
</style>
