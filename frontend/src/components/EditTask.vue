<template>
  <div class="task-form">
    <h2>Edit Task</h2>

    <!-- Show loading or message if task is not ready -->
    <div v-if="!task && !message" class="loading">
      <i class="fas fa-spinner fa-spin"></i> Loading task...
    </div>

    <form v-else-if="task" @submit.prevent="handleUpdate">
      <div class="form-group">
        <label>Title</label>
        <input v-model="task.title" required class="form-control" />
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea v-model="task.description" required class="form-control"></textarea>
      </div>

      <div class="form-group">
        <label>Assigned To</label>
        <select v-model="task.assigned_to" class="form-control" required>
          <option disabled value="">-- Select Student --</option>
          <option v-for="student in students" :key="student.id" :value="student.id">
            {{ student.full_name || student.username }}
          </option>
        </select>
      </div>

      <div class="form-group">
        <label>Deadline</label>
        <input v-model="task.deadline" type="date" required class="form-control" />
      </div>

      <div class="form-group">
        <label>Status</label>
        <select v-model="task.status" class="form-control" required>
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
        </select>
      </div>

      <button type="submit" class="btn btn-success">Update Task</button>
    </form>

    <p v-if="message" class="mt-3 alert alert-info">{{ message }}</p>
  </div>
</template>

<script>
import { useTaskStore } from '@/stores/tasks.js'
import { useUserStore } from '@/stores/users.js'

export default {
  name: 'EditTask',
  data() {
    return {
      task: null,
      students: [],
      message: ''
    }
  },
  async created() {
    const taskStore = useTaskStore()
    const userStore = useUserStore()
    const taskId = this.$route.params.id

    // Fetch the task
    const result = await taskStore.getTaskById(taskId)
    if (result.success) {
      this.task = result.data
    } else {
      this.message = result.message || 'Failed to load task.'
    }

    // Fetch all users and filter for students
    await userStore.fetchUsers()
    this.students = userStore.users.filter(user => user.role === 'student')
  },
  methods: {
    async handleUpdate() {
      const taskStore = useTaskStore()
      const result = await taskStore.updateTask(this.task.id, this.task)

      if (result.success) {
        this.$router.push('/admin-dashboard')
      } else {
        this.message = result.message || 'Failed to update task.'
      }
    }
  }
}
</script>

<style scoped>
.task-form {
  max-width: 600px;
  margin: auto;
  padding: 20px;
}
.form-group {
  margin-bottom: 15px;
}
</style>
