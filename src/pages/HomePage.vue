<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'

const user = ref(null)
const menuOpen = ref(false)
const router = useRouter()

try {
  user.value = JSON.parse(localStorage.getItem('fototeek_user') || 'null')
} catch (error) {
  user.value = null
}

const isLoggedIn = computed(() => Boolean(user.value))
const beginArchiveRoute = computed(() => (isLoggedIn.value ? '/parand' : '/registreeru'))

function logout() {
  localStorage.removeItem('fototeek_user')
  user.value = null
  menuOpen.value = false
  router.push('/')
}
</script>

<template>
  <main class="page">
    <div class="header-wrap">
      <AppHeader
        :show-auth-links="!isLoggedIn"
        :show-menu="isLoggedIn"
        @menu-click="menuOpen = !menuOpen"
      />
      <div v-if="isLoggedIn && menuOpen" class="menu-popover">
        <RouterLink to="/parand" @click="menuOpen = false">Minu pärand</RouterLink>
        <button type="button" @click="logout">Logi välja</button>
      </div>
    </div>

    <div class="hero-left">
      <section class="photo-card">
        <div class="photo-frame">
          <div class="photo-placeholder" />
          <p>Pühapäevane piknik, 1954</p>
        </div>
      </section>

      <section class="intro">
        <p class="eyebrow">Perearhiiv</p>
        <h1>
          Digitaalne kodu sinu <em>elavatele mälestustele.</em>
        </h1>
        <p class="copy">
          Hoia pere tähtsad hetked alles ka tulevaste põlvkondade jaoks.
        </p>
      </section>
    </div>

    <div class="hero-right">
      <section class="cta-box">
        <RouterLink :to="beginArchiveRoute" class="cta-button">Alusta oma arhiivi</RouterLink>
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
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px 16px 34px;
  color: var(--ink, #231f20);
  font-family: var(--font-serif, 'EB Garamond', Georgia, serif);
}

.hero-left {
  display: contents;
}

.hero-right {
  display: contents;
}

@media (min-width: 640px) {
  .hero-left {
    display: flex;
    flex-direction: column;
    gap: 28px;
    grid-area: hero-left;
    align-self: start;
  }

  .hero-right {
    display: flex;
    flex-direction: column;
    gap: 14px;
    grid-area: hero-right;
    align-self: start;
    min-width: 0;
  }
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
  color: var(--ink, #231f20);
  text-decoration: none;
  font-family: var(--font-sans, 'Inter', sans-serif);
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
  width: min(100%, 220px);
  background: #fff;
  box-shadow: 0 4px 24px rgba(35, 31, 32, 0.1);
  border-radius: 3px;
  padding: 12px 12px 18px;
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
  font-size: 14px;
  color: #4a423c;
}

.intro {
  margin-top: 26px;
  text-align: center;
}

@media (min-width: 640px) {
  .hero-left .photo-card {
    margin-top: 0;
  }

  .hero-left .intro {
    margin-top: 0;
  }
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
  font-size: clamp(36px, 9vw, 48px);
  line-height: 1.08;
  font-weight: 600;
  max-width: 11em;
}

h1 em {
  display: inline;
  font-style: italic;
  font-weight: 500;
}

.copy {
  margin: 16px auto 0;
  max-width: 22em;
  font-style: italic;
  color: #3a3330;
  line-height: 1.45;
  font-size: clamp(17px, 4.2vw, 20px);
}

.cta-box {
  margin-top: 24px;
  background: #fff;
  border-radius: 15px;
  padding: 22px 20px 20px;
  text-align: center;
  box-shadow: 0 2px 18px rgba(35, 31, 32, 0.07);
}

@media (min-width: 640px) {
  .hero-right .cta-box {
    margin-top: 0;
  }

  .hero-right .feature-list {
    margin-top: 0;
  }
}

.cta-button {
  display: block;
  background: #231f20;
  color: #faf8f5;
  border-radius: 999px;
  text-decoration: none;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  font-size: 11px;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-weight: 700;
  min-height: 48px;
  padding: 15px 28px;
  text-align: center;
}

.cta-box p {
  margin-top: 14px;
  font-style: italic;
  font-size: 14px;
  font-family: var(--font-serif, 'EB Garamond', Georgia, serif);
  color: #5c534d;
}

.feature-list {
  margin-top: 18px;
  display: grid;
  gap: 14px;
}

.feature-card {
  background: #fff;
  border-radius: 15px;
  padding: 20px 22px;
  box-shadow: 0 2px 18px rgba(35, 31, 32, 0.07);
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
  margin-top: 10px;
  font-style: italic;
  color: #3a3330;
  line-height: 1.5;
  font-size: 15px;
  font-family: var(--font-serif, 'EB Garamond', Georgia, serif);
}

.footer {
  margin-top: 44px;
  text-align: center;
  grid-area: footer;
  border-top: 1px solid #ded7cb;
  padding-top: 24px;
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

/* Two-column layout from small tablet up so typical laptop windows are not stuck in single column. */
@media (min-width: 640px) {
  .page {
    padding: 32px 32px 48px;
    display: grid;
    grid-template-columns: minmax(0, 0.45fr) minmax(0, 0.55fr);
    grid-template-areas:
      'header header'
      'hero-left hero-right'
      'footer footer';
    gap: 28px 40px;
    align-items: start;
  }

  .footer {
    margin-top: 56px;
  }

  .photo-card {
    justify-content: flex-start;
  }

  .photo-frame {
    width: min(100%, 250px);
  }

  .intro {
    text-align: left;
  }

  .copy {
    margin-left: 0;
    margin-right: 0;
    max-width: 22em;
    font-size: clamp(17px, 1.9vw, 21px);
  }

  h1 {
    font-size: clamp(44px, 5vw, 56px);
    max-width: 12em;
  }

  .cta-box {
    padding: 24px;
    text-align: left;
  }

  .cta-box p {
    margin-top: 12px;
  }

  .feature-card p {
    font-size: 16px;
  }
}

@media (min-width: 1200px) {
  .page {
    max-width: 1180px;
    grid-template-columns: minmax(340px, 0.42fr) minmax(0, 0.58fr);
    grid-template-areas:
      'header header'
      'hero-left hero-right'
      'footer footer';
    gap: 36px 56px;
    padding: 36px 40px 64px;
  }

  .hero-left {
    gap: 36px;
  }

  .hero-right {
    gap: 16px;
  }

  .photo-frame {
    width: min(100%, 280px);
    padding: 14px 14px 20px;
  }

  .photo-placeholder {
    height: 240px;
  }

  .intro {
    text-align: left;
  }

  h1 {
    max-width: 9em;
    font-size: clamp(52px, 4.8vw, 68px);
    line-height: 1.06;
    font-weight: 600;
  }

  .copy {
    max-width: 18em;
    font-size: clamp(19px, 1.65vw, 24px);
  }

  .cta-box {
    text-align: left;
    padding: 26px 28px;
  }

  .cta-box p {
    margin-top: 14px;
    max-width: none;
    font-size: 15px;
  }

  .feature-list {
    gap: 16px;
  }

  .feature-card {
    min-height: auto;
    padding: 22px 26px;
  }

  .feature-card p {
    margin-top: 10px;
    font-size: clamp(17px, 1.25vw, 20px);
    line-height: 1.5;
  }

  .footer {
    margin-top: 64px;
  }
}
</style>
