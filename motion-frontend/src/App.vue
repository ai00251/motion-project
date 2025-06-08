<template>
  <div id="app">
    <NavigationBar />
    <router-view />
    <Footer />
  </div>
</template>

<script>
import NavigationBar from './components/Navbar.vue'
import Footer from './components/Footer.vue'

export default {
  name: 'App',
  components: {
    NavigationBar,
    Footer
  }
}
</script>
