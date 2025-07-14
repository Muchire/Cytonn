<template>
  <div class="student-dashboard">
    <nav class="modern-nav">
      <div class="nav-container">
        <router-link to="/dashboard" class="logo">
          <span>TaskFlow</span>
        </router-link>
        <div class="nav-right">
          <router-link to="/profile" class="nav-profile">
            <span class="welcome">Welcome, {{ user.full_name }}</span>
            <div class="avatar-placeholder"></div>
          </router-link>
          <button @click="logout" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
          </button>
        </div>
      </div>
    </nav>
    
    <div class="container">
      <div v-if="message" :class="messageClass">
        {{ message }}
      </div>
      
      <!-- Dashboard Stats -->
      <div class="stats-section mb-4">
        <div class="row">
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon">📋</div>
              <div class="stat-content">
                <h3>{{ totalTasks }}</h3>
                <p>Total Tasks</p>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon">⏳</div>
              <div class="stat-content">
                <h3>{{ pendingTasks }}</h3>
                <p>Pending Tasks</p>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon">✅</div>
              <div class="stat-content">
                <h3>{{ completedTasks }}</h3>
                <p>Completed Tasks</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Tasks Section -->
      <div class="card">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2>My Tasks</h2>
          <div class="filters">
            <select v-model="statusFilter" class="form-control" style="width: auto; display: inline-block;">
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>
        
        <div v-if="isLoading" class="text-center">Loading tasks...</div>
        
        <div v-else-if="filteredTasks.length === 0" class="text-center">
          <p>No tasks found.</p>
        </div>
        
        <div v-else class="tasks-grid">
          <div v-for="task in filteredTasks" :key="task.id" class="task-card">
            <div class="task-header">
              <h3>{{ task.title }}</h3>
              <span :class="'status-badge status-' + task.status">{{ task.status.replace('_', ' ') }}</span>
            </div>
            <p class="task-description">{{ task.description }}</p>
            <div class="task-meta">
              <p><strong>Deadline:</strong> {{ formatDate(task.deadline) }}</p>
            </div>
            <div class="task-actions">
              <label class="mr-2"><strong>Status:</strong></label>
              <select
                v-model="task.status"
                @change="updateTaskStatus(task.id, task.status)"
                class="form-control"
                style="width: auto; display: inline-block;"
              >
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
              </select>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useTaskStore } from '@/stores/tasks.js'

export default {
  name: 'StudentDashboard',
  data() {
    return {
      user: {
        full_name: ''
      },
      tasks: [],
      isLoading: false,
      statusFilter: '',
      message: '',
      messageClass: ''
    }
  },
  computed: {
    filteredTasks() {
      if (!this.statusFilter) {
        return this.tasks
      }
      return this.tasks.filter(task => task.status === this.statusFilter)
    },
    totalTasks() {
      return this.tasks.length
    },
    pendingTasks() {
      return this.tasks.filter(task => task.status === 'pending').length
    },
    completedTasks() {
      return this.tasks.filter(task => task.status === 'completed').length
    }
  },
  mounted() {
    this.loadUserData()
    this.loadTasks()
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user')
      if (userData) {
        this.user = JSON.parse(userData)
      }
    },
    async loadTasks() {
      this.isLoading = true
      const taskStore = useTaskStore()
      try {
        await taskStore.fetchTasks() // ✅ No need for userId, token is handled
        this.tasks = taskStore.tasks
      } catch (error) {
        console.error('Error loading tasks:', error)
        this.showMessage('Error loading tasks', 'error')
      } finally {
        this.isLoading = false
      }
    },

    async updateTaskStatus(taskId, newStatus) {
      try {
        const token = localStorage.getItem('token')
        const response = await fetch(`http://localhost:8000/api/tasks.php?task_id=${taskId}`, {

          method: 'PUT',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            status: newStatus,
            status_only: true // ✅ required by your backend logic
          })
        })
        
        if (response.ok) {
          // Update local task status
          const taskIndex = this.tasks.findIndex(task => task.id === taskId)
          if (taskIndex !== -1) {
            this.tasks[taskIndex].status = newStatus
          }
          this.showMessage('Task status updated successfully', 'success')
        } else {
          this.showMessage('Failed to update task status', 'error')
        }
      } catch (error) {
        console.error('Error updating task status:', error)
        this.showMessage('Error updating task status', 'error')
      }
    },

    formatDate(dateString) {
      if (!dateString) return 'No deadline'
      const date = new Date(dateString)
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    },
    showMessage(text, type) {
      this.message = text
      this.messageClass = type === 'error' ? 'alert alert-danger' : 'alert alert-success'
      setTimeout(() => {
        this.message = ''
        this.messageClass = ''
      }, 5000)
    },
    logout() {
      localStorage.removeItem('token')
      localStorage.removeItem('userRole')
      localStorage.removeItem('user')
      this.$router.push('/login')
    }
  }
}
</script>
<style scoped>
/* Enhanced Color Variables */
:root {
  --sand-dollar: #f5ece4;  /* Lighter, more white */
  --tan: #d0b49f;
  --brown: #a47551;
  --carafe: #523a28;
  --dark-carafe: #3a2a1c;  /* Darker shade for contrast */
  --white: #ffffff;
  --light-tan: #e8d9cc;    /* New light accent */
  --text-dark: #2c241b;    /* Dark text for readability */
  --text-light: #f8f5f2;   /* Light text for dark backgrounds */
}

