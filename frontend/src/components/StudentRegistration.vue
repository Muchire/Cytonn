<template>
  <div class="registration-page">
    <nav class="navbar">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <router-link to="/" class="navbar-brand">Task Manager</router-link>
          <div class="navbar-nav">
            <router-link to="/" class="nav-link">Back to Home</router-link>
            <router-link to="/login" class="nav-link">Login</router-link>
          </div>
        </div>
      </div>
    </nav>
    
    <div class="registration-container">
      <div class="container">
        <div class="registration-card">
          <h2>Student Registration</h2>
          <div v-if="message" :class="messageClass">
            {{ message }}
          </div>
          <form @submit.prevent="register">
            <div class="form-group">
              <label for="username">Username</label>
              <input 
                type="text" 
                id="username" 
                v-model="form.username" 
                class="form-control" 
                required
              >
            </div>
            <div class="form-group">
              <label for="full_name">Full Name</label>
              <input 
                type="text" 
                id="full_name" 
                v-model="form.full_name" 
                class="form-control" 
                required
              >
            </div>
            <div class="form-group">
              <label for="email">Email</label>
              <input 
                type="email" 
                id="email" 
                v-model="form.email" 
                class="form-control" 
                required
              >
            </div>
            <div class="form-group">
              <label for="password">Password</label>
              <input 
                type="password" 
                id="password" 
                v-model="form.password" 
                class="form-control" 
                required
              >
            </div>
            <div class="form-group">
              <label for="confirm_password">Confirm Password</label>
              <input 
                type="password" 
                id="confirm_password" 
                v-model="form.confirm_password" 
                class="form-control" 
                required
              >
            </div>
            <button type="submit" class="btn btn-primary" :disabled="isLoading">
              {{ isLoading ? 'Registering...' : 'Register' }}
            </button>
          </form>
          <div class="registration-footer">
            <p>Already have an account? <router-link to="/login">Login here</router-link></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StudentRegistration',
  data() {
    return {
      form: {
        username: '',
        full_name: '',
        email: '',
        password: '',
        confirm_password: ''
      },
      message: '',
      messageClass: '',
      isLoading: false
    }
  },
  methods: {
    async register() {
      // Validate passwords match
      if (this.form.password !== this.form.confirm_password) {
        this.message = 'Passwords do not match'
        this.messageClass = 'message message-error'
        return
      }
      
      this.isLoading = true
      this.message = ''
      
      try {
        const response = await fetch('/api/register.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            username: this.form.username,
            full_name: this.form.full_name,
            email: this.form.email,
            password: this.form.password,
            role: 'student'
          })
        })
        
        const data = await response.json()
        
        if (data.success) {
          this.message = 'Registration successful! Redirecting to login...'
          this.messageClass = 'message message-success'
          
          // Reset form
          this.form = {
            username: '',
            full_name: '',
            email: '',
            password: '',
            confirm_password: ''
          }
          
          // Redirect to login page
          setTimeout(() => {
            this.$router.push('/login')
          }, 2000)
        } else {
          this.message = data.message || 'Registration failed'
          this.messageClass = 'message message-error'
        }
      } catch (error) {
        this.message = 'Network error. Please try again.'
        this.messageClass = 'message message-error'
      } finally {
        this.isLoading = false
      }
    }
  }
}
</script>

<style scoped>
.registration-page {
  min-height: 100vh;
}

.registration-container {
  padding: 50px 0;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
}

.registration-card {
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 450px;
}

.registration-card h2 {
  text-align: center;
  margin-bottom: 2rem;
  color: var(--coffee-pot-dark);
}

.btn {
  width: 100%;
}

.registration-footer {
  text-align: center;
  margin-top: 2rem;
}

.registration-footer p {
  color: var(--coffee-pot);
}

.registration-footer a {
  color: var(--coffee-pot-dark);
  text-decoration: none;
  font-weight: bold;
}

.registration-footer a:hover {
  text-decoration: underline;
}
</style>