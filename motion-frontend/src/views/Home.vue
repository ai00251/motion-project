<template>
  <div class="home-container">
    <section class="home-hero">
      <div class="left-block">
        <div class="headline-art">
          <span class="united-by">vienoti</span>
          <br />
          <span class="motion">kustībā</span>
        </div>
      </div>
      <div class="right-block">
        <div class="welcome-block">
          <p class="welcome-text">
            laipni lūgts "motion" — vietā, kur mūzika satiekas ar kustību un katrs solis izstāsta savu stāstu. mēs piedāvājam nodarbības visiem līmeņiem dažādos deju stilos, lai iedvesmotu, izaicinātu un vienotu. neatkarīgi no tā, vai tu tikai sāc vai jau pilnveido savu meistarību, šī ir vieta, kur satiekas aizrautība un tehnika. nāc iekšā, kusties brīvi un kļūsti par daļu no kā lielāka.
          </p>
        </div>
      </div>
    </section>

    <section class="about-section">
      <div class="about-content">
        <h2>par mums</h2>
        <p>
          "motion" ir vairāk nekā tikai studija — tā ir aizrautīgu dejotāju un pasniedzēju kopiena. mūsu misija ir radīt atvērtu un iedvesmojošu vidi, kur ikviens var izzināt, izpausties un attīstīties caur deju. no hiphopa līdz laikmetīgajai dejai, no iesācēju līdz augstākam līmenim — pie mums ir vieta ikvienam.
        </p>
      </div>
    </section>

    <section class="classes-section">
      <h2>mūsu stili</h2>
      <div v-if="loadingStyles" class="loading-state">
        ielādē deju stilus...
      </div>
      <div v-else class="classes-list">
        <div v-for="style in styles" :key="style.title" class="class-card">
          <h3>{{ style.title }}</h3>
          <p>{{ style.description }}</p>
        </div>
      </div>
    </section>

    <section class="crew-section">
      <h2>iepazīsti komandu</h2>
      <div v-if="loadingInstructors" class="loading-state">
        ielādē pasniedzējus...
      </div>
      <div v-else class="crew-list">
        <div v-for="(instructor, index) in instructors" :key="instructor.name" class="crew-member">
          <img :src="instructor.photo" :alt="instructor.name" />
          <h4>{{ instructor.name.toLowerCase() }}</h4>
          <p>{{ getInstructorTitle(index) }}</p>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <h2>gatavs būt kustībā?</h2>
      <router-link to="/signup" class="cta-btn">pieraksties savai nodarbībai</router-link>
    </section>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Home',
  data() {
    return {
      instructors: [],
      styles: [],
      loadingInstructors: true,
      loadingStyles: true
    }
  },
  mounted() {
    this.loadInstructors();
    this.loadStyles();
  },
  methods: {
    async loadInstructors() {
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/instructors');
        this.instructors = response.data.instructors.slice(0, 3);
      } catch (error) {
        console.error('Kļūda, ielādējot pasniedzējus:', error);
        this.instructors = [
          { name: 'alex', photo: 'https://via.placeholder.com/100' },
          { name: 'jamie', photo: 'https://via.placeholder.com/100' },
          { name: 'maria', photo: 'https://via.placeholder.com/100' }
        ];
      } finally {
        this.loadingInstructors = false;
      }
    },

    getInstructorTitle(index) {
      const titles = [
        'komerciālā deja',
        'dibinātājs un ielu dejotājs',
        'laikmetīgās dejas māksliniece',
      ];
      return titles[index] || 'deju pasniedzējs';
    },

    async loadStyles() {
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/styles');
        this.styles = response.data.styles.slice(0, 3);
      } catch (error) {
        console.error('Kļūda, ielādējot deju stilus:', error);
        this.styles = [
          { title: 'hip-hop', description: 'enerģisks, izteiksmīgs un vienmēr attīstībā. gaidīts ikviens līmenis.' },
          { title: 'contemporary', description: 'plūstoša kustība, emocionāls stāstījums un radoša brīvība.' },
          { title: 'breaking', description: 'spēka elementi, footwork un stils. mācies no pilsētas labākajiem b-boy un b-girl dejotājiem.' },
          { title: 'kids & teens', description: 'jautras, drošas un iedvesmojošas nodarbības jaunajai kustību paaudzei.' }
        ];
      } finally {
        this.loadingStyles = false;
      }
    }
  }
}
</script>

<style scoped>

