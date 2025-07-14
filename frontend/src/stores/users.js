import { defineStore } from 'pinia'
import axios from 'axios'
import { useAuthStore } from './auth'

const API_BASE_URL = 'http://localhost:8000/api'

export const useUserStore = defineStore('users', {
  state: () => ({
    users: [],
    loading: false,
    error: null
  }),

  actions: {
    async fetchUsers() {
      const authStore = useAuthStore()
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.get(`${API_BASE_URL}/users.php`, {
          headers: {
            'Authorization': `Bearer ${authStore.token}`
          }
        })
        
        if (response.data.success) {
          this.users = response.data.data
        } else {
          this.error = response.data.message
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch users'
      } finally {
        this.loading = false
      }
    },

    async createUser(userData) {
      const authStore = useAuthStore()
      try {
        const response = await axios.post(`${API_BASE_URL}/users.php`, userData, {
          headers: {
            'Authorization': `Bearer ${authStore.token}`,
            'Content-Type': 'application/json'
          }
        })
        
        if (response.data.success) {
          await this.fetchUsers()
          return { success: true }
        }
        
        return { success: false, message: response.data.message }
      } catch (error) {
        return { 
          success: false, 
          message: error.response?.data?.message || 'Failed to create user' 
        }
      }
    },

    async updateUser(userId, userData) {
      const authStore = useAuthStore()
      try {
        const response = await axios.put(`${API_BASE_URL}/users.php/${userId}`, userData, {
          headers: {
            'Authorization': `Bearer ${authStore.token}`,
            'Content-Type': 'application/json'
          }
        })
        
        if (response.data.success) {
          await this.fetchUsers()
          return { success: true }
        }
        
        return { success: false, message: response.data.message }
      } catch (error) {
        return { 
          success: false, 
          message: error.response?.data?.message || 'Failed to update user' 
        }
      }
    },

    async deleteUser(userId) {
      const authStore = useAuthStore();
      try {
        const response = await axios.delete(`${API_BASE_URL}/users.php/${userId}`, {
          headers: {
            'Authorization': `Bearer ${authStore.token}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (response.data.success) {
          await this.fetchUsers(); // Refresh the user list
          return { success: true, message: response.data.message };
        }
        return { success: false, message: response.data.message };
      } catch (error) {
        console.error('Delete user error:', error.response);
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to delete user'
        };
      }
    }
  }
})