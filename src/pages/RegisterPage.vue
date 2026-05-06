<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'
import registerPhotoSrc from '@/assets/register-photo.png'
import { apiFetch, getToken, logoutSession, parseApiError, setToken } from '@/api/fototeekApi.js'

const router = useRouter()
const user = ref(null)
const menuOpen = ref(false)

try {
  user.value = JSON.parse(localStorage.getItem('fototeek_user') || 'null')
} catch (error) {
  user.value = null
}

const isLoggedIn = computed(() => Boolean(user.value && getToken()))

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})
const showPassword = ref(false)
const isSubmitting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

async function onSubmit() {
  isSubmitting.value = true
  errorMessage.value = ''
  successMessage.value = ''
  try {
    const response = await apiFetch('/register', {
      method: 'POST',
      body: {
        name: form.value.name,
        email: form.value.email,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation,
      },
    })

    if (!response.ok) {
      errorMessage.value = await parseApiError(response, 'Registreerimine ebaõnnestus. Proovi uuesti.')
      return
    }

    const payload = await response.json()
    setToken(payload?.token || '')
    localStorage.setItem('fototeek_user', JSON.stringify(payload?.user || null))
    successMessage.value = 'Registreerimine õnnestus.'
    router.push('/')
  } catch (error) {
    errorMessage.value = 'Serveriga ei saanud ühendust. Kontrolli, et backend töötab.'
  } finally {
    isSubmitting.value = false
  }
}

async function logout() {
  await logoutSession()
  user.value = null
  menuOpen.value = false
  router.push('/')
}
</script>

<template>
  <main class="page">
    <div class="header-wrap">
      <AppHeader :show-auth-links="!isLoggedIn" :show-menu="isLoggedIn" @menu-click="menuOpen = !menuOpen" />
      <div v-if="isLoggedIn && menuOpen" class="menu-popover">
        <RouterLink to="/albumid" @click="menuOpen = false">Minu albumid</RouterLink>
        <button type="button" @click="logout">Logi välja</button>
      </div>
    </div>

    <section class="hero-left">
      <section class="title">
        <p>Perearhiiv</p>
        <h1>Alusta oma<br />arhiivi.</h1>
        <h2>Iga mälestus väärib oma kohta.</h2>
      </section>
      <section class="photo-card" aria-hidden="true">
        <div class="photo-frame">
          <img class="photo-placeholder" :src="registerPhotoSrc" alt="Sõmera klubi, 1975" />
          <p>Sõmera klubi, 1975</p>
        </div>
      </section>
    </section>

    <section class="hero-right">
      <form class="register-form" @submit.prevent="onSubmit">
        <input v-model="form.name" type="text" placeholder="Täisnimi" />
        <input v-model="form.email" type="email" placeholder="E-posti aadress" />

        <div class="password-wrap">
          <input v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="Parool" />
          <button type="button" @click="showPassword = !showPassword">
            {{ showPassword ? 'PEIDA' : 'NÄITA' }}
          </button>
        </div>

        <input v-model="form.password_confirmation" type="password" placeholder="Kinnita parool" />

        <button type="submit" class="submit-btn" :disabled="isSubmitting">
          {{ isSubmitting ? 'Registreerin...' : 'Registreeru' }}
        </button>
      </form>

      <p v-if="errorMessage" class="feedback error">{{ errorMessage }}</p>
      <p v-if="successMessage" class="feedback success">{{ successMessage }}</p>

      <p class="signin-line">
        Sul on juba arhiiv?
        <RouterLink to="/logi-sisse">Logi sisse</RouterLink>
      </p>
    </section>

    <footer class="footer">
      <nav>
        <a href="#">Meist</a>
        <a href="#">Privaatsus</a>
        <a href="#">Eetika</a>
      </nav>
      <p class="copyright">© 2025 Fototeek</p>
      <p class="note">Hoiame meie esivanemate lugusid.</p>
    </footer>
  </main>
</template>

