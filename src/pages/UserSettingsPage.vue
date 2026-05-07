<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'
import { apiFetch, getToken, logoutSession, parseApiError } from '@/api/fototeekApi.js'

const router = useRouter()
const user = ref(null)
try {
  user.value = JSON.parse(localStorage.getItem('fototeek_user') || 'null')
} catch (error) {
  user.value = null
}

const isLoggedIn = computed(() => Boolean(user.value && getToken()))

const profileLoading = ref(false)
const profileSaving = ref(false)
const profileError = ref('')
const profileSuccess = ref('')
const profileForm = reactive({
  name: '',
  email: '',
})

const pwSaving = ref(false)
const pwError = ref('')
const pwSuccess = ref('')
const showPw = ref(false)
const pwForm = reactive({
  current: '',
  next: '',
  nextConfirm: '',
})

function persistUser(payload) {
  user.value = payload
  if (payload) {
    localStorage.setItem('fototeek_user', JSON.stringify(payload))
  }
}

async function refreshProfileFromApi() {
  profileLoading.value = true
  profileError.value = ''
  try {
    const res = await apiFetch('/user')
    if (!res.ok) {
      profileError.value = await parseApiError(res, 'Profiili ei laaditud.')
      return
    }
    const data = await res.json()
    const u = data?.user
    if (u) {
      persistUser(u)
      profileForm.name = u.name || ''
      profileForm.email = u.email || ''
    }
  } finally {
    profileLoading.value = false
  }
}

onMounted(() => {
  void refreshProfileFromApi()
})

async function saveProfile() {
  profileSaving.value = true
  profileError.value = ''
  profileSuccess.value = ''

  const name = profileForm.name.trim()
  const email = profileForm.email.trim()
  if (!name || !email) {
    profileError.value = 'Täida nimi ja e-post.'
    profileSaving.value = false
    return
  }

  try {
    const res = await apiFetch('/user', {
      method: 'PATCH',
      body: { name, email },
    })

    if (!res.ok) {
      profileError.value = await parseApiError(res, 'Profiili ei õnnestunud salvestada.')
      return
    }

    const data = await res.json()
    if (data?.user) {
      persistUser(data.user)
      profileForm.name = data.user.name || ''
      profileForm.email = data.user.email || ''
    }
    profileSuccess.value = data?.message || 'Salvestatud.'
  } catch (error) {
    profileError.value = 'Serveriga ei saanud ühendust.'
  } finally {
    profileSaving.value = false
  }
}

async function savePassword() {
  pwSaving.value = true
  pwError.value = ''
  pwSuccess.value = ''

  if (!pwForm.current || !pwForm.next || !pwForm.nextConfirm) {
    pwError.value = 'Täida kõik salasõna väljad.'
    pwSaving.value = false
    return
  }

  if (pwForm.next !== pwForm.nextConfirm) {
    pwError.value = 'Uued salasõnad ei kattu.'
    pwSaving.value = false
    return
  }

  try {
    const res = await apiFetch('/user/password', {
      method: 'PATCH',
      body: {
        current_password: pwForm.current,
        password: pwForm.next,
        password_confirmation: pwForm.nextConfirm,
      },
    })

    if (!res.ok) {
      pwError.value = await parseApiError(res, 'Salasõna ei õnnestunud muuta.')
      return
    }

    const data = await res.json().catch(() => ({}))
    pwSuccess.value = data?.message || 'Salasõna uuendatud.'
    pwForm.current = ''
    pwForm.next = ''
    pwForm.nextConfirm = ''
  } catch (error) {
    pwError.value = 'Serveriga ei saanud ühendust.'
  } finally {
    pwSaving.value = false
  }
}

async function logout() {
  await logoutSession()
  user.value = null
  router.push('/')
}
</script>

