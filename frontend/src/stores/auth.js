import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    isAuthenticated: false
  }),

  actions: {
    isTokenValid(token) {
      if (!token) return false
      
      try {
        const payload = JSON.parse(atob(token.split('.')[1]))
        const expiry = payload.exp * 1000
        return Date.now() < expiry
      } catch {
        return false
      }
    },

    async login(credentials) {
      try {
        const response = await axios.post('http://localhost:8000/api/auth.php', credentials)
        
        if (response.data.success) {
          this.user = response.data.data.user
          this.token = response.data.data.token
          this.isAuthenticated = true
          
          localStorage.setItem('token', this.token)
          localStorage.setItem('user', JSON.stringify(this.user))
          localStorage.setItem('userRole', this.user.role)
          
          axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
          
          return { success: true }
        }
        
        return { success: false, message: response.data.message }
      } catch (error) {
        return { 
          success: false, 
          message: error.response?.data?.message || 'Login failed' 
        }
      }
    },

    logout() {
      this.user = null
      this.token = null
      this.isAuthenticated = false
      
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      localStorage.removeItem('userRole')
      
      delete axios.defaults.headers.common['Authorization']
    },

    initializeAuth() {
      const token = localStorage.getItem('token')
      const user = localStorage.getItem('user')
      
      if (token && user) {
        this.token = token
        this.user = JSON.parse(user)
        this.isAuthenticated = true
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
      }
    }
  }
})