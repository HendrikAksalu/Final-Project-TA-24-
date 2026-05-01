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
  max-width: 1320px;
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
  grid-area: cta;
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
  color: #6f6257;
}

.feature-list {
  margin-top: 18px;
  display: grid;
  gap: 11px;
  grid-area: features;
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
  grid-area: footer;
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

@media (min-width: 560px) {
  .page {
    padding: 32px 34px 46px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-areas:
      'header header'
      'photo intro'
      'photo cta'
      'features features'
      'footer footer';
    gap: 28px 36px;
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
    width: min(100%, 380px);
  }

  h1 {
    font-size: clamp(58px, 6.6vw, 78px);
  }

  .copy {
    max-width: 520px;
    font-size: 21px;
  }

  .cta-box {
    max-width: 520px;
  }

  .feature-list {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
  }

  .footer {
    margin-top: 64px;
  }
}

@media (min-width: 860px) {
  .page {
    grid-template-columns: minmax(360px, 0.44fr) minmax(0, 0.56fr);
    grid-template-areas:
      'header header'
      'photo intro'
      'photo cta'
      'features features'
      'footer footer';
    gap: 32px 58px;
    padding: 40px 52px 62px;
  }

  .photo-card {
    justify-content: flex-start;
  }

  .photo-frame {
    width: min(100%, 460px);
    padding: 12px 12px 18px;
  }

  .photo-placeholder {
    height: 370px;
  }

  .intro {
    text-align: left;
  }

  h1 {
    max-width: 760px;
    line-height: 0.93;
  }

  .copy {
    margin-left: 0;
    margin-right: 0;
    max-width: 650px;
    font-size: 23px;
  }

  .cta-box {
    text-align: left;
    max-width: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    padding: 24px 26px;
  }

  .cta-box p {
    margin-top: 0;
    max-width: 300px;
  }

  .feature-list {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-top: 8px;
  }

  .feature-card {
    min-height: 152px;
    padding: 20px 22px;
  }

  .feature-card p {
    margin-top: 10px;
    font-size: 18px;
    line-height: 1.45;
  }

  .footer {
    margin-top: 74px;
  }
}

@media (min-width: 1200px) {
  .page {
    max-width: 1440px;
    grid-template-columns: minmax(390px, 0.43fr) minmax(0, 0.57fr);
    gap: 34px 72px;
    padding: 44px 68px 70px;
  }

  .photo-frame {
    width: min(100%, 500px);
  }

  .photo-placeholder {
    height: 405px;
  }

  h1 {
    font-size: clamp(72px, 5.2vw, 88px);
  }

  .copy {
    max-width: 700px;
    font-size: 24px;
  }
}

/* Force a clearly desktop-first composition on real desktop widths. */
@media (min-width: 900px) {
  .page {
    max-width: 1500px;
    padding: 42px 64px 72px;
    display: grid;
    grid-template-columns: minmax(360px, 430px) minmax(0, 1fr);
    grid-template-areas:
      'header header'
      'photo intro'
      'photo cta'
      'photo features'
      'footer footer';
    gap: 32px 72px;
    align-items: start;
  }

  .photo-card,
  .intro,
  .cta-box,
  .feature-list,
  .footer {
    margin-top: 0;
  }

  .photo-card {
    justify-content: flex-start;
  }

  .photo-frame {
    width: min(100%, 430px);
  }

  .photo-placeholder {
    height: 380px;
  }

  .intro {
    text-align: left;
  }

  h1 {
    font-size: clamp(68px, 5.4vw, 92px);
    line-height: 0.92;
    max-width: 860px;
  }

  .copy {
    margin-left: 0;
    margin-right: 0;
    max-width: 700px;
    font-size: 24px;
  }

  .cta-box {
    text-align: left;
    max-width: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
    padding: 24px 28px;
  }

  .cta-box p {
    margin-top: 0;
    max-width: 320px;
  }

  .feature-list {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
  }

  .feature-card {
    min-height: 160px;
    padding: 22px 24px;
  }
}
</style>
