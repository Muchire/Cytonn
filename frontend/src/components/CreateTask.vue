<template>
  <div class="task-form">
    <h2>Create New Task</h2>
    <form @submit.prevent="handleCreate">
      <div class="form-group">
        <label>Title</label>
        <input v-model="task.title" required class="form-control" />
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea v-model="task.description" required class="form-control" />
      </div>

      <div class="form-group">
        <label>Assigned To</label>
        <select v-model="task.assigned_to" class="form-control" required>
          <option value="" disabled>Select a student</option>
          <option 
            v-for="user in studentUsers" 
            :key="user.id" 
            :value="user.id"
          >
            {{ getUserDisplayName(user) }}
          </option>
        </select>
        <p v-if="userStore.loading" class="text-muted">Loading users...</p>
        <p v-if="userStore.error" class="text-danger">Error: {{ userStore.error }}</p>
        <p v-if="!userStore.loading && studentUsers.length === 0" class="text-warning">
          No students available
        </p>
      </div>

      <div class="form-group">
        <label>Deadline</label>
        <input v-model="task.deadline" type="date" required class="form-control" />
      </div>

      <button type="submit" class="btn btn-primary">Create Task</button>
    </form>

    <p v-if="message" class="alert">{{ message }}</p>
  </div>
</template>

<script>
import { useTaskStore } from '@/stores/tasks.js'
import { useUserStore } from '@/stores/users.js'
import { computed } from 'vue'

export default {
  name: 'CreateTask',
  data() {
    return {
      task: {
        title: '',
        description: '',
        assigned_to: '',
        deadline: '',
        status: 'pending'
      },
      message: ''
    }
  },
  setup() {
    const userStore = useUserStore()
    const taskStore = useTaskStore()
    
    const studentUsers = computed(() => {
      return userStore.users.filter(user => {
        // Check for student role (adjust based on your API response)
        return user.role === 'student' || 
               user.user_type === 2 || // Example if using numeric types
               user.is_student === true
      })
    })

    return { userStore, taskStore, studentUsers }
  },
  async created() {
    await this.userStore.fetchUsers()
    console.log('Loaded users:', this.userStore.users) // Debug log
  },
  methods: {
    getUserDisplayName(user) {
      // Customize based on your user object structure
      return user.full_name || user.name || user.username || `Student ${user.id}`
    },
    async handleCreate() {
      try {
        const result = await this.taskStore.createTask(this.task)
        
        if (result.success) {
          this.message = 'Task created successfully!'
          this.$router.push('/admin-dashboard')
        } else {
          this.message = result.message || 'Failed to create task'
        }
      } catch (error) {
        this.message = 'An error occurred while creating the task'
        console.error('Task creation error:', error)
      }
    }
  }
}
</script>


<style scoped>
.task-form {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
  font-family: Arial, sans-serif;
}

.form-group {
  margin-bottom: 1rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: bold;
}

.form-control {
  width: 100%;
  padding: 0.5rem;
  font-size: 1rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  box-sizing: border-box;
}

select.form-control {
  height: 38px;
}

option {
  color: #333;
  padding: 8px;
}

.btn-primary {
  background-color: #007bff;
  color: white;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
}

.btn-primary:hover {
  background-color: #0069d9;
}

.text-muted {
  color: #6c757d;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.text-danger {
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.text-warning {
  color: #ffc107;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.alert {
  padding: 0.75rem 1.25rem;
  margin-top: 1rem;
  border-radius: 4px;
}

.alert-success {
  background-color: #d4edda;
  color: #155724;
}

.alert-error {
  background-color: #f8d7da;
  color: #721c24;
}
</style>