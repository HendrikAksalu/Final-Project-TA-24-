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

    <section class="photo-card">
      <div class="photo-frame">
        <div class="photo-placeholder" />
        <p>Pühapäevane piknik, 1954</p>
      </div>
    </section>

    <section class="intro">
      <p class="eyebrow">Perearhiiv</p>
      <h1>
        Digitaalne kodu sinu
        <em>elavatele mälestustele.</em>
      </h1>
      <p class="copy">
        Hoia pere tähtsad hetked alles ka tulevaste põlvkondade jaoks.
      </p>
    </section>

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
  max-width: 1280px;
  margin: 0 auto;
  padding: 20px 16px 34px;
  color: #1c1714;
  font-family: Georgia, 'Times New Roman', serif;
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
  grid-area: photo;
}

.photo-frame {
  width: min(100%, 220px);
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
  grid-area: intro;
}

.eyebrow {
  margin-bottom: 12px;
  text-transform: uppercase;
  letter-spacing: 0.22em;
  font-size: 10px;
  font-family: Arial, sans-serif;
}

h1 {
  font-size: clamp(42px, 11vw, 54px);
  line-height: 0.95;
  font-weight: 500;
}

h1 em {
  display: block;
  font-style: italic;
  font-size: 0.92em;
}

.copy {
  margin: 14px auto 0;
  max-width: 360px;
  font-style: italic;
  color: #3e322a;
  line-height: 1.35;
  font-size: clamp(22px, 5.2vw, 28px);
}

.cta-box {
  margin-top: 24px;
  background: #f8f6f1;
  border-radius: 18px;
  padding: 20px 16px 18px;
  text-align: center;
  grid-area: cta;
}

.cta-button {
  display: block;
  background: #1e130c;
  color: #f8f5ee;
  border-radius: 999px;
  text-decoration: none;
  text-transform: uppercase;
  letter-spacing: 0.09em;
  font-size: 12px;
  font-family: Arial, sans-serif;
  font-weight: 700;
  min-height: 48px;
  padding: 15px 26px;
}

.cta-box p {
  margin-top: 16px;
  font-style: italic;
  font-size: 13px;
  color: #6f6257;
}

.feature-list {
  margin-top: 18px;
  display: grid;
  gap: 12px;
  grid-area: features;
}

.feature-card {
  background: #f8f6f1;
  border-radius: 18px;
  padding: 18px 20px;
}

.feature-card h2 {
  text-transform: uppercase;
  letter-spacing: 0.11em;
  font-size: 11px;
  margin: 0;
  font-family: Arial, sans-serif;
}

.feature-card p {
  margin-top: 8px;
  font-style: italic;
  color: #4f443c;
  line-height: 1.35;
  font-size: clamp(30px, 7.2vw, 38px);
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
  color: #6f6358;
}

.note {
  margin-top: 8px;
  font-style: italic;
  font-size: 12px;
  color: #74685d;
}

@media (min-width: 768px) {
  .page {
    padding: 32px 32px 48px;
    display: grid;
    grid-template-columns: minmax(0, 0.45fr) minmax(0, 0.55fr);
    grid-template-areas:
      'header header'
      'photo cta'
      'intro features'
      'footer footer';
    gap: 24px;
    align-items: start;
  }

  .photo-card,
  .intro,
  .cta-box,
  .feature-list,
  .footer {
    margin-top: 0;
  }

  .photo-frame {
    width: min(100%, 250px);
  }

  h1 {
    font-size: clamp(64px, 8vw, 72px);
  }

  .copy {
    max-width: 520px;
    font-size: clamp(32px, 4vw, 36px);
  }

  .cta-box {
    padding: 24px;
  }

  .footer {
    margin-top: 56px;
  }
}

@media (min-width: 1200px) {
  .page {
    max-width: 1320px;
    grid-template-columns: minmax(360px, 0.43fr) minmax(0, 0.57fr);
    grid-template-areas:
      'header header'
      'photo cta'
      'intro features'
      'footer footer';
    gap: 30px 64px;
    padding: 40px 48px 64px;
  }

  .photo-card {
    justify-content: flex-start;
  }

  .photo-frame {
    width: min(100%, 320px);
    padding: 12px 12px 18px;
  }

  .photo-placeholder {
    height: 260px;
  }

  .intro {
    text-align: left;
    margin-top: 0;
  }

  h1 {
    max-width: 620px;
    font-size: clamp(72px, 6vw, 86px);
    line-height: 0.92;
  }

  .copy {
    margin-left: 0;
    margin-right: 0;
    max-width: 520px;
    font-size: clamp(32px, 2.2vw, 38px);
  }

  .cta-box {
    text-align: left;
    padding: 24px 28px;
  }

  .cta-box p {
    margin-top: 16px;
    max-width: none;
  }

  .feature-list {
    margin-top: 0;
    gap: 14px;
    padding-left: 28px;
    border-left: 1px solid #ded7cb;
  }

  .cta-box {
    margin-top: 0;
    margin-left: 28px;
  }

  .feature-card {
    min-height: 134px;
    padding: 22px 24px;
  }

  .feature-card p {
    margin-top: 10px;
    font-size: clamp(42px, 3vw, 50px);
    line-height: 1.15;
  }

  .footer {
    margin-top: 64px;
  }
}
</style>
