<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

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

  localStorage.setItem(
    'fototeek_user',
    JSON.stringify({
      name: 'User',
      email: form.value.email || '',
    }),
  )

  router.push('/')
}
</script>

<template>
  <main class="page">
    <header class="topbar">
      <RouterLink to="/" class="icon-link" aria-label="Go back">←</RouterLink>
      <div class="brand-wrap">
        <span class="book-icon">◧</span>
        <span class="brand-text">Fototeek</span>
      </div>
      <button class="icon-link" aria-label="More actions">⋮</button>
    </header>

    <section class="title">
      <p>Family archives</p>
      <h1>Welcome back.</h1>
      <h2>Your memories are waiting.</h2>
    </section>

    <section class="photo" aria-hidden="true">
      <div class="photo-polaroid" />
    </section>

    <form class="login-form" @submit.prevent="onSubmit">
      <input v-model="form.email" type="email" placeholder="Email Address" />

      <div class="password-wrap">
        <input v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="Password" />
        <button type="button" @click="showPassword = !showPassword">
          {{ showPassword ? 'HIDE' : 'SHOW' }}
        </button>
      </div>

      <a href="#" class="forgot-link">Forgot your password?</a>
      <button type="submit" class="submit-btn" :disabled="isSubmitting">
        {{ isSubmitting ? 'Signing in...' : 'Sign in' }}
      </button>
    </form>

    <p v-if="errorMessage" class="feedback error">{{ errorMessage }}</p>
    <p v-if="successMessage" class="feedback success">{{ successMessage }}</p>

    <p class="register-line">
      Don't have an archive yet?
      <RouterLink to="/register">Begin here</RouterLink>
    </p>

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
  font-size: 58px;
  font-weight: 500;
  line-height: 0.95;
}

.title h2 {
  margin-top: 10px;
  font-style: italic;
  font-size: 38px;
  font-weight: 500;
  line-height: 1.02;
  color: #3e322a;
}

.photo {
  margin-top: 18px;
  display: flex;
  justify-content: center;
}

.photo-polaroid {
  width: 152px;
  height: 190px;
  background: #f3f0e8;
  box-shadow: 0 5px 14px rgba(15, 9, 7, 0.16);
  transform: rotate(-2deg);
  position: relative;
}

.photo-polaroid::before {
  content: '';
  position: absolute;
  inset: 13px 12px 36px;
  background: radial-gradient(circle at 55% 45%, #6a6a6a 0%, #2f2f2f 50%, #1f1f1f 100%);
}

.photo-polaroid::after {
  content: '';
  position: absolute;
  width: 36px;
  height: 11px;
  left: 50%;
  top: -8px;
  transform: translateX(-50%);
  background: #ede8de;
}

.login-form {
  margin-top: 36px;
  display: grid;
  gap: 11px;
}

.login-form input {
  width: 100%;
  border: none;
  border-radius: 14px;
  padding: 17px 18px;
  background: #f4f4f4;
  font-size: 14px;
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
  font-size: 11px;
  font-weight: 700;
  color: #5c5047;
  cursor: pointer;
}

.forgot-link {
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-family: Arial, sans-serif;
  font-size: 10px;
  text-align: right;
  color: #7a6e63;
  text-decoration: none;
}

.submit-btn {
  margin-top: 4px;
  border: none;
  border-radius: 999px;
  background: #1e130c;
  color: #fff;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-family: Arial, sans-serif;
  font-weight: 700;
  padding: 16px;
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
  margin-top: 28px;
  text-align: center;
  font-style: italic;
  color: #5d4f45;
  font-size: 16px;
}

.register-line a {
  color: #1c1714;
  font-style: normal;
  font-weight: 700;
  text-decoration: none;
}

.footer {
  margin-top: 34px;
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