<template>
  <main class="page page-shell">
    <AppHeader :show-auth-links="!isLoggedIn" :show-menu="isLoggedIn" @logout="logout" />

    <div class="settings-inner">
      <section class="title">
        <p>Konto</p>
        <h1>Kasutaja seaded</h1>
        <p class="subtitle">Uuenda oma profiili ja salasõna — muudatused salvestatakse serverisse.</p>
      </section>

      <section class="settings-card">
        <p v-if="profileLoading" class="muted">Laadin profiili…</p>

        <div class="block">
        <h2 class="block-title">Profiil</h2>
        <form class="stack-form" @submit.prevent="saveProfile">
          <label class="field">
            <span class="label">Nimi</span>
            <input v-model="profileForm.name" type="text" autocomplete="name" class="input" />
          </label>
          <label class="field">
            <span class="label">E-post</span>
            <input v-model="profileForm.email" type="email" autocomplete="email" class="input" />
          </label>
          <button type="submit" class="primary-btn" :disabled="profileSaving || profileLoading">
            {{ profileSaving ? 'Salvestan…' : 'Salvesta profiil' }}
          </button>
        </form>
        <p v-if="profileError" class="feedback error">{{ profileError }}</p>
        <p v-if="profileSuccess" class="feedback success">{{ profileSuccess }}</p>
        </div>

        <div class="block divider-top">
        <h2 class="block-title">Salasõna</h2>
        <form class="stack-form" @submit.prevent="savePassword">
          <label class="field">
            <span class="label">Praegune salasõna</span>
            <input
              v-model="pwForm.current"
              :type="showPw ? 'text' : 'password'"
              autocomplete="current-password"
              class="input"
            />
          </label>
          <label class="field">
            <span class="label">Uus salasõna</span>
            <input
              v-model="pwForm.next"
              :type="showPw ? 'text' : 'password'"
              autocomplete="new-password"
              class="input"
            />
          </label>
          <label class="field">
            <span class="label">Uus salasõna uuesti</span>
            <input
              v-model="pwForm.nextConfirm"
              :type="showPw ? 'text' : 'password'"
              autocomplete="new-password"
              class="input"
            />
          </label>
          <label class="toggle-line">
            <input v-model="showPw" type="checkbox" />
            <span>Näita salasõnu</span>
          </label>
          <button type="submit" class="primary-btn secondary-tone" :disabled="pwSaving">
            {{ pwSaving ? 'Uuendan…' : 'Muuda salasõna' }}
          </button>
        </form>
        <p v-if="pwError" class="feedback error">{{ pwError }}</p>
        <p v-if="pwSuccess" class="feedback success">{{ pwSuccess }}</p>
        </div>

        <div class="hint-box">
        <p><strong>„Unauthenticated“?</strong> Klõpsa menüüst <strong>Logi välja</strong> ja logi uuesti sisse.</p>
        <p>
          <strong>„Invalid credentials“?</strong> Kui andmebaas on kord tühjaks käidud, pead <RouterLink to="/registreeru">uuesti registreeruma</RouterLink>.
        </p>
        </div>

        <div class="settings-links">
          <RouterLink to="/albumid" class="back-link">← Tagasi albumitesse</RouterLink>
          <RouterLink to="/" class="back-link">← Tagasi avalehele</RouterLink>
        </div>
      </section>
    </div>

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
.settings-inner {
  width: 100%;
  max-width: 640px;
  margin: 0 auto;
  box-sizing: border-box;
}

.title {
  margin-top: 26px;
  margin-bottom: 28px;
  text-align: center;
}

.title > p:first-of-type {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-family: Arial, sans-serif;
  font-size: 10px;
  color: var(--ink, #231f20);
}

.title h1 {
  margin: 16px 0 0;
  font-family: var(--font-serif, 'EB Garamond', Georgia, serif);
  font-size: clamp(40px, 9vw, 56px);
  line-height: 0.98;
  font-weight: 500;
  color: var(--ink, #231f20);
}

.subtitle {
  margin: 16px auto 0;
  max-width: 30em;
  text-transform: none !important;
  letter-spacing: 0 !important;
  font-family: Georgia, 'Times New Roman', serif !important;
  font-style: italic;
  color: #53473f;
  font-size: 17px !important;
  line-height: 1.35;
}

.muted {
  margin: 0 0 12px;
  font-size: 13px;
  color: #8a8078;
}

.settings-card {
  background: var(--surface-strong, #fff);
  border: 1px solid var(--line-soft, #ddd4c6);
  border-radius: 14px;
  padding: 22px 20px 24px;
}

.block {
  margin-bottom: 0;
}

.divider-top {
  margin-top: 26px;
  padding-top: 26px;
  border-top: 1px solid #ebe6df;
}

.block-title {
  margin: 0 0 14px;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: #6b6058;
}

.stack-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #8a8078;
}

.input {
  border: 1px solid #d6ccbe;
  border-radius: 10px;
  padding: 11px 12px;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 14px;
  background: #fffdfa;
  color: var(--ink, #231f20);
}

.input:focus {
  outline: 2px solid rgba(30, 19, 12, 0.18);
  outline-offset: 1px;
}

.toggle-line {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #5c534c;
  cursor: pointer;
}

.toggle-line input {
  accent-color: #1e130c;
}

.primary-btn {
  align-self: flex-start;
  margin-top: 4px;
  border: 0;
  border-radius: 999px;
  padding: 11px 22px;
  background: #1e130c;
  color: #fff;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  cursor: pointer;
}

.primary-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.primary-btn.secondary-tone {
  background: #3f342d;
}

.feedback {
  margin: 12px 0 0;
  font-size: 13px;
}

.feedback.error {
  color: #8b2942;
}

.feedback.success {
  color: #2d6b4a;
}

.hint-box {
  margin-top: 26px;
  padding: 14px 14px 16px;
  background: #f8f4ed;
  border-radius: 10px;
  border: 1px solid #e8dfd3;
}

.hint-box p {
  margin: 0 0 10px;
  font-size: 13px;
  line-height: 1.55;
  color: #4a423c;
}

.hint-box p:last-child {
  margin-bottom: 0;
}

.hint-box :deep(a) {
  color: #5c4d3f;
  font-weight: 600;
}

.settings-links {
  display: flex;
  justify-content: center;
  gap: 16px;
  flex-wrap: wrap;
}

.back-link {
  display: inline-block;
  margin-top: 22px;
  font-size: 13px;
  color: #5c4d3f;
  text-decoration: underline;
  text-underline-offset: 3px;
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
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-weight: 500;
}

.footer a {
  color: var(--ink, #231f20);
  text-decoration: none;
}

.copyright {
  margin-top: 20px;
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
