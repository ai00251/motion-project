import axios from 'axios'

const API_BASE_URL = 'http://127.0.0.1:8000'

const api = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true, 
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export default {
  getCsrfCookie: () => api.get('/sanctum/csrf-cookie'),
  
  register: (userData) => api.post('/api/register', userData),
  login: (credentials) => api.post('/api/login', credentials),
  logout: () => api.post('/api/logout'),
  
  getStyles: () => api.get('/api/styles'),
  getEvents: () => api.get('/api/events'),
  getUser: () => api.get('/api/user')
}

export const forgotPassword = (email) => {
  return api.post('/api/forgot-password', { email });
};

export const resetPassword = (data) => {
  return api.post('/api/reset-password', data);
};
