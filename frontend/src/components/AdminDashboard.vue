<template>
  <div class="admin-dashboard">
    <div class="dashboard-header">
      <h1>Admin Dashboard</h1>
      <div class="header-actions">
        <button @click="showTaskModal(null)" class="btn btn-primary">
          <i class="fas fa-plus"></i> Create Task
        </button>
        <button @click="showUserModal(null)" class="btn btn-secondary">
          <i class="fas fa-user-plus"></i> Add User
        </button>
      </div>
    </div>

    <div class="stats-grid">
      <!-- Keep your existing stat cards -->
      <div class="stat-card total">
        <!-- ... -->
      </div>
      <!-- Other stat cards ... -->
    </div>

    <div class="dashboard-tabs">
      <div class="tabs-header">
        <button 
          @click="activeTab = 'tasks'" 
          :class="{ active: activeTab === 'tasks' }"
          class="tab-btn"
        >
          <i class="fas fa-tasks"></i> Tasks
        </button>
        <button 
          @click="activeTab = 'users'" 
          :class="{ active: activeTab === 'users' }"
          class="tab-btn"
        >
          <i class="fas fa-users"></i> Users
        </button>
      </div>

      <div class="tab-content">
        <!-- Tasks Tab -->
        <div v-if="activeTab === 'tasks'" class="tasks-tab">
          <div class="table-controls">
            <div class="search-filter">
              <input 
                v-model="taskSearch" 
                placeholder="Search tasks..." 
                class="search-input"
              >
              <select v-model="taskStatusFilter" class="filter-select">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
              </select>
            </div>
            <div class="pagination-controls">
              <button @click="currentTaskPage--" class="pagination-btn">
                ◀
              </button>

              <span>Page {{ currentTaskPage }} of {{ totalTaskPages }}</span>
              <button @click="currentTaskPage++" class="pagination-btn">
                ▶
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <table class="tasks-table">
              <thead>
                <tr>
                  <th @click="sortTasks('title')">
                    Title <i :class="sortIcon('title')"></i>
                  </th>
                  <th @click="sortTasks('full_name')">
                    Assigned To <i :class="sortIcon('full_name')"></i>
                  </th>
                  <th @click="sortTasks('deadline')">
                    Deadline <i :class="sortIcon('deadline')"></i>
                  </th>
                  <th @click="sortTasks('status')">
                    Status <i :class="sortIcon('status')"></i>
                  </th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="task in paginatedTasks" :key="task.id">
                  <td>{{ task.title }}</td>
                  <td>{{ task.full_name || 'Unassigned' }}</td>
                  <td>{{ formatDate(task.deadline) }}</td>
                  <td>
                    <span :class="'status-badge status-' + task.status">
                      {{ formatStatus(task.status) }}
                    </span>
                  </td>
                  <td class="actions-cell">
                    <button @click="showTaskModal(task)" class="btn-icon">
                      ✏️
                    </button>
                    <button @click="confirmDeleteTask(task.id)" class="btn-icon btn-danger">
                      🗑️
                    </button>

                  </td>
                </tr>
                <tr v-if="filteredTasks.length === 0">
                  <td colspan="5" class="no-results">No tasks found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Users Tab -->
        <div v-if="activeTab === 'users'" class="users-tab">
          <div class="table-controls">
            <div class="search-filter">
              <input 
                v-model="userSearch" 
                placeholder="Search users..." 
                class="search-input"
              >
              <select v-model="userRoleFilter" class="filter-select">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="student">Student</option>
              </select>
            </div>
          </div>

          <div class="table-responsive">
            <table class="users-table">
              <thead>
                <tr>
                  <th @click="sortUsers('full_name')">
                    Name <i :class="sortIcon('full_name')"></i>
                  </th>
                  <th @click="sortUsers('username')">
                    Username <i :class="sortIcon('username')"></i>
                  </th>
                  <th @click="sortUsers('role')">
                    Role <i :class="sortIcon('role')"></i>
                  </th>
                  <th>Tasks Assigned</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in filteredUsers" :key="user.id">
                  <td>{{ user.full_name }}</td>
                  <td>@{{ user.username }}</td>
                  <td>
                    <span :class="'role-badge role-' + user.role">
                      {{ formatRole(user.role) }}
                    </span>
                  </td>
                  <td>{{ getUserTaskCount(user.id) }}</td>
                  <td class="actions-cell">
                    <button @click="showUserModal(user)" class="btn-icon">
                      ✏️
                    </button>

                    <button 
                      @click="confirmDeleteUser(user.id)" 
                      class="btn-icon btn-danger"
                      :disabled="user.id === currentUserId"
                    >
                      🗑️
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredUsers.length === 0">
                  <td colspan="5" class="no-results">No users found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Task Modal -->
    <div v-if="showModal === 'task'" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ editingTask ? 'Edit Task' : 'Create Task' }}</h3>
          <button @click="closeModal" class="modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="handleTaskSubmit">
            <div class="form-group">
              <label>Title</label>
              <input v-model="taskForm.title" required class="form-control" />
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea v-model="taskForm.description" required class="form-control"></textarea>
            </div>
            <div class="form-group">
              <label>Assigned To</label>
              <select v-model="taskForm.assigned_to" class="form-control" required>
                <option value="">Select User</option>
                <option 
                  v-for="user in studentUsers" 
                  :key="user.id" 
                  :value="user.id"
                >
                  {{ user.full_name || user.username }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Deadline</label>
              <input v-model="taskForm.deadline" type="date" required class="form-control" />
            </div>
            <div class="form-group">
              <label>Status</label>
              <select v-model="taskForm.status" class="form-control" required>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
              </select>
            </div>
            <div class="form-actions">
              <button type="button" @click="closeModal" class="btn btn-secondary">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary">
                {{ editingTask ? 'Update' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- User Modal -->
    <div v-if="showModal === 'user'" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ editingUser ? 'Edit User' : 'Create User' }}</h3>
          <button @click="closeModal" class="modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="handleUserSubmit">
            <div class="form-group">
              <label>Full Name <span class="required">*</span></label>
              <input v-model="userForm.full_name" required class="form-control" />
            </div>
            <div class="form-group">
              <label>Username <span class="required">*</span></label>
              <input v-model="userForm.username" required class="form-control" />
            </div>
            <div class="form-group">
              <label>Email <span class="required">*</span></label>
              <input 
                v-model="userForm.email" 
                type="email" 
                required 
                class="form-control"
                placeholder="user@example.com"
              />
            </div>
            <div class="form-group" v-if="!editingUser">
              <label>Password <span class="required">*</span></label>
              <input 
                v-model="userForm.password" 
                type="password" 
                :required="!editingUser" 
                class="form-control"
              />
            </div>
            <div class="form-group" v-else>
              <label>New Password (leave blank to keep current)</label>
              <input 
                v-model="userForm.password" 
                type="password" 
                class="form-control"
                placeholder="Leave blank to keep current password"
              />
            </div>
            <div class="form-group">
              <label>Role <span class="required">*</span></label>
              <select v-model="userForm.role" class="form-control" required>
                <option value="student">Student</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div class="form-actions">
              <button type="button" @click="closeModal" class="btn btn-secondary">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary">
                {{ editingUser ? 'Update' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Confirmation Modal -->
    <div v-if="confirmationModal.show" class="modal-overlay">
      <div class="modal-content confirmation-modal">
        <div class="modal-header">
          <h3>{{ confirmationModal.title }}</h3>
          <button @click="closeConfirmationModal" class="modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <p>{{ confirmationModal.message }}</p>
          <div v-if="confirmationModal.isProcessing" class="processing-message">
            <i class="fas fa-spinner fa-spin"></i> Processing...
          </div>
          <div v-else class="confirmation-actions">
            <button @click="closeConfirmationModal" class="btn btn-secondary">
              Cancel
            </button>
            <button @click="confirmationModal.onConfirm" class="btn btn-danger">
              Confirm
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Notification -->
    <div v-if="showSuccessNotification" class="notification success">
      <i class="fas fa-check-circle"></i> {{ successMessage }}
      <button @click="showSuccessNotification = false" class="notification-close">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </div>
</template>

<script>
import { onMounted } from 'vue'
import { useTaskStore } from '../stores/tasks.js'
import { useUserStore } from '../stores/users.js'
import { useAuthStore } from '../stores/auth.js'

export default {
  name: 'AdminDashboard',
  setup() {
    const taskStore = useTaskStore()
    const userStore = useUserStore()
    const authStore = useAuthStore()
    
    // Initialize data
    onMounted(async () => {
      await taskStore.fetchTasks()
      await userStore.fetchUsers()
    })

    return { taskStore, userStore, authStore }
  },
  data() {
    return {
      activeTab: 'tasks',
      showModal: null,
      editingTask: null,
      editingUser: null,
      currentTaskPage: 1,
      tasksPerPage: 10,
      taskSearch: '',
      taskStatusFilter: '',
      taskSortField: 'deadline',
      taskSortDirection: 'asc',
      userSearch: '',
      userRoleFilter: '',
      userSortField: 'full_name',
      userSortDirection: 'asc',
      showSuccessNotification: false,
      successMessage: '',
      confirmationModal: {
        show: false,
        title: '',
        message: '',
        isProcessing: false,
        onConfirm: null
      },

      taskForm: {
        title: '',
        description: '',
        assigned_to: '',
        deadline: '',
        status: 'pending'
      },
      userForm: {
        full_name: '',
        username: '',
        email: '',
        password: '',
        role: 'student'
      },

    }
  },
  computed: {
    currentUserId() {
      return this.authStore.user?.id || null
    },
    tasks() {
      return this.taskStore.tasks
    },
    users() {
      return this.userStore.users
    },
    studentUsers() {
      return this.users.filter(user => user.role === 'student')
    },
    filteredTasks() {
      let filtered = this.tasks
      
      // Apply search filter
      if (this.taskSearch) {
        const searchTerm = this.taskSearch.toLowerCase()
        filtered = filtered.filter(task => 
          task.title.toLowerCase().includes(searchTerm) ||
          (task.description && task.description.toLowerCase().includes(searchTerm)) ||
          (task.full_name && task.full_name.toLowerCase().includes(searchTerm))
        )
      }
      
      // Apply status filter
      if (this.taskStatusFilter) {
        filtered = filtered.filter(task => task.status === this.taskStatusFilter)
      }
      
      // Apply sorting
      return this.sortArray(filtered, this.taskSortField, this.taskSortDirection)
    },
    filteredUsers() {
      let filtered = this.users
      
      // Apply search filter
      if (this.userSearch) {
        const searchTerm = this.userSearch.toLowerCase()
        filtered = filtered.filter(user => 
          user.full_name.toLowerCase().includes(searchTerm) ||
          user.username.toLowerCase().includes(searchTerm))
      }
      
      // Apply role filter
      if (this.userRoleFilter) {
        filtered = filtered.filter(user => user.role === this.userRoleFilter)
      }
      
      // Apply sorting
      return this.sortArray(filtered, this.userSortField, this.userSortDirection)
    },
    paginatedTasks() {
      const start = (this.currentTaskPage - 1) * this.tasksPerPage
      const end = start + this.tasksPerPage
      return this.filteredTasks.slice(start, end)
    },
    totalTaskPages() {
      return Math.ceil(this.filteredTasks.length / this.tasksPerPage)
    },
    totalTasks() {
      return this.tasks.length
    },
    pendingTasks() {
      return this.tasks.filter(task => task.status === 'pending').length
    },
    inProgressTasks() {
      return this.tasks.filter(task => task.status === 'in_progress').length
    },
    completedTasks() {
      return this.tasks.filter(task => task.status === 'completed').length
    },
    overdueTasks() {
      const today = new Date().toISOString().split('T')[0]
      return this.tasks.filter(task => 
        task.status !== 'completed' && 
        task.deadline && 
        task.deadline < today
      ).length
    },
    totalUsers() {
      return this.users.length
    }
  },
  watch: {
    totalTaskPages(newVal) {
      if (this.currentTaskPage > newVal && newVal > 0) {
        this.currentTaskPage = newVal
      }
    }
  },
  methods: {
    // Sorting methods
    sortTasks(field) {
      if (this.taskSortField === field) {
        this.taskSortDirection = this.taskSortDirection === 'asc' ? 'desc' : 'asc'
      } else {
        this.taskSortField = field
        this.taskSortDirection = 'asc'
      }
    },
    sortUsers(field) {
      if (this.userSortField === field) {
        this.userSortDirection = this.userSortDirection === 'asc' ? 'desc' : 'asc'
      } else {
        this.userSortField = field
        this.userSortDirection = 'asc'
      }
    },
    sortIcon(field) {
      const isActive = this.activeTab === 'tasks' 
        ? field === this.taskSortField 
        : field === this.userSortField
      
      const direction = this.activeTab === 'tasks' 
        ? this.taskSortDirection 
        : this.userSortDirection
      
      return {
        'fas': true,
        'fa-sort-up': isActive && direction === 'asc',
        'fa-sort-down': isActive && direction === 'desc',
        'fa-sort': !isActive
      }
    },
    sortArray(array, field, direction) {
      return [...array].sort((a, b) => {
        let valueA = a[field]
        let valueB = b[field]
        
        // Handle null/undefined values
        if (valueA == null) valueA = ''
        if (valueB == null) valueB = ''
        
        // Convert to string for comparison
        valueA = String(valueA).toLowerCase()
        valueB = String(valueB).toLowerCase()
        
        if (valueA < valueB) return direction === 'asc' ? -1 : 1
        if (valueA > valueB) return direction === 'asc' ? 1 : -1
        return 0
      })
    },
    
    // Modal methods
    showTaskModal(task) {
      this.editingTask = task
      if (task) {
        this.taskForm = {
          title: task.title,
          description: task.description,
          assigned_to: task.assigned_to,
          deadline: task.deadline.split('T')[0], // Format date for input
          status: task.status
        }
      } else {
        this.resetTaskForm()
      }
      this.showModal = 'task'
    },
    showUserModal(user) {
      this.editingUser = user
      if (user) {
        this.userForm = {
          full_name: user.full_name,
          username: user.username,
          email: user.email,
          password: '',
          role: user.role
        }
      } else {
        this.resetUserForm()
      }
      this.showModal = 'user'
    },
    closeModal() {
      this.showModal = null
      this.editingTask = null
      this.editingUser = null
    },
    resetTaskForm() {
      this.taskForm = {
        title: '',
        description: '',
        assigned_to: '',
        deadline: '',
        status: 'pending'
      }
    },
    resetUserForm() {
      this.userForm = {
        full_name: '',
        username: '',
        email: '',
        password: '',
        role: 'student'
      }
    },
    
    // Form submission handlers
    async handleTaskSubmit() {
      try {
        if (this.editingTask) {
          await this.taskStore.updateTask(this.editingTask.id, this.taskForm)
        } else {
          await this.taskStore.createTask(this.taskForm)
        }
        this.closeModal()
      } catch (error) {
        console.error('Error saving task:', error)
      }
    },
    async handleUserSubmit() {
      try {
        // Validate required fields
        if (!this.userForm.username || !this.userForm.full_name || 
            (!this.editingUser && !this.userForm.password)) {
          alert('Please fill all required fields')
          return
        }

        // Prepare user data with all required fields
        const userData = {
          username: this.userForm.username,
          full_name: this.userForm.full_name,
          email: this.userForm.email || `${this.userForm.username}@example.com`,
          role: this.userForm.role
        }

        if (this.editingUser) {
          // For updates, only include password if it was changed
          if (this.userForm.password) {
            userData.password = this.userForm.password
          }

          const result = await this.userStore.updateUser(this.editingUser.id, userData)
          if (result.success) {
            alert('User updated successfully')
            this.closeModal()
            await this.userStore.fetchUsers()
          } else {
            alert(result.message || 'Failed to update user')
          }
        } else {
          // For new users, password is required
          userData.password = this.userForm.password

          const result = await this.userStore.createUser(userData)
          if (result.success) {
            alert('User created successfully')
            this.closeModal()
            await this.userStore.fetchUsers()
          } else {
            alert(result.message || 'Failed to create user')
          }
        }
      } catch (error) {
        console.error('Error saving user:', error)
        alert(error.response?.data?.message || error.message || 'An error occurred')
      }
    },
        
    // Confirmation dialogs
    confirmDeleteTask(taskId) {
      this.confirmationModal = {
        show: true,
        title: 'Confirm Deletion',
        message: 'Are you sure you want to delete this task? This action cannot be undone.',
        onConfirm: async () => {
          await this.taskStore.deleteTask(taskId)
          this.closeConfirmationModal()
        }
      }
    },
    confirmDeleteUser(userId) {
      this.confirmationModal = {
        show: true,
        title: 'Confirm Deletion',
        message: 'Are you sure you want to delete this user? All their tasks will be unassigned.',
        onConfirm: async () => {
          await this.userStore.deleteUser(userId)
          this.closeConfirmationModal()
        }
      }
    },
    closeConfirmationModal() {
      this.confirmationModal = {
        show: false,
        title: '',
        message: '',
        onConfirm: null
      }
    },
    
    // Utility methods
    formatDate(dateString) {
      if (!dateString) return 'N/A'
      const options = { year: 'numeric', month: 'short', day: 'numeric' }
      return new Date(dateString).toLocaleDateString(undefined, options)
    },
    formatStatus(status) {
      const statusMap = {
        pending: 'Pending',
        in_progress: 'In Progress',
        completed: 'Completed'
      }
      return statusMap[status] || status
    },
    formatRole(role) {
      return role.charAt(0).toUpperCase() + role.slice(1)
    },
    getUserTaskCount(userId) {
      return this.tasks.filter(task => task.assigned_to === userId).length
    }
  },
  
  confirmDeleteTask(taskId) {
    this.confirmationModal = {
      show: true,
      title: 'Confirm Task Deletion',
      message: 'Are you sure you want to delete this task? This action cannot be undone.',
      isProcessing: false,
      onConfirm: async () => {
        this.confirmationModal.isProcessing = true;
        try {
          await this.taskStore.deleteTask(taskId);
          this.showSuccess('Task deleted successfully');
          this.closeConfirmationModal();
        } catch (error) {
          alert(error.message || 'Failed to delete task');
        } finally {
          this.confirmationModal.isProcessing = false;
        }
      }
    }
  },

  confirmDeleteUser(userId) {
    this.confirmationModal = {
      show: true,
      title: 'Confirm User Deletion',
      message: 'Are you sure you want to delete this user? All their tasks will be unassigned.',
      isProcessing: false,
      onConfirm: async () => {
        this.confirmationModal.isProcessing = true;
        try {
          await this.userStore.deleteUser(userId);
          this.showSuccess('User deleted successfully');
          this.closeConfirmationModal();
        } catch (error) {
          alert(error.message || 'Failed to delete user');
        } finally {
          this.confirmationModal.isProcessing = false;
        }
      }
    }
  },

  showSuccess(message) {
    this.successMessage = message;
    this.showSuccessNotification = true;
    setTimeout(() => {
      this.showSuccessNotification = false;
    }, 3000);
  },

  closeConfirmationModal() {
    this.confirmationModal = {
      show: false,
      title: '',
      message: '',
      isProcessing: false,
      onConfirm: null
    }
  }
}
</script>

<style scoped>
/* Base styles */
.admin-dashboard {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 15px;
}

.dashboard-header h1 {
  color: #2c3e50;
  margin: 0;
  font-size: 28px;
  font-weight: 600;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.btn {
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s ease;
}

.btn-primary {
  background: #3498db;
  color: white;
}

.btn-primary:hover {
  background: #2980b9;
  transform: translateY(-1px);
}

.btn-secondary {
  background: #2ecc71;
  color: white;
}

.btn-secondary:hover {
  background: #27ae60;
  transform: translateY(-1px);
}

/* Tabs */
.dashboard-tabs {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  margin-top: 30px;
  overflow: hidden;
}

.tabs-header {
  display: flex;
  border-bottom: 1px solid #eee;
}

.tab-btn {
  padding: 15px 20px;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 15px;
  font-weight: 500;
  color: #7f8c8d;
  position: relative;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s ease;
}

.tab-btn:hover {
  color: #3498db;
}

.tab-btn.active {
  color: #3498db;
  font-weight: 600;
}

.tab-btn.active::after {
  content: '';
  position: absolute;
  bottom: -1px;
  left: 0;
  right: 0;
  height: 3px;
  background: #3498db;
}

.tab-content {
  padding: 20px;
}

/* Table controls */
.table-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  flex-wrap: wrap;
  gap: 15px;
}

.search-filter {
  display: flex;
  gap: 10px;
  align-items: center;
}

.search-input {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  min-width: 250px;
  transition: border 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #3498db;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  background: white;
}
.required {
  color: #e74c3c;
}
.pagination-controls {
  display: flex;
  align-items: center;
  gap: 10px;
}

.pagination-btn {
  width: 32px;
  height: 32px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-btn:hover:not(:disabled) {
  background: #f5f5f5;
}

/* Tables */
.table-responsive {
  overflow-x: auto;
}

.tasks-table, .users-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.tasks-table th, .users-table th {
  padding: 12px 15px;
  text-align: left;
  background: #f8f9fa;
  font-weight: 600;
  color: #2c3e50;
  cursor: pointer;
  user-select: none;
  position: relative;
}

.tasks-table th:hover, .users-table th:hover {
  background: #e9ecef;
}

.tasks-table td, .users-table td {
  padding: 12px 15px;
  border-bottom: 1px solid #eee;
  vertical-align: middle;
}

.tasks-table tr:hover, .users-table tr:hover {
  background: #f8f9fa;
}

.no-results {
  text-align: center;
  padding: 20px;
  color: #7f8c8d;
}

/* Status and role badges */
.status-badge, .role-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  display: inline-block;
}

.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-in_progress {
  background: #d1ecf1;
  color: #0c5460;
}

.status-completed {
  background: #d4edda;
  color: #155724;
}

.role-admin {
  background: #dc3545;
  color: white;
}

.role-student {
  background: #28a745;
  color: white;
}

/* Action buttons */
.actions-cell {
  display: flex;
  gap: 8px;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
  color: #666;
  transition: all 0.2s;
}

.btn-icon:hover {
  background: #e9ecef;
  transform: translateY(-1px);
}

.btn-icon.btn-danger {
  background: #f8d7da;
  color: #721c24;
}

.btn-icon.btn-danger:hover {
  background: #f5c6cb;
}

.btn-icon:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Modals */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  animation: modalFadeIn 0.3s ease;
}

.confirmation-modal {
  max-width: 400px;
}

.modal-header {
  padding: 20px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 18px;
  color: #2c3e50;
}

.modal-close {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #7f8c8d;
  transition: color 0.2s;
}

.modal-close:hover {
  color: #e74c3c;
}

.modal-body {
  padding: 20px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  color: #2c3e50;
}

.form-control {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  transition: border 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #3498db;
}

textarea.form-control {
  min-height: 100px;
  resize: vertical;
}

.form-actions, .confirmation-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

/* Animations */
@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .header-actions {
    width: 100%;
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
    justify-content: center;
  }
  
  .table-controls {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-filter {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-input, .filter-select {
    width: 100%;
  }
  
  .modal-content {
    margin: 10px;
    width: calc(100% - 20px);
  }
}
/* Notification Styles */
.notification {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 15px 20px;
  border-radius: 4px;
  color: white;
  display: flex;
  align-items: center;
  gap: 10px;
  z-index: 1001;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  animation: slideIn 0.3s ease-out;
}

.notification.success {
  background-color: #28a745;
}

.notification-close {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  margin-left: 10px;
}

.processing-message {
  color: #666;
  display: flex;
  align-items: center;
  gap: 10px;
}

.fa-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes slideIn {
  from { transform: translateX(100%); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}
</style>