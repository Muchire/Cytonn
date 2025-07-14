import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  { path: '/', component: () => import('@/components/LandingPage.vue') },
  { path: '/login', component: () => import('@/components/LoginPage.vue') },
  { path: '/register', component: () => import('@/components/StudentRegistration.vue') },
  { 
    path: '/student-dashboard', 
    component: () => import('@/components/StudentDashboard.vue'),
    meta: { requiresAuth: true, role: 'student' } 
  },
  { 
    path: '/admin-dashboard', 
    component: () => import('@/components/AdminDashboard.vue'),
    meta: { requiresAuth: true, role: 'admin' } 
  },
  { 
    path: '/tasks/create', 
    component: () => import('@/components/CreateTask.vue'),
    meta: { requiresAuth: true, role: 'admin' } 
  },
  { 
    path: '/tasks/:id/edit', 
    component: () => import('@/components/EditTask.vue'),
    props: true,
    meta: { requiresAuth: true, role: 'admin' } 
  },
  { 
    path: '/profile', 
    component: () => import('@/components/ProfilePage.vue'),
    meta: { requiresAuth: true } 
  },
  { path: '/:pathMatch(.*)*', redirect: '/' }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  const token = localStorage.getItem('token')
  const userRole = localStorage.getItem('userRole')

  if (to.meta.requiresAuth) {
    if (!token || !authStore.isTokenValid(token)) {
      authStore.logout()
      return next('/login')
    }

    if (to.meta.role && to.meta.role !== userRole) {
      if (to.path === '/profile') {
        next()
      } else {
        next(userRole === 'admin' ? '/admin-dashboard' : '/student-dashboard')
      }
    } else {
      next()
    }
  } else {
    next()
  }
})

export default router