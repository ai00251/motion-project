<template>
  <div class="signup-page">
    <div class="signup-container">
      <h2 class="signup-title">sign up</h2>
      <form class="signup-form" @submit.prevent="handleSignUp">
        <!-- Show validation errors -->
        <div v-if="Object.keys(errors).length > 0" class="error-messages">
          <div v-for="(errorArray, field) in errors" :key="field" class="error-message">
            {{ errorArray[0] }}
          </div>
        </div>

        <input 
          type="text" 
          placeholder="name" 
          class="signup-input"
          v-model="form.name"
          :class="{ 'error': errors.name }"
          required
        >
        <input 
          type="text" 
          placeholder="last name" 
          class="signup-input"
          v-model="form.surname"
          :class="{ 'error': errors.surname }"
          required
        >
        <input 
          type="date" 
          placeholder="birth date" 
          class="signup-input"
          v-model="form.birth_date"
          :class="{ 'error': errors.birth_date }"
          required
        >
        <input 
          type="tel" 
          placeholder="phone" 
          class="signup-input"
          v-model="form.phone_number"
          :class="{ 'error': errors.phone_number }"
          required
        >
        <input 
          type="email" 
          placeholder="e-mail" 
          class="signup-input"
          v-model="form.email"
          :class="{ 'error': errors.email }"
          required
        >
        <input 
          type="password" 
          placeholder="password" 
          class="signup-input"
          v-model="form.password"
          :class="{ 'error': errors.password }"
          required
        >
        <input 
          type="password" 
          placeholder="confirm password" 
          class="signup-input"
          v-model="form.password_confirmation"
          :class="{ 'error': errors.password_confirmation || passwordMismatch }"
          required
        >
        <div v-if="passwordMismatch" class="error-message" style="margin-top: -15px;">
          Passwords do not match
        </div>

        <button 
          type="submit" 
          class="signup-button"
          :disabled="loading || passwordMismatch"
        >
          {{ loading ? 'signing up...' : 'sign up' }}
        </button>
      </form>
      <p class="signin-text">
        already have an account? 
        <router-link to="/signin" class="signin-link">log in!</router-link>
      </p>
    </div>
  </div>
</template>

<script>
import auth from '../services/auth'

export default {
  name: 'SignUp',
  data() {
    return {
      form: {
        name: '',
        surname: '',
        birth_date: '',
        email: '',
        phone_number: '',
        password: '',
        password_confirmation: ''
      },
      errors: {},
      loading: false
    }
  },
  computed: {
    passwordMismatch() {
      return this.form.password && this.form.password_confirmation && 
             this.form.password !== this.form.password_confirmation
    }
  },
  methods: {
    async handleSignUp() {
      if (this.passwordMismatch) {
        return
      }

      this.loading = true
      this.errors = {}
      
      try {
        const response = await auth.register(this.form)
        
        console.log('Registration successful:', response)
        alert('Registration successful! Welcome to Motion!')
        
        this.$router.push('/')
        
      } catch (error) {
        console.error('Registration error:', error)
        
        if (error.response && error.response.status === 422) {
          this.errors = error.response.data.errors
        } else {
          alert('Registration failed. Please try again.')
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.signup-page {
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

.signup-page::before {
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

.signup-container {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  padding: 40px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
}

.signup-title {
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

.signup-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
  min-width: 300px;
}

.signup-input {
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

.signup-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.signup-input:focus {
  outline: none;
  transform: scale(1.02);
}

.signup-input.error {
  border-color: #ff6b6b;
  box-shadow: 0 0 10px rgba(255, 107, 107, 0.3);
}

.error-messages {
  background: rgba(255, 107, 107, 0.1);
  border: 1px solid rgba(255, 107, 107, 0.3);
  border-radius: 10px;
  padding: 15px;
  margin-bottom: 10px;
}

.error-message {
  color: #ff6b6b;
  font-family: 'NeueHaasDisplay', sans-serif;
  font-size: 14px;
  margin-bottom: 5px;
}

.error-message:last-child {
  margin-bottom: 0;
}

.signup-button {
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

.signup-button:hover:not(:disabled) {
  box-shadow:
    0 0 32px #ffdc8f9e;
  transform: scale(1.05);
}

.signup-button:active:not(:disabled) {
  transform: scale(0.98);
}

.signup-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.signin-text {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: rgba(255, 255, 255, 0.8);
  font-size: 16px;
  text-align: center;
  margin-top: 25px;
  margin-bottom: 0;
}

.signin-link {
  color: #ffffff;
  text-decoration: none;
  font-family: 'NeueHaasDisplayBold', sans-serif;
  transition: 
    color 0.3s,
    text-shadow 0.3s;
}

.signin-link:hover {
  color: #ffffff;
  text-shadow:
    0 0 8px #ffe18d,
    0 0 20px #ffe18d80;
}
</style>
