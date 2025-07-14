<template>
  <div class="landing-page">
    <nav class="navbar">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <h1 class="navbar-brand">Task Manager</h1>
          <div class="navbar-nav">
            <router-link to="/login" class="btn btn-primary">Login</router-link>
            <router-link to="/register" class="btn btn-secondary">Register</router-link>
          </div>
        </div>
      </div>
    </nav>
    
    <div class="hero-section">
      <div class="container">
        <div class="hero-content">
          <h1>Welcome to Task Manager</h1>
          <p>Streamline your workflow with our intuitive task management system</p>
          <div class="hero-buttons">
            <router-link to="/login" class="btn btn-primary btn-lg">Get Started</router-link>
            <router-link to="/register" class="btn btn-secondary btn-lg">Sign Up</router-link>
          </div>
        </div>
      </div>
    </div>
    
    <div class="features-section">
      <div class="container">
        <h2>Features</h2>
        <div class="row">
          <div class="col-md-4">
            <div class="feature-card">
              <div class="feature-icon">📋</div>
              <h3>Task Management</h3>
              <p>Create, assign, and track tasks efficiently</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="feature-card">
              <div class="feature-icon">👥</div>
              <h3>User Management</h3>
              <p>Manage users and their roles seamlessly</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="feature-card">
              <div class="feature-icon">📊</div>
              <h3>Progress Tracking</h3>
              <p>Monitor task progress and completion rates</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LandingPage'
}
</script>

<style scoped>
.landing-page {
  min-height: 100vh;
}

.hero-section {
  background: linear-gradient(135deg, var(--rosewater), var(--dusty-rose));
  padding: 100px 0;
  text-align: center;
}

.hero-content h1 {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: var(--coffee-pot-dark);
}

.hero-content p {
  font-size: 1.2rem;
  margin-bottom: 2rem;
  color: var(--coffee-pot);
}

.hero-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-lg {
  padding: 15px 30px;
  font-size: 1.1rem;
}

.features-section {
  padding: 80px 0;
}

.features-section h2 {
  text-align: center;
  margin-bottom: 3rem;
  color: var(--coffee-pot-dark);
}

.feature-card {
  text-align: center;
  padding: 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.feature-card h3 {
  color: var(--coffee-pot-dark);
  margin-bottom: 1rem;
}

.feature-card p {
  color: var(--coffee-pot);
}

@media (max-width: 768px) {
  .hero-content h1 {
    font-size: 2rem;
  }
  
  .hero-buttons {
    flex-direction: column;
    align-items: center;
  }
  
  .btn-lg {
    width: 200px;
  }
}
</style>