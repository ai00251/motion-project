<template>
  <div>
    <section class="home-hero">
      <div class="left-block">
        <div class="headline-art">
          <span class="united-by">united by</span>
          <br />
          <span class="motion">MOTION</span>
        </div>
      </div>
      <div class="right-block">
        <div class="welcome-block">
          <p class="welcome-text">
            welcome to “motion” — a space where music meets movement, and every step tells a story. with classes for all levels across a variety of styles, we’re here to inspire, challenge, and connect. whether you’re just starting out or mastering your craft, this is where passion and technique come together. step in, move freely, and be part of something bigger.
          </p>
        </div>
      </div>
    </section>

    <section class="about-section">
      <div class="about-content">
        <h2>about us</h2>
        <p>
          “motion” is more than just a studio—it's a community of passionate dancers and teachers. our mission is to provide a welcoming space for everyone to explore, express, and evolve through dance. from hip-hop to contemporary, from beginner to advanced, we have something for everyone.
        </p>
      </div>
    </section>

    <section class="classes-section">
      <h2>our classes</h2>
      <div class="classes-list">
        <div class="class-card">
          <h3>hip-hop</h3>
          <p>energetic, expressive, and always evolving. all levels welcome.</p>
        </div>
        <div class="class-card">
          <h3>contemporary</h3>
          <p>fluid movement, emotional storytelling, and creative freedom.</p>
        </div>
        <div class="class-card">
          <h3>breaking</h3>
          <p>power moves, footwork, and style. learn from the city’s best b-boys and b-girls.</p>
        </div>
        <div class="class-card">
          <h3>kids & teens</h3>
          <p>fun, safe, and inspiring classes for the next generation of movers.</p>
        </div>
      </div>
    </section>

    <section class="crew-section">
      <h2>meet the crew</h2>
      <div class="crew-list">
        <div class="crew-member">
          <img src="https://via.placeholder.com/100" alt="Crew Member 1" />
          <h4>alex</h4>
          <p>choreographer & founder</p>
        </div>
        <div class="crew-member">
          <img src="https://via.placeholder.com/100" alt="Crew Member 2" />
          <h4>jamie</h4>
          <p>hip-hop & freestyle</p>
        </div>
        <div class="crew-member">
          <img src="https://via.placeholder.com/100" alt="Crew Member 3" />
          <h4>maria</h4>
          <p>contemporary & jazz</p>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <h2>ready to be in motion?</h2>
      <button class="cta-btn">sign up for your class</button>
    </section>
  </div>
</template>

<script>
export default {
  name: 'Home'
}
</script>

<style scoped>

.home-hero {
  height: 100vh;
  width: 100%;
  background-image:
    /* linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), */
    url('/images/homebg.webp');
  background-size: cover;
  background-position: center top;
  background-repeat: no-repeat;
  background-attachment: fixed;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
  position: relative;
  overflow: hidden;
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
  background: #181818;
  color: #ffe18d;
  padding: 80px 0 60px 0;
  display: flex;
  justify-content: center;
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

.classes-section {
  background: #232323;
  color: #fff;
  padding: 80px 0 60px 0;
  text-align: center;
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
  background: #292929;
  border-radius: 12px;
  box-shadow: 0 4px 24px #0005;
  padding: 32px 28px;
  width: 260px;
  min-height: 180px;
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: box-shadow 0.2s;
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

.crew-section {
  background: #181818;
  color: #fff;
  padding: 80px 0 60px 0;
  text-align: center;
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
  background: #232323;
  border-radius: 12px;
  padding: 24px 20px;
  width: 180px;
  box-shadow: 0 4px 24px #0005;
  display: flex;
  flex-direction: column;
  align-items: center;
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

.cta-section {
  background: linear-gradient(90deg, #ffe18d10 0%, #181818 100%);
  color: #fff;
  padding: 80px 0 100px 0;
  text-align: center;
}
.cta-section h2 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  font-size: 2rem;
  color: #ffe18d;
  margin-bottom: 2rem;
}
.cta-btn {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  background: #ffdc8f;
  color: #222;
  font-size: 1.1rem;
  border: none;
  border-radius: 10px;
  padding: 16px 40px;
  cursor: pointer;
  box-shadow: 0 0 32px #ffe18d40;
  transition: 
    background 0.2s,
    color 0.2s,
    box-shadow 0.2s;
}

.cta-btn:hover {
  background: #fff;
  color: #222;
  box-shadow: 0 0 48px #ffe18d80;
}

</style>
