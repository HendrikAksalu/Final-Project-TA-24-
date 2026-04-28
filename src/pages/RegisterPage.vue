<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

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

  localStorage.setItem(
    'fototeek_user',
    JSON.stringify({
      name: form.value.name || 'User',
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
      <h1>Begin your archive.</h1>
      <h2>Complimentary for your first 500 precious memories.</h2>
    </section>

    <section class="stacked-photo" aria-hidden="true">
      <div class="polaroid back" />
      <div class="polaroid front" />
    </section>

    <form class="register-form" @submit.prevent="onSubmit">
      <input v-model="form.name" type="text" placeholder="Full Name" />
      <input v-model="form.email" type="email" placeholder="Email Address" />

      <div class="password-wrap">
        <input v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="Password" />
        <button type="button" @click="showPassword = !showPassword">
          {{ showPassword ? 'HIDE' : 'SHOW' }}
        </button>
      </div>

      <input v-model="form.password_confirmation" type="password" placeholder="Confirm Password" />

      <button type="submit" class="submit-btn" :disabled="isSubmitting">
        {{ isSubmitting ? 'Registering...' : 'Register' }}
      </button>
    </form>

    <p v-if="errorMessage" class="feedback error">{{ errorMessage }}</p>
    <p v-if="successMessage" class="feedback success">{{ successMessage }}</p>

    <p class="signin-line">
      Already have an archive?
      <RouterLink to="/login">Sign in</RouterLink>
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

.brand-text {
  font-size: 38px;
  line-height: 1;
}

.book-icon {
  font-size: 15px;
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

.stacked-photo {
  margin-top: 18px;
  height: 180px;
  position: relative;
}

.polaroid {
  width: 118px;
  height: 146px;
  background: #f3f0e8;
  box-shadow: 0 5px 14px rgba(15, 9, 7, 0.16);
  position: absolute;
  left: 50%;
  transform-origin: center center;
}

.polaroid::before {
  content: '';
  position: absolute;
  inset: 10px 10px 28px;
  background: #d2ccbc;
}

.back {
  transform: translateX(-58%) rotate(-4deg);
  top: 20px;
}

.front {
  transform: translateX(-42%) rotate(3deg);
  top: 30px;
}

.register-form {
  margin-top: 12px;
  display: grid;
  gap: 11px;
}

.register-form input {
  width: 100%;
  border: none;
  border-radius: 14px;
  padding: 17px 18px;
  background: #f4f4f4;
  font-size: 14px;
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
  font-size: 11px;
  font-weight: 700;
  color: #5c5047;
  cursor: pointer;
}

.submit-btn {
  margin-top: 6px;
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

.signin-line {
  margin-top: 28px;
  text-align: center;
  font-style: italic;
  color: #5d4f45;
  font-size: 16px;
}

.signin-line a {
  color: #1c1714;
  font-style: normal;
  font-weight: 700;
  text-decoration: none;
}

.footer {
  margin-top: 34px;
  border-top: 1px solid #dad6cd;
  padding-top: 24px;
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
