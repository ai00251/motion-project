<template>
  <footer class="footer">
    <div class="footer-main">
      <div class="footer-col">
        <div class="footer-socials">
          <a href="#" aria-label="instagram" class="footer-social"><i class="fa-brands fa-instagram"></i></a>
          <a href="#" aria-label="facebook" class="footer-social"><i class="fa-brands fa-facebook"></i></a>
          <a href="#" aria-label="youtube" class="footer-social"><i class="fa-brands fa-youtube"></i></a>
        </div>
      </div>
      <div class="footer-col">
        <h4>contact information</h4>
        <p>motion@rvt.lv</p>
        <p>+371 2039 7700</p>
      </div>
      <div class="footer-col">
        <h4>address / location</h4>
        <p>krišjāņa valdemāra iela 1c,<br>centra rajons, riga, lv-1010</p>
        <h4 style="margin-top:1em;">working hours</h4>
        <p>mon - fri 14:00–20:00<br>sat - sun 15:00–18:00</p>
      </div>
      <div class="footer-col">
        <h4>useful links</h4>
        <a href="#" class="footer-link">faqs</a>
        <a href="#" class="footer-link">privacy & terms</a>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; {{ new Date().getFullYear() }} motion. all rights reserved.</span>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'Footer'
}
</script>

<style scoped>
.footer {
  width: 100%;
  background: linear-gradient(
    to top,
    rgba(0,0,0,0.85) 0%,
    rgba(0,0,0,0.6) 50%,
    rgba(0,0,0,0.0) 100%
  );
  box-sizing: border-box;
  padding: 48px 0 16px 0;
  border-top: none; 
  position: relative;
  z-index: 1;
}

.footer-main {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 50px;
  flex-wrap: wrap;
  gap: 40px;
}

.footer-col {
  display: flex;
  flex-direction: column;
  min-width: 180px;
  max-width: 260px;
  gap: 0.6em;
}

.footer-socials {
  display: flex;
  gap: 18px;
  margin-top: 6px;
}

.footer-social {
  color: #ffffff;
  font-size: 1.5rem;
  transition: color 0.3s, text-shadow 0.3s;
}

.footer-social:hover {
  text-shadow: 0 0 8px #ffe18d, 0 0 20px #ffe18d80;
}

.footer-col h4 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #ffe18d;
  margin-bottom: 0.2em;
  font-size: 1.04em;
  letter-spacing: 0.5px;
}

.footer-col p, .footer-link {
  font-family: 'NeueHaasDisplayRoman', sans-serif;
  color: #fff;
  font-size: 1em;
  margin: 0 0 0.2em 0;
  opacity: 0.9;
  text-decoration: none;
    transition: color 0.3s, text-shadow 0.3s;
}

.footer-link:hover {
  text-shadow: 0 0 8px #ffbb00, 0 0 20px #ffe18d80;
}

.footer-bottom {
  text-align: center;
  margin-top: 32px;
  font-family: 'NeueHaasDisplayRoman', sans-serif;
  color: #fff;
  font-size: 14px;
  opacity: 0.7;
  letter-spacing: 0.5px;
}
</style>
