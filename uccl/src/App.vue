<template>
  <div id="app">
    <UcCl />
  </div>
</template>

<script>
import UcCl from './components/UcClResults.vue'

export default {
  name: 'app',
  components: {
    UcCl
  }
}
</script>

<style>
@import './styles/site.css';
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
