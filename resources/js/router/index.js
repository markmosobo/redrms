import { createRouter, createWebHistory } from 'vue-router';

import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import Home from '../views/Home.vue';
import Landlords from '../views/Landlords.vue';
import Properties from '../views/Properties.vue';
import Tenancies from '../views/Tenancies.vue';
import Deposits from '../views/Deposits.vue';
import Inspections from '../views/Inspections.vue';
import Deductions from '../views/Deductions.vue';
import Refunds from '../views/Refunds.vue';

const routes = [
  // Public routes
  { path: '/', name: 'index', component: Login },
  { path: '/login', name: 'login', component: Login },
  { path: '/register', name: 'register', component: Register },
  { path: '/home', name: 'home', component: Home, meta: { requiresAuth: true } },
  { path: '/landlords', name: 'landlords', component: Landlords, meta: { requiresAuth: true } },
  { path: '/properties', name: 'properties', component: Properties, meta: { requiresAuth: true } },
  { path: '/tenancies', name: 'tenancies', component: Tenancies, meta: { requiresAuth: true } },
  { path: '/deposits', name: 'deposits', component: Deposits, meta: { requiresAuth: true } },
  { path: '/inspections', name: 'inspections', component: Inspections, meta: { requiresAuth: true } },
  { path: '/deductions', name: 'deductions', component: Deductions, meta: { requiresAuth: true } },
  { path: '/refunds', name: 'refunds', component: Refunds, meta: { requiresAuth: true } },
];

const router = createRouter({
  history: createWebHistory('/'),
  routes,
});

// 🔐 Global Auth Guard
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');

  if (to.meta.requiresAuth && !token) {
    next({ path: '/login', replace: true });
  } else {
    next();
  }
});

export default router;