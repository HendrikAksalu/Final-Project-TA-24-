<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'
import { getToken, logoutSession } from '@/api/fototeekApi.js'

const router = useRouter()
const route = useRoute()

const user = ref(null)
try {
  user.value = JSON.parse(localStorage.getItem('fototeek_user') || 'null')
} catch (error) {
  user.value = null
}

const isLoggedIn = computed(() => Boolean(getToken()))

/** Tekstid: Perearhiiv / Fototeek lõputöö kontekstis */
const DOCS = {
  meist: {
    title: 'Meist',
    lead: 'Fototeek on veebipõhine perearhiiv mälestuste ja fotode koondamiseks suguvõsa või sõpruskonna jaoks.',
    sections: [
      {
        heading: 'Miks seda vaja on?',
        paragraphs: [
          'Perekonna fotod ja lood satuvad sageli eri kanalitesse — vestlusäpid, pilveteenused ja kaustad arvutis muudavad ülevaate raskesti hoitavaks. Fototeek pakub ühte kohta, kuhu mälestusi struktureeritult lisada ja jagada neid ainult nendega, kellele ise õigused annad.',
          'Rakendus sobib kasutamiseks eraviisiliselt pereringis; see ei ole ametlik arhiiv ega muuseumitööriist, vaid praktiline vahend digitaalse pärandi korraldamiseks.',
        ],
      },
      {
        heading: 'Kuidas see töötab?',
        paragraphs: [
          'Pärast registreerumist saad luua albumeid ning lisada mälestusi koos teksti ja piltidega. Albumi saab jagada ka teiste kasutajatega vastavalt rakenduses seatud rollidele.',
          'Arendusprojekt kasutab tänapäevast veebitehnoloogiat (Vue üheleherakendus ja Laravel API). Üksikasjad leiab projekti dokumentatsioonist ja lähtekoodist.',
        ],
      },
      {
        heading: 'Kontakt',
        paragraphs: [
          'Ühendust saab võtta lehe autoriga Hendrik Aksalu, meil: aksaluhendrik@gmail.com.',
        ],
      },
    ],
  },
  privaatsus: {
    title: 'Privaatsus',
    lead: 'Siin kirjeldatakse lühidalt, millised isikuandmed rakendus töötleb ja kuidas kasutaja oma kontot hallata saab. Tekst on informatiivne õppeprojekti kontekstis ja ei asenda juristi koostatud privaatsuspoliitikat.',
    sections: [
      {
        heading: 'Millised andmed kogunevad?',
        paragraphs: [
          'Registreerumisel salvestatakse vähemalt nimi, e-posti aadress ja krüpteeritud salasõna. Kasutamise käigus võid lisada mälestuste tekste, pilte ja albumeid ning jagamisõigusi.',
          'Andmed hoitakse serveris ja andmebaasis vastavalt selle keskkonna seadistusele, kus Laravel rakendus töötab (nt õppe-, testimis- või juurutuskeskkond).',
        ],
      },
      {
        heading: 'Autentimine ja sessioon',
        paragraphs: [
          'Rakenduses kasutatakse token-põhist sisselogimist: brauseris hoitakse autoriseerimise jaoks väärtusi (nt token ja kasutaja põhiinfo kohalikus salvestuses). See võimaldab mugavat tööd, kuid sellega kaasneb ka risk jagatud või avaliku arvuti korral — sellisel juhul logi alati välja.',
        ],
      },
      {
        heading: 'Sinu õigused ja kontroll',
        paragraphs: [
          'Konto sättetes saad muuta oma profiili ja salasõna. Andmete kustutamise või eksportimise võimalused sõltuvad konkreetse juurutuse seadistusest — õppeprojektis tasub selle kohta küsida arendajalt või hostist.',
        ],
      },
      {
        heading: 'Küpsised ja kolmandad osapooled',
        paragraphs: [
          'Lihtsustatud rakenduses ei ole eesmärk jälitada kasutajat reklaami jaoks. Kui hiljem lisanduvad analüütika või välisteenused (fontide või kaartide laadijad), tuleks see siia täpsustada ja vastavalt töödelda.',
        ],
      },
    ],
  },
  eetika: {
    title: 'Eetika',
    lead: 'Mälestuste ja fotode kogumine puudutab inimesi ja nende lugusid. Alljärgnevad põhimõtted aitavad mõistlikult käituda.',
    sections: [
      {
        heading: 'Nõusolek ja austus',
        paragraphs: [
          'Lisa fotosid ja isikuandmeid (nt nimed, kohtumised, tähtpäevad) ainult siis, kui sul on selleks mõistlik alus — eelistatavalt nõusolek või selge pere-/kogukonnakokkulepe. Lastest või haavatavatest isikutest kirjutades mõtle täiendavalt privaatsusele.',
        ],
      },
      {
        heading: 'Täpsus ja kontekst',
        paragraphs: [
          'Mälestuste tekstid mõjutavad seda, kuidas tulevased lugejad lugusid mõistavad. Väldi teadlikku moonutamist; kui kontekst on ebaselge, märgi see sõbralikult või jäta välja.',
        ],
      },
      {
        heading: 'Jagamine ja ligipääs',
        paragraphs: [
          'Albumi jagamine annab teistele ligipääsu sinu valitud sisule. Anna õigusi ainult neile, kes peaksid neid andmeid nägema, ning kontrolli perioodiliselt kaastöötajate nimekirja.',
        ],
      },
      {
        heading: 'Autoriõigus ja kolmandate osapoolte materjal',
        paragraphs: [
          'Ära laadi üles materjali, mille avaldamiseks sul õigust pole (nt võõras foto professionaalsest väljaandest). Kasuta oma või õigustatud allikas olevaid faile.',
        ],
      },
      {
        heading: 'Digitaalse pärandi säilitamine',
        paragraphs: [
          'Digitaalne arhiiv ei asenda varukoopiaid. Oluliste mälestuste puhul tasub mõelda ka teisele säilitamisviisile (väline ketas või teenusepakkuja varundusreeglid), kui see sulle kättesaadav on.',
        ],
      },
    ],
  },
}

