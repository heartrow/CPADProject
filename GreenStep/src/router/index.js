import { createRouter, createWebHistory } from 'vue-router'
import DashboardView  from '../../views/DashboardView.vue'
import LoginView from '../../views/LoginView.vue'
import ActivityView from '../../views/ActivityView.vue'
import ChallengesView from '../../views/ChallengesView.vue'
import BadgesView from '../../views/BadgesView.vue'
import ProfileView from '../../views/ProfileView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'login', component: LoginView},
    { path: '/dashboard', name: 'dashboard', component: DashboardView},
    { path: '/activity', name: 'activity', component: ActivityView},
    { path: '/challenges', name: 'challenges', component: ChallengesView},
    { path: '/badges', name: 'badges', component: BadgesView},
    { path: '/profile', name: 'profile', component: ProfileView},
  ],
})

export default router
