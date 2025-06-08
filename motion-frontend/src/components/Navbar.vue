<template>
  <nav class="nav-container">
    <img
      src="/images/logo.webp"
      alt="Logo"
      class="logo"
    />
    <div class="nav-links">
      <router-link to="/" class="nav-link">home</router-link>
      <a href="#" class="nav-link">groups</a>
      <a href="#" class="nav-link">crew</a>
      <a href="#" class="nav-link">classes</a>

      <router-link 
        v-if="!isLoggedIn" 
        to="/signin" 
        class="nav-link sign-in-button"
      >
        sign in
      </router-link>
      
      <router-link 
        v-if="isLoggedIn" 
        to="/profile" 
        class="nav-link"
      >
        <i class="fa-regular fa-user"></i>
      </router-link>
    </div>
  </nav>
</template>

<script>
export default {
  name: 'NavigationBar',
  data() {
    return {
      forceUpdate: 0 // this will trigger reactivity
    }
  },
  computed: {
    isLoggedIn() {
      // force reactivity by referencing forceUpdate
      this.forceUpdate;
      return !!localStorage.getItem('auth_token');
    }
  },
  mounted() {
    // listen for custom auth events
    window.addEventListener('auth-changed', this.updateAuth);
  },
  beforeUnmount() {
    window.removeEventListener('auth-changed', this.updateAuth);
  },
  methods: {
    updateAuth() {
      this.forceUpdate++; // this triggers the computed property to re-run
    }
  }
}
</script>

<style scoped>
.nav-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 30px 50px 0;
  height: 70px;
  box-sizing: border-box;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 1000;
  background: linear-gradient(
    to bottom,
    rgba(0,0,0,0.45) 0%, 
    rgba(0,0,0,0.18) 40%, 
    rgba(0,0,0,0.0) 80%   
  );
  backdrop-filter: blur(1px);
  border-bottom: none;
}

.logo {
  width: 180px;
  filter: drop-shadow(0 0 5px rgba(0, 0, 0));
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 50px; /* increased from 40px to maintain visual balance */
}

.nav-link {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: #fff;
  font-size: 17px;
  font-weight: 400;
  line-height: 1.5;
  text-decoration: none;
  position: relative;
  transition: 
    color 0.3s,
    text-shadow 0.3s,
    transform 0.2s cubic-bezier(.4,2,.6,1);
}

.nav-link:hover {
  color: #ffffff;
  text-shadow:
    0 0 8px #ffe18d,
    0 0 20px #ffe18d80,
    0 0 40px #ffe18d40;
  transform: scale(1.08);
  z-index: 1;
}

.sign-in-button {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  padding: 8px 20px;
  background: #ffdc8f9e;
  color: #ffffff;
  border-radius: 10px;
  transition: 
    box-shadow 0.3s,
    background 0.3s,
    color 0.3s,
    transform 0.2s cubic-bezier(.4,2,.6,1);
}

.sign-in-button:hover {
  box-shadow:
    0 0 32px #ffe18d80;
  transform: scale(1.07);
}
</style>
