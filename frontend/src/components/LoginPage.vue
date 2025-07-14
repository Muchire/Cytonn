<template>
  <div class="login-page">
    <nav class="navbar">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <router-link to="/" class="navbar-brand">Task Manager</router-link>
          <div class="navbar-nav">
            <router-link to="/" class="nav-link">Back to Home</router-link>
          </div>
        </div>
      </div>
    </nav>
    
    <div class="login-container">
      <div class="container">
        <div class="login-card">
          <h2>Login</h2>
          <div v-if="message" :class="messageClass">
            {{ message }}
          </div>
          <form @submit.prevent="loginHandler">
            <div class="form-group">
              <label for="username">Username</label>
              <input 
                type="text" 
                id="username" 
                v-model="username" 
                class="form-control" 
                required
              >
            </div>
            <div class="form-group">
              <label for="password">Password</label>
              <input 
                type="password" 
                id="password" 
                v-model="password" 
                class="form-control" 
                required
              >
            </div>
            <button type="submit" class="btn btn-primary" :disabled="isLoading">
              {{ isLoading ? 'Logging in...' : 'Login' }}
            </button>
          </form>
          <div class="login-footer">
            <p>Don't have an account? <router-link to="/register">Register here</router-link></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '../stores/auth.js'
import { mapActions } from 'pinia'

export default {
  name: 'LoginPage',
  data() {
    return {
      username: '',
      password: '',
      message: '',
      messageClass: '',
      isLoading: false
    }
  },
  methods: {
    ...mapActions(useAuthStore, ['login']), // 👈 map login() from auth store

    async loginHandler() {
      this.isLoading = true
      this.message = ''
      console.log('Attempting login with:', this.username)

      const credentials = {
        username: this.username,
        password: this.password
      }

      const result = await this.login(credentials) // 👈 use auth store login()

      if (result.success) {
        this.message = 'Login successful! Redirecting...'
        this.messageClass = 'message message-success'

        const userRole = JSON.parse(localStorage.getItem('user'))?.role
        console.log('Logged in user role:', userRole)

        setTimeout(() => {
          if (userRole === 'admin') {
            this.$router.push('/admin-dashboard')
          } else {
            this.$router.push('/student-dashboard')
          }
        }, 1000)
      } else {
        this.message = result.message || 'Login failed'
        this.messageClass = 'message message-error'
      }

      this.isLoading = false
    }
  }
}
</script>


<style scoped>
.login-page {
  min-height: 100vh;
}

.login-container {
  padding: 100px 0;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
}

.login-card {
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

.login-card h2 {
  text-align: center;
  margin-bottom: 2rem;
  color: var(--coffee-pot-dark);
}

.btn {
  width: 100%;
}

.login-footer {
  text-align: center;
  margin-top: 2rem;
}

.login-footer p {
  color: var(--coffee-pot);
}

.login-footer a {
  color: var(--coffee-pot-dark);
  text-decoration: none;
  font-weight: bold;
}

.login-footer a:hover {
  text-decoration: underline;
}
</style>