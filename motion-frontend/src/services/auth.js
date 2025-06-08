import api from './api'

export default {
  async login(credentials) {
    // CSRF cookie first
    await api.getCsrfCookie()
    
    try {
      const response = await api.login(credentials)
      
      // storing
      localStorage.setItem('auth_token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
      
      return response.data
    } catch (error) {
      throw error
    }
  },

  async register(userData) {
    // CSRF cookie
    await api.getCsrfCookie()
    
    try {
      const response = await api.register(userData)
      
      // storing
      localStorage.setItem('auth_token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
      
      return response.data
    } catch (error) {
      throw error
    }
  },

  async logout() {
    try {
      await api.logout()
    } catch (error) {
      console.log('Logout error:', error)
    } finally {
      // clear data 
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
    }
  },

  // if user is authenticated
  isAuthenticated() {
    return !!localStorage.getItem('auth_token')
  },

  // current user data
  getUser() {
    const user = localStorage.getItem('user')
    return user ? JSON.parse(user) : null
  }
}
