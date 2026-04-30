<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'

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
      name: form.value.name || 'Kasutaja',
      email: form.value.email || '',
    }),
  )

  router.push('/')
}
</script>

<template>
  <main class="page">
    <AppHeader :back-to="'/'" />

    <section class="title">
      <p>Perearhiiv</p>
      <h1>Alusta oma arhiivi.</h1>
      <h2>Esimesed 500 kallist mälestust on tasuta.</h2>
    </section>

    <section class="stacked-photo" aria-hidden="true">
      <div class="polaroid back" />
      <div class="polaroid front" />
    </section>

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

    <footer class="footer">
      <nav>
        <a href="#">Meist</a>
        <a href="#">Privaatsus</a>
        <a href="#">Eetika</a>
      </nav>
      <p class="bookmark">◫</p>
      <p class="copyright">© 2025 Fototeek</p>
      <p class="note">Hoiame meie esivanemate lugusid.</p>
    </footer>
  </main>
</template>

<style scoped>
.page {
  max-width: 1120px;
  margin: 0 auto;
  padding: 16px 14px 28px;
  color: #1c1714;
  font-family: Georgia, 'Times New Roman', serif;
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

@media (min-width: 900px) {
  .page {
    padding: 28px 28px 40px;
  }

  .title h1 {
    font-size: 72px;
  }

  .title h2 {
    font-size: 42px;
  }

  .stacked-photo {
    height: 230px;
  }

  .polaroid {
    width: 160px;
    height: 200px;
  }

  .register-form {
    max-width: 680px;
    margin-left: auto;
    margin-right: auto;
  }
}
</style>
