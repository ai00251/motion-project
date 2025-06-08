import { createRouter, createWebHistory } from 'vue-router'
import SignIn from '../views/SignIn.vue'
import SignUp from '../views/SignUp.vue'
import Home from '../views/Home.vue'
import ForgotPassword from '../views/ForgotPassword.vue'
import ResetPassword from '../views/ResetPassword.vue'
import Profile from '../views/Profile.vue'


const routes = [
  {
    path: '/signin',
    name: 'SignIn',
    component: SignIn
  },
  {
    path: '/signup',
    name: 'SignUp',
    component: SignUp
  },
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
  path: '/forgot-password',
  name: 'ForgotPassword',
  component: () => import('../views/ForgotPassword.vue')
  },
  {
  path: '/reset-password',
  name: 'ResetPassword',
  component: () => import('../views/ResetPassword.vue')
  },
  {
  path: '/profile',
  name: 'Profile',
  component: () => import('../views/Profile.vue')
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    return new Promise((resolve) => {
      setTimeout(() => {
        window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
        resolve({ left: 0, top: 0 });
      }, 0);
    });
  }
})

export default router