.home-container {
  position: relative;
  min-height: 100vh;
  background-image: url('/images/homebg.webp');
  background-size: cover;
  background-position: center top;
  background-repeat: no-repeat;
  background-attachment: fixed;
}

.home-hero {
  height: 100vh;
  width: 100%;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
  position: relative;
  overflow: hidden;
}

.loading-state {
  text-align: center;
  padding: 40px;
  color: rgba(255, 255, 255, 0.7);
  font-family: 'NeueHaasDisplay', sans-serif;
}

.left-block {
  flex: 1 1 50%;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
  padding-left: 8vw;
}

.right-block {
  flex: 1 1 50%;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  justify-content: center;
  padding-right: 8vw;
}

.united-by {
  font-family: 'NeueHaasDisplayThinItalic', sans-serif;
  font-size: 5.2vw;
  font-weight: 300;
  color: #ffe18d;
  font-style: italic;
  letter-spacing: 0.5px;
  z-index: 2;
  position: relative;
  line-height: 1;
}

.motion {
  font-family: 'NeueHaasDisplayThinItalic', sans-serif;
  font-size: 6vw;
  font-weight: 700;
  color: #ffe18d;
  letter-spacing: 2px;
  line-height: 1.1;
  z-index: 2;
  position: relative;
  line-height: 1;
  margin-top: -0.2em;
}

.welcome-block {
  max-width: 420px;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.welcome-text {
  font-family: 'NeueHaasDisplayRoman', sans-serif;
  color: #fff;
  font-size: 1.3rem;
  line-height: 1.1;
  margin-bottom: 24px;
  text-shadow: 0 0 8px #00000080;
  text-align: right;
}

.about-section {
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(2px);
  -webkit-backdrop-filter: blur(2px);
  color: #ffe18d;
  padding: 80px 0 60px 0;
  display: flex;
  justify-content: center;
  position: relative;
}

.classes-section {
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  color: #fff;
  padding: 80px 0 60px 0;
  text-align: center;
  position: relative;
}

.crew-section {
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  color: #fff;
  padding: 80px 0 60px 0;
  text-align: center;
  position: relative;
}

.cta-section {
  background: rgba(0, 0, 0, 0.9);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  color: #fff;
  padding: 80px 0 100px 0;
  text-align: center;
  position: relative;
}

.about-content {
  max-width: 700px;
  text-align: center;
}

.about-section h2 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  font-size: 2.2rem;
  margin-bottom: 1rem;
}

.about-section p {
  font-family: 'NeueHaasDisplayRoman', sans-serif;
  color: #fff;
  font-size: 1.2rem;
}

.classes-section h2 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  font-size: 2rem;
  color: #ffe18d;
  margin-bottom: 2rem;
}

.classes-list {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 40px;
}

.class-card {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
  padding: 32px 28px;
  width: 260px;
  min-height: 180px;
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: box-shadow 0.2s;
  backdrop-filter: blur(5px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.class-card h3 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #ffe18d;
  margin-bottom: 0.5rem;
}

.class-card p {
  font-family: 'NeueHaasDisplayRoman', sans-serif;
  color: #fff;
  font-size: 1.05rem;
}

.crew-section h2 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  font-size: 2rem;
  color: #ffe18d;
  margin-bottom: 2rem;
}

.crew-list {
  display: flex;
  justify-content: center;
  gap: 40px;
}

.crew-member {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 12px;
  padding: 24px 20px;
  width: 180px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
  display: flex;
  flex-direction: column;
  align-items: center;
  backdrop-filter: blur(5px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.crew-member img {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  margin-bottom: 1rem;
  object-fit: cover;
  background: #111;
}

.crew-member h4 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #ffe18d;
  margin: 0.5rem 0 0.2rem 0;
}

.crew-member p {
  font-family: 'NeueHaasDisplayRoman', sans-serif;
  color: #fff;
  font-size: 1rem;
  margin: 0;
}

.cta-section h2 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 2rem;
  margin-bottom: 2rem;
  text-shadow:
    0 0 8px #ffe18d,
    0 0 20px #ffe18d80;
}

.cta-btn {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  background: #ffdc8f9e;
  color: #ffffff;
  font-size: 1.1rem;
  border: none;
  border-radius: 10px;
  padding: 16px 40px;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
  transition: 
    box-shadow 0.3s,
    background 0.3s,
    color 0.3s,
    transform 0.2s cubic-bezier(.4,2,.6,1);
}

.cta-btn:hover {
  box-shadow: 0 0 32px #ffdc8f9e;
  transform: scale(1.05);
}

.cta-btn:active {
  transform: scale(0.98);
}

</style>
