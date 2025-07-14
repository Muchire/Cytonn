// src/stores/tasks.js
import { defineStore } from 'pinia'
import axios from 'axios'

const API_BASE_URL = 'http://localhost:8000/api'

export const useTaskStore = defineStore('tasks', {
  state: () => ({
    tasks: [],
    loading: false,
    error: null
  }),

  actions: {
    async fetchTasks(userId = null) {
      this.loading = true
      this.error = null

      try {
        const token = localStorage.getItem('token')

        const url = userId
          ? `${API_BASE_URL}/tasks.php?user_id=${userId}` // admin
          : `${API_BASE_URL}/tasks.php` // student or default

        const response = await axios.get(url, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })

        if (response.data.success) {
          this.tasks = response.data.data
        } else {
          this.error = response.data.message
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch tasks'
      } finally {
        this.loading = false
      }
    },


    async createTask(taskData) {
      try {
        const token = localStorage.getItem('token')
        const response = await axios.post(`${API_BASE_URL}/tasks.php`, taskData, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })
        
        if (response.data.success) {
          await this.fetchTasks()
          return { success: true }
        }
        
        return { success: false, message: response.data.message }
      } catch (error) {
        return { 
          success: false, 
          message: error.response?.data?.message || 'Failed to create task' 
        }
      }
    },

    async updateTask(taskId, taskData) {
      try {
        const token = localStorage.getItem('token')

        const response = await axios.put(`http://localhost:8000/api/tasks.php/${taskId}`, taskData, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        })
        if (response.data.success) {
          await this.fetchTasks()
          return { success: true }
        }
        
        return { success: false, message: response.data.message }
      } catch (error) {
        return { 
          success: false, 
          message: error.response?.data?.message || 'Failed to update task' 
        }
      }
    },
    async updateTaskStatus(taskId, newStatus) {
      try {
        const token = localStorage.getItem('token');
        // Change the URL to use query parameter instead of path parameter
        const response = await fetch(`http://localhost:8000/api/tasks.php?task_id=${taskId}`, {
          method: 'PUT',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            status: newStatus,
            status_only: true
          })
        });
        
        if (response.ok) {
          // Update local task status
          const taskIndex = this.tasks.findIndex(task => task.id === taskId);
          if (taskIndex !== -1) {
            this.tasks[taskIndex].status = newStatus;
          }
          this.showMessage('Task status updated successfully', 'success');
          // Refresh tasks after update
          await this.loadTasks();
        } else {
          const errorData = await response.json();
          this.showMessage(errorData.message || 'Failed to update task status', 'error');
        }
      } catch (error) {
        console.error('Error updating task status:', error);
        this.showMessage('Error updating task status', 'error');
      }
    },

    async deleteTask(taskId) {
      try {
        const token = localStorage.getItem('token')

        const response = await axios.delete(`${API_BASE_URL}/tasks.php/${taskId}`, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })

        if (response.data.success) {
          await this.fetchTasks()
          return { success: true }
        }
        
        return { success: false, message: response.data.message }
      } catch (error) {
        return { 
          success: false, 
          message: error.response?.data?.message || 'Failed to delete task' 
        }
      }
    },

    async getTaskById(taskId) {
      const token = localStorage.getItem('token') // 👈 Fetch the token

      try {
        const response = await axios.get(`${API_BASE_URL}/tasks.php?task_id=${taskId}`, {
          headers: {
            Authorization: `Bearer ${token}` // 👈 Include token in header
          }
        })

        if (response.data.success) {
          return { success: true, data: response.data.data }
        }

        return { success: false, message: response.data.message }
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to fetch task'
        }
      }
    }


  }
})