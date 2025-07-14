<template>
  <div class="profile-page">
    <nav class="modern-nav">
      <div class="nav-container">
        <router-link to="/dashboard" class="logo">
          <span>TaskFlow</span>
        </router-link>
        <div class="nav-right">
          <router-link to="/profile" class="nav-profile">
            <span class="welcome">Welcome, {{ user.full_name }}</span>
            <div class="avatar-placeholder">
              {{ userInitials }}
            </div>
          </router-link>
          <button @click="logout" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
          </button>
        </div>
      </div>
    </nav>

    <div class="profile-container">
      <div class="profile-card">
        <div class="profile-header">
          <div class="profile-avatar">
            {{ userInitials }}
          </div>
          <h2>{{ user.full_name }}</h2>
          <p class="email">{{ user.email }}</p>
        </div>

        <div class="profile-details">
          <div class="detail-item">
            <label>Role:</label>
            <span>{{ user.role }}</span>
          </div>
          <div class="detail-item">
            <label>Member Since:</label>
            <span>{{ formatDate(user.created_at) }}</span>
          </div>
        </div>

        <div class="profile-actions">
          <button class="btn btn-primary" @click="editProfile">
            Edit Profile
          </button>
          <button class="btn btn-secondary" @click="changePassword">
            Change Password
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProfilePage',
  data() {
    return {
      user: {
        full_name: '',
        email: '',
        role: '',
        created_at: ''
      }
    }
  },
  computed: {
    userInitials() {
      if (!this.user.full_name) return ''
      return this.user.full_name.split(' ').map(n => n[0]).join('').toUpperCase()
    }
  },
  mounted() {
    this.loadUserData()
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user')
      if (userData) {
        this.user = JSON.parse(userData)
      }
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    },
    editProfile() {
      // Implement edit profile functionality
    },
    changePassword() {
      // Implement change password functionality
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
.profile-page {
  min-height: 100vh;
  background-color: var(--sand-dollar);
}

.profile-container {
  max-width: 800px;
  margin: 2rem auto;
  padding: 0 1rem;
}

.profile-card {
  background: var(--white);
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  border: 1px solid var(--tan);
}

.profile-header {
  text-align: center;
  margin-bottom: 2rem;
}

.profile-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background-color: var(--tan);
  color: var(--carafe);
  font-size: 2rem;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
}

.profile-header h2 {
  color: var(--carafe);
  margin-bottom: 0.5rem;
}

.email {
  color: var(--brown);
  margin-bottom: 0;
}

.profile-details {
  margin: 2rem 0;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  padding: 1rem 0;
  border-bottom: 1px solid var(--sand-dollar);
}

.detail-item label {
  font-weight: 600;
  color: var(--carafe);
}

.detail-item span {
  color: var(--brown);
}

.profile-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn-primary {
  background-color: var(--brown);
  color: var(--white);
  border: none;
}

.btn-primary:hover {
  background-color: var(--carafe);
}

/* Use the same color variables as before */
</style>