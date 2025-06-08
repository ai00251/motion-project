<template>
  <div class="signin-page">
    <div class="signin-container">
      <h2 class="signin-title">log in</h2>
      
      <div v-if="message" class="success-message">
        {{ message }}
      </div>
      
      <div v-if="error" class="error-message">
        {{ error }}
      </div>
      
      <form class="signin-form" @submit.prevent="handleLogin">
        <input 
          v-model="email"
          type="email" 
          placeholder="e-mail" 
          class="signin-input"
          required
        >
        <input 
          v-model="password"
          type="password" 
          placeholder="password" 
          class="signin-input"
          required
        >
        <button type="submit" class="signin-button" :disabled="loading">
          {{ loading ? 'logging in...' : 'log in' }}
        </button>
      </form>
      
      <p class="forgot-password-text">
        <router-link to="/forgot-password" class="forgot-password-link">forgot password?</router-link>
      </p>
      
      <p class="signup-text">
        don't have an account? 
        <router-link to="/signup" class="signup-link">sign up!</router-link>
      </p>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'SignIn',
  data() {
    return {
      email: '',
      password: '',
      loading: false,
      message: '',
      error: ''
    }
  },
  methods: {
    async handleLogin() {
      this.loading = true;
      this.message = '';
      this.error = '';
      
      console.log('Making login API call...');
      
      try {
        const response = await axios.post('http://127.0.0.1:8000/api/login', {
          email: this.email,
          password: this.password
        }, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });
        
        console.log('Login success:', response.data);
        
        localStorage.setItem('auth_token', response.data.token);
        
        localStorage.setItem('user', JSON.stringify(response.data.user));
        
        window.dispatchEvent(new CustomEvent('auth-changed'));
        
        this.message = 'Login successful! Redirecting...';
        
        setTimeout(() => {
          this.$router.push('/');
        }, 1000);
        
      } catch (error) {
        console.error('Login error:', error);
        if (error.response && error.response.data) {
          this.error = error.response.data.message || 'Login failed';
        } else {
          this.error = 'Network error. Please try again.';
        }
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>
.signin-page {
  position: relative;
  min-height: 100vh;
  width: 100%;
  padding: 100px 20px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
  overflow-y: auto;
  z-index: 0;
}

.signin-page::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-image: 
    linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)),
    url('/images/signinbg.webp');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  z-index: -1;
  pointer-events: none;
}

.signin-container {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  padding: 40px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
}

.signin-title {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 32px;
  font-weight: 400;
  line-height: 1.5;
  text-align: center;
  margin-bottom: 30px;
  text-shadow:
    0 0 8px #ffe18d,
    0 0 20px #ffe18d80;
}

.signin-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
  min-width: 300px;
}

.signin-input {
  font-family: 'NeueHaasDisplay', sans-serif;
  font-size: 18px;
  padding: 15px 20px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  backdrop-filter: blur(5px);
  transition: 
    border-color 0.3s,
    box-shadow 0.3s,
    transform 0.2s cubic-bezier(.4,2,.6,1);
}

.signin-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.signin-input:focus {
  outline: none;
  transform: scale(1.02);
}

.signin-button {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  font-size: 20px;
  padding: 15px 30px;
  background: #ffdc8f9e;
  color: #ffffff;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: 
    box-shadow 0.3s,
    background 0.3s,
    color 0.3s,
    transform 0.2s cubic-bezier(.4,2,.6,1);
  margin-top: 10px;
}

.signin-button:hover:not(:disabled) {
  box-shadow:
    0 0 32px #ffdc8f9e;
  transform: scale(1.05);
}

.signin-button:active:not(:disabled) {
  transform: scale(0.98);
}

.signin-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.forgot-password-text {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: rgba(255, 255, 255, 0.856);
  font-size: 14px;
  text-align: center;
  margin-top: 15px;
  margin-bottom: 10px;
}

.forgot-password-link {
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  font-family: 'NeueHaasDisplay', sans-serif;
  transition: 
    color 0.3s,
    text-shadow 0.3s;
}

.forgot-password-link:hover {
  color: #ffffff;
  text-shadow:
    0 0 8px #ffe18d,
    0 0 20px #ffe18d80;
}

.signup-text {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: rgba(255, 255, 255, 0.856);
  font-size: 16px;
  text-align: center;
  margin-top: 25px;
  margin-bottom: 0;
}

.signup-link {
  color: #ffffff;
  text-decoration: none;
  font-family: 'NeueHaasDisplayBold', sans-serif;
  transition: 
    color 0.3s,
    text-shadow 0.3s;
}

.signup-link:hover {
  text-shadow:
    0 0 8px #ffe18d,
    0 0 20px #ffe18d80;
}

.success-message {
  background: rgba(76, 175, 80, 0.2);
  border: 1px solid rgba(76, 175, 80, 0.5);
  color: #4CAF50;
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 20px;
  text-align: center;
  font-family: 'NeueHaasDisplay', sans-serif;
  backdrop-filter: blur(5px);
}

.error-message {
  background: rgba(244, 67, 54, 0.2);
  border: 1px solid rgba(244, 67, 54, 0.5);
  color: #f44336;
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 20px;
  text-align: center;
  font-family: 'NeueHaasDisplay', sans-serif;
  backdrop-filter: blur(5px);
}
</style>
