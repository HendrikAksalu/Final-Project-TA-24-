<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'
import loginPhotoSrc from '@/assets/login-photo.png'
import { apiFetch, getToken, logoutSession, parseApiError, setToken } from '@/api/fototeekApi.js'

const router = useRouter()
const user = ref(null)
try {
  user.value = JSON.parse(localStorage.getItem('fototeek_user') || 'null')
} catch (error) {
  user.value = null
}

const isLoggedIn = computed(() => Boolean(user.value && getToken()))

const form = ref({
  email: '',
  password: '',
})
const showPassword = ref(false)
const isSubmitting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

async function onSubmit() {
  isSubmitting.value = true
  errorMessage.value = ''
  successMessage.value = ''
  const email = form.value.email.trim().toLowerCase()
  const password = form.value.password
  try {
    const response = await apiFetch('/login', {
      method: 'POST',
      body: {
        email,
        password,
      },
    })

    if (!response.ok) {
      let parsedError = await parseApiError(response, 'Sisselogimine ebaõnnestus. Proovi uuesti.')
      if (response.status === 401 && /invalid credentials/i.test(String(parsedError))) {
        parsedError = 'Vale e-post või parool. Kui konto on olemas, kontrolli kasutasid sama parooli mis registreerimisel.'
      }
      errorMessage.value = parsedError
      return
    }

    const payload = await response.json()
    setToken(payload?.token || '')
    localStorage.setItem('fototeek_user', JSON.stringify(payload?.user || null))
    successMessage.value = 'Sisselogimine õnnestus.'
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
  router.push('/')
}
</script>

<template>
  <main class="page">
    <div class="page-header-slot">
      <AppHeader :show-auth-links="!isLoggedIn" :show-menu="isLoggedIn" @logout="logout" />
    </div>

    <section class="hero-left">
      <section class="title">
        <p>Perearhiiv</p>
        <h1>Tore sind taas<br />näha.</h1>
        <h2>Sinu mälestused ootavad.</h2>
      </section>
      <section class="photo-card" aria-hidden="true">
        <div class="photo-frame">
          <img class="photo-placeholder" :src="loginPhotoSrc" alt="Koer, kass ja koer, 1932" loading="lazy" />
          <p>Koer, kass ja koer, 1932</p>
        </div>
      </section>
    </section>

    <section class="hero-right">
      <form class="login-form" @submit.prevent="onSubmit">
        <input v-model="form.email" type="email" placeholder="E-posti aadress" />

        <div class="password-wrap">
          <input v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="Parool" />
          <button type="button" @click="showPassword = !showPassword">
            {{ showPassword ? 'PEIDA' : 'NÄITA' }}
          </button>
        </div>

        <a href="#" class="forgot-link">Unustasid parooli?</a>
        <button type="submit" class="submit-btn" :disabled="isSubmitting">
          {{ isSubmitting ? 'Sisenen...' : 'Logi sisse' }}
        </button>
      </form>

      <p v-if="errorMessage" class="feedback error">{{ errorMessage }}</p>
      <p v-if="successMessage" class="feedback success">{{ successMessage }}</p>

      <p class="register-line">
        Sul ei ole veel arhiivi?
        <RouterLink to="/registreeru">Alusta siit</RouterLink>
      </p>
    </section>

    <footer class="footer">
      <nav>
        <RouterLink to="/meist">Meist</RouterLink>
        <RouterLink to="/privaatsus">Privaatsus</RouterLink>
        <RouterLink to="/eetika">Eetika</RouterLink>
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
  color: var(--ink, #231f20);
  font-family: var(--font-serif, 'EB Garamond', Georgia, serif);
}

.page-header-slot {
  grid-area: header;
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
  line-height: 1.03;
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
  object-position: 62% center;
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

.login-form {
  display: grid;
  gap: 10px;
  background: var(--surface, #f8f7f4);
  border: 1px solid var(--line-soft, #e4ddd1);
  border-radius: 20px;
  padding: 16px;
}

.login-form input {
  width: 100%;
  border: 1px solid var(--line-soft, #e4ddd1);
  border-radius: 999px;
  padding: 14px 18px;
  background: var(--surface-strong, #fff);
  font-size: 13px;
  font-family: var(--font-sans, 'Inter', sans-serif);
  color: #3e342e;
}

.login-form input::placeholder {
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

.forgot-link {
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 10px;
  text-align: right;
  color: #7a6e63;
  text-decoration: none;
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

.register-line {
  margin-top: 24px;
  text-align: center;
  font-style: italic;
  color: #5d4f45;
  font-size: 15px;
}

.register-line a {
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

  .login-form {
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
    max-width: none;
    font-size: clamp(62px, 6.2vw, 70px);
    line-height: 1.02;
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

  .photo-frame {
    width: min(100%, 280px);
    padding: 14px 14px 20px;
  }

  .photo-placeholder {
    height: 240px;
  }

  .login-form {
    max-width: 420px;
    margin: 0;
    gap: 12px;
    padding: 20px;
  }

  .register-line {
    max-width: 420px;
  }

  .footer {
    grid-area: footer;
    margin-top: 36px;
  }
}
</style>
