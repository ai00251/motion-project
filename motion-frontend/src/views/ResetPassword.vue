<template>
  <div class="forgot-password-page">
    <div class="forgot-password-container">
      <h2 class="forgot-password-title">set new password</h2>
      <p class="forgot-password-description">
        enter your email and create a new secure password
      </p>
      
      <div v-if="message" class="success-message">
        {{ message }}
      </div>
      
      <div v-if="error" class="error-message">
        {{ error }}
      </div>
      
      <form class="forgot-password-form" @submit.prevent="handleResetPassword">
        <input 
          v-model="email"
          type="email" 
          placeholder="e-mail" 
          class="forgot-password-input"
          required
        >
        <input 
          v-model="password"
          type="password" 
          placeholder="new password" 
          class="forgot-password-input"
          required
        >
        <input 
          v-model="password_confirmation"
          type="password" 
          placeholder="confirm password" 
          class="forgot-password-input"
          required
        >
        <button type="submit" class="forgot-password-button" :disabled="loading">
          {{ loading ? 'resetting...' : 'reset password' }}
        </button>
      </form>
      
      <p class="back-to-signin-text">
        suddenly remembered your password? 
        <router-link to="/signin" class="back-to-signin-link">sign in</router-link>
      </p>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ResetPassword',
  data() {
    return {
      email: '',
      password: '',
      password_confirmation: '',
      loading: false,
      message: '',
      error: ''
    }
  },
  methods: {
    async handleResetPassword() {
      this.loading = true;
      this.message = '';
      this.error = '';
      
      const token = this.$route.query.token;
      
      if (!token) {
        this.error = 'Invalid reset token';
        this.loading = false;
        return;
      }
      
      console.log('Making reset password API call...');
      
      try {
        const response = await axios.post('http://127.0.0.1:8000/api/reset-password', {
          token: token,
          email: this.email,
          password: this.password,
          password_confirmation: this.password_confirmation
        }, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });
        
        console.log('Success:', response.data);
        this.message = response.data.message;
        
        // Redirect to login after 3 seconds
        setTimeout(() => {
          this.$router.push('/signin');
        }, 3000);
        
      } catch (error) {
        console.error('Error:', error);
        if (error.response && error.response.data) {
          this.error = error.response.data.message || 'An error occurred';
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
.forgot-password-page {
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

.forgot-password-page::before {
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

.forgot-password-container {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  padding: 40px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
  max-width: 400px;
  width: 100%;
}

.forgot-password-title {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 32px;
  font-weight: 400;
  line-height: 1.5;
  text-align: center;
  margin-bottom: 20px;
  text-shadow:
    0 0 8px #ffe18d,
    0 0 20px #ffe18d80;
}

.forgot-password-description {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: rgba(255, 255, 255, 0.8);
  font-size: 16px;
  text-align: center;
  margin-bottom: 30px;
  line-height: 1.4;
}

.forgot-password-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
  min-width: 300px;
}

.forgot-password-input {
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

.forgot-password-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.forgot-password-input:focus {
  outline: none;
  transform: scale(1.02);
}

.forgot-password-button {
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

.forgot-password-button:hover:not(:disabled) {
  box-shadow:
    0 0 32px #ffdc8f9e;
  transform: scale(1.05);
}

.forgot-password-button:active:not(:disabled) {
  transform: scale(0.98);
}

.forgot-password-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.back-to-signin-text {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: rgba(255, 255, 255, 0.856);
  font-size: 16px;
  text-align: center;
  margin-top: 25px;
  margin-bottom: 0;
}

.back-to-signin-link {
  color: #ffffff;
  text-decoration: none;
  font-family: 'NeueHaasDisplayBold', sans-serif;
  transition: 
    color 0.3s,
    text-shadow 0.3s;
}

.back-to-signin-link:hover {
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