/* Modern Navbar Styles */
.modern-nav {
  background-color: var(--carafe);
  padding: 1rem 2rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.nav-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1400px;
  margin: 0 auto;
}

.logo {
  color: var(--white);
  font-size: 1.8rem;
  font-weight: 700;
  text-decoration: none;
  letter-spacing: 1.5px;
  font-family: 'Segoe UI', sans-serif;
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.nav-profile {
  display: flex;
  align-items: center;
  gap: 1rem;
  text-decoration: none;
  transition: all 0.3s ease;
  padding: 0.5rem 1rem;
  border-radius: 50px;
  background-color: rgba(255,255,255,0.1);
}

.nav-profile:hover {
  background-color: rgba(255,255,255,0.2);
}

.welcome {
  color: var(--light-tan);
  font-weight: 500;
  font-size: 1rem;
  letter-spacing: 0.5px;
}

.avatar-placeholder {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--tan) 0%, var(--brown) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--white);
  font-weight: 600;
  font-size: 1.1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.logout-btn {
  background: var(--brown);
  border: none;
  color: var(--white);
  width: 42px;
  height: 42px;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.logout-btn:hover {
  background: var(--dark-carafe);
  transform: translateY(-2px);
}

/* Dashboard Styles */
.student-dashboard {
  min-height: 100vh;
  background-color: var(--sand-dollar);
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2.5rem;
}

/* Enhanced Card Styles */
.card {
  background: var(--white);
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
  border: none;
  margin-bottom: 2rem;
}

.stat-card {
  background: var(--white);
  border-radius: 10px;
  padding: 1.8rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  border: none;
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-icon {
  font-size: 2.2rem;
  color: var(--carafe);
  margin-bottom: 1rem;
}

.stat-content h3 {
  color: var(--dark-carafe);
  font-size: 2.2rem;
  margin-bottom: 0.5rem;
}

.stat-content p {
  color: var(--brown);
  font-weight: 500;
}

/* Enhanced Task Card Styles */
.tasks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.task-card {
  border: none;
  border-radius: 10px;
  padding: 1.5rem;
  background: var(--white);
  transition: all 0.3s ease;
  box-shadow: 0 4px 8px rgba(0,0,0,0.05);
  border-left: 4px solid var(--tan);
}

.task-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.task-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.task-header h3 {
  color: var(--dark-carafe);
  font-size: 1.2rem;
  margin: 0;
  flex: 1;
}

.task-description {
  color: var(--text-dark);
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.task-meta {
  font-size: 0.9rem;
  color: var(--brown);
  margin-bottom: 1.5rem;
}

.task-meta strong {
  color: var(--carafe);
}

/* Enhanced Status Badges */
.status-badge {
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.status-pending {
  background-color: var(--tan);
  color: var(--dark-carafe);
}

.status-in_progress {
  background-color: var(--brown);
  color: var(--white);
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.status-completed {
  background-color: var(--carafe);
  color: var(--white);
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Enhanced Buttons */
.btn {
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
  letter-spacing: 0.5px;
  border: none;
}

.btn-secondary {
  background-color: var(--brown);
  color: var(--white);
}

.btn-secondary:hover {
  background-color: var(--dark-carafe);
  transform: translateY(-2px);
}

/* Form Elements */
.form-control {
  padding: 0.7rem 1rem;
  border: 1px solid var(--light-tan);
  border-radius: 8px;
  background-color: var(--white);
  transition: all 0.3s ease;
}

.form-control:focus {
  border-color: var(--brown);
  box-shadow: 0 0 0 3px rgba(164, 117, 81, 0.2);
}

/* Profile Page Specific */
.profile-avatar {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--tan) 0%, var(--brown) 100%);
  color: var(--white);
  font-size: 2.5rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Responsive Design */
@media (max-width: 992px) {
  .container {
    padding: 1.5rem;
  }
  
  .tasks-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  }
}

@media (max-width: 768px) {
  .modern-nav {
    padding: 0.8rem 1.5rem;
  }
  
  .nav-profile {
    padding: 0.3rem 0.6rem;
  }
  
  .welcome {
    font-size: 0.9rem;
  }
  
  .avatar-placeholder {
    width: 36px;
    height: 36px;
    font-size: 0.9rem;
  }
  
  .logout-btn {
    width: 36px;
    height: 36px;
  }
}

@media (max-width: 576px) {
  .nav-right {
    gap: 1rem;
  }
  
  .welcome {
    display: none;
  }
  
  .profile-avatar {
    width: 90px;
    height: 90px;
    font-size: 2rem;
  }
}
</style>