const contentKey = computed(() => route.meta?.contentKey || '')
const doc = computed(() => DOCS[contentKey.value] || null)

watch(
  doc,
  (d) => {
    if (!d) router.replace('/')
  },
  { immediate: true },
)

async function logout() {
  await logoutSession()
  user.value = null
  router.push('/')
}
</script>

<template>
  <main v-if="doc" class="page page-shell">
    <AppHeader :show-auth-links="!isLoggedIn" :show-menu="isLoggedIn" @logout="logout" />

    <div class="article-inner">
      <section class="title">
        <p>Fototeek</p>
        <h1>{{ doc.title }}</h1>
        <p class="subtitle">{{ doc.lead }}</p>
      </section>

      <article class="article">
        <section v-for="(block, idx) in doc.sections" :key="idx" class="block">
          <h2>{{ block.heading }}</h2>
          <p v-for="(para, pIdx) in block.paragraphs" :key="pIdx">{{ para }}</p>
        </section>

        <nav class="bottom-nav">
          <RouterLink to="/">Tagasi avalehele</RouterLink>
        </nav>
      </article>
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
.article-inner {
  width: 100%;
  max-width: 840px;
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
  max-width: 44em;
  text-transform: none !important;
  letter-spacing: 0 !important;
  font-family: Georgia, 'Times New Roman', serif !important;
  font-style: italic;
  color: #53473f;
  font-size: 17px !important;
  line-height: 1.35;
}

.article {
  color: var(--ink, #231f20);
}

.block {
  margin-bottom: 26px;
}

.block h2 {
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #6b6058;
  margin: 0 0 12px;
}

.block p {
  margin: 0 0 12px;
  font-size: 15px;
  line-height: 1.65;
  color: #2f2925;
}

.block p:last-child {
  margin-bottom: 0;
}

.bottom-nav {
  display: flex;
  flex-wrap: wrap;
  gap: 14px 18px;
  margin-top: 36px;
  font-family: var(--font-sans, 'Inter', sans-serif);
  font-size: 13px;
}

.bottom-nav a {
  color: #5c4d3f;
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
