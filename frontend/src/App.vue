<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>

<style>
:root {
  --rosewater: #e8b4b8;
  --dusty-rose: #eed6d3;
  --coffee-pot: #a49393;
  --coffee-pot-dark: #67595e;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: var(--dusty-rose);
  color: var(--coffee-pot-dark);
  line-height: 1.6;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.d-flex {
  display: flex;
}

.justify-content-between {
  justify-content: space-between;
}

.align-items-center {
  align-items: center;
}

.text-center {
  text-align: center;
}

.mb-3 {
  margin-bottom: 1rem;
}

.mb-4 {
  margin-bottom: 1.5rem;
}

.row {
  display: flex;
  flex-wrap: wrap;
  margin: -0.5rem;
}

.col-md-3 {
  flex: 0 0 25%;
  max-width: 25%;
  padding: 0.5rem;
}

.col-md-4 {
  flex: 0 0 33.333333%;
  max-width: 33.333333%;
  padding: 0.5rem;
}

@media (max-width: 768px) {
  .col-md-3,
  .col-md-4 {
    flex: 0 0 100%;
    max-width: 100%;
  }
}

.card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.card h2 {
  color: var(--coffee-pot-dark);
  margin-bottom: 1rem;
}

.navbar {
  background: var(--coffee-pot-dark);
  color: white;
  padding: 1rem 0;
  margin-bottom: 2rem;
}

.navbar-brand {
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
  text-decoration: none;
}

.navbar-nav {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.nav-item {
  color: white;
}

.nav-link {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  transition: background-color 0.3s ease;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.1);
}

.btn {
  display: inline-block;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  text-decoration: none;
  text-align: center;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.btn-primary {
  background: var(--coffee-pot);
  color: white;
}

.btn-primary:hover {
  background: var(--coffee-pot-dark);
}

.btn-secondary {
  background: var(--dusty-rose);
  color: var(--coffee-pot-dark);
  border: 2px solid var(--coffee-pot);
}

.btn-secondary:hover {
  background: var(--coffee-pot);
  color: white;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-danger:hover {
  background: #c82333;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.status-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: bold;
  text-transform: uppercase;
}

.status-pending {
  background: #ffeaa7;
  color: #d63031;
}

.status-in_progress {
  background: #81ecec;
  color: #00b894;
}

.status-completed {
  background: #55a3ff;
  color: white;
}

.status-cancelled {
  background: #fd79a8;
  color: #e84393;
}

.message {
  padding: 1rem;
  border-radius: 6px;
  margin-bottom: 1rem;
}

.message-success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.message-error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--coffee-pot-dark);
  font-weight: bold;
}

.form-control {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid var(--coffee-pot);
  border-radius: 6px;
  font-size: 1rem;
}

.form-control:focus {
  outline: none;
  border-color: var(--coffee-pot-dark);
}

textarea.form-control {
  resize: vertical;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  max-height: 80vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.modal-header h3 {
  margin: 0;
  color: var(--coffee-pot-dark);
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--coffee-pot);
}

.close-btn:hover {
  color: var(--coffee-pot-dark);
}

.table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}

.table th,
.table td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid var(--coffee-pot);
}

.table th {
  background: var(--dusty-rose);
  color: var(--coffee-pot-dark);
  font-weight: bold;
}

.table tbody tr:hover {
  background: var(--rosewater);
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
  margin-right: 0.5rem;
}
</style>