<style scoped>
.page {
  max-width: 1240px;
  margin: 0 auto;
  padding: 20px 20px 30px;
  color: #1c1714;
  font-family: var(--font-serif, 'EB Garamond', Georgia, serif);
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

.hero-left {
  margin-top: 24px;
}

.hero-right {
  margin-top: 22px;
}

.title {
  text-align: center;
}

.title p {
  text-transform: uppercase;
  letter-spacing: 0.18em;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 10px;
  font-weight: 600;
}

.title h1 {
  margin-top: 16px;
  font-size: clamp(52px, 11vw, 66px);
  font-weight: 600;
  line-height: 0.95;
}

.title h2 {
  margin-top: 18px;
  font-style: italic;
  font-size: clamp(24px, 5.5vw, 38px);
  font-weight: 500;
  line-height: 1.15;
  color: #3e322a;
}

.photo-card {
  margin-top: 26px;
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

.register-form {
  display: grid;
  gap: 10px;
  background: var(--surface, #f8f7f4);
  border: 1px solid var(--line-soft, #e4ddd1);
  border-radius: 20px;
  padding: 16px;
}

.register-form input {
  width: 100%;
  border: 1px solid var(--line-soft, #e4ddd1);
  border-radius: 999px;
  padding: 14px 18px;
  background: var(--surface-strong, #fff);
  font-size: 13px;
  font-family: var(--font-sans, 'Inter', sans-serif);
  color: #3e342e;
}

.register-form input::placeholder {
  color: #adadad;
}

.password-wrap {
  position: relative;
}

.password-wrap button {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  border: none;
  background: transparent;
  letter-spacing: 0.08em;
  font-size: 10px;
  font-weight: 700;
  color: #5c5047;
  cursor: pointer;
}

.submit-btn {
  margin-top: 8px;
  border: none;
  border-radius: 999px;
  background: #1e130c;
  color: #fff;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-weight: 700;
  padding: 14px;
  min-height: 42px;
  cursor: pointer;
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.feedback {
  margin-top: 10px;
  text-align: center;
  font-family: Arial, sans-serif;
  font-size: 12px;
}

.feedback.error {
  color: #a32020;
}

.feedback.success {
  color: #1f6b34;
}

.signin-line {
  margin-top: 24px;
  text-align: center;
  font-style: italic;
  color: #5d4f45;
  font-size: 15px;
}

.signin-line a {
  color: #1c1714;
  font-style: normal;
  font-weight: 700;
  text-decoration: underline;
}

.footer {
  margin-top: 62px;
  border-top: 1px solid var(--line-soft, #dad6cd);
  padding-top: 28px;
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
  margin-top: 20px;
  text-transform: uppercase;
  letter-spacing: 0.11em;
  font-size: 9px;
  font-family: var(--font-sans, 'Inter', sans-serif);
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
    padding: 26px 32px 44px;
  }

  .hero-left {
    margin-top: 28px;
  }

  .hero-right {
    margin-top: 26px;
  }

  .photo-frame {
    width: min(100%, 250px);
  }

  .register-form {
    max-width: 560px;
    margin-left: auto;
    margin-right: auto;
    padding: 18px;
  }
}

@media (min-width: 1024px) {
  .page {
    display: grid;
    grid-template-columns: minmax(300px, 0.5fr) minmax(300px, 0.5fr);
    grid-template-areas:
      'header header'
      'left right'
      'footer footer';
    gap: 28px 56px;
    padding: 26px 42px 52px;
    position: relative;
  }

  .page::after {
    content: '';
    position: absolute;
    top: 122px;
    bottom: 178px;
    left: 50%;
    width: 1px;
    transform: translateX(-50%);
    background: var(--line-soft, #d9d2c7);
    pointer-events: none;
  }

  .hero-left {
    grid-area: left;
    margin-top: 2px;
    padding-right: 14px;
  }

  .photo-card {
    justify-content: flex-start;
  }

  .title {
    text-align: left;
  }

  .title h1 {
    max-width: 4.7em;
    font-size: clamp(62px, 6.2vw, 70px);
  }

  .title h2 {
    max-width: 8.3em;
    font-size: clamp(24px, 2.15vw, 42px);
    line-height: 1.14;
  }

  .hero-right {
    grid-area: right;
    margin-top: 90px;
    padding-left: 18px;
  }

  .photo-card {
    margin-top: 44px;
  }

  .photo-frame {
    width: min(100%, 280px);
    padding: 14px 14px 20px;
  }

  .photo-placeholder {
    height: 240px;
  }

  .register-form {
    max-width: 420px;
    margin: 0;
    gap: 12px;
    padding: 20px;
  }

  .register-form input {
    min-height: 42px;
  }

  .signin-line {
    max-width: 420px;
  }

  .footer {
    grid-area: footer;
    margin-top: 36px;
  }
}
</style>
