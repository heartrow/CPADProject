import App from '@/App.vue';
import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '../stores/auth';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', 
      name: 'login', 
      component: () => import('../../views/LoginView.vue'),
    },
    { path: '/dashboard', 
      name: 'dashboard', 
      component: () => import('../../views/DashboardView.vue'),
      meta: { requiresAuth: true }
    },
    { path: '/activity', 
      name: 'activity', 
      component: () => import('../../views/ActivityView.vue'),
      meta: { requiresAuth: true }
    },
    { path: '/challenges', 
      name: 'challenges', 
      component: () => import('../../views/ChallengesView.vue'),
      meta: { requiresAuth: true }
    },
    { path: '/profile', 
      name: 'profile', 
      component: () => import('../../views/ProfileView.vue'), 
      meta: { requiresAuth: true }
    }, 
  ],
})

router.beforeEach((to) => {
  const auth = useAuth();
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } };
  }
});

export default router
