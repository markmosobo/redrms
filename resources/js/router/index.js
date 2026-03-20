import { createRouter, createWebHistory } from 'vue-router';

import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import Home from '../views/Home.vue';
import Landlords from '../views/Landlords.vue';
import Properties from '../views/Properties.vue';

const routes = [
  // Public routes
  { path: '/', name: 'index', component: Login },
  { path: '/login', name: 'login', component: Login },
  { path: '/register', name: 'register', component: Register },
  { path: '/home', name: 'home', component: Home, meta: { requiresAuth: true } },
  { path: '/landlords', name: 'landlords', component: Landlords, meta: { requiresAuth: true } },
  { path: '/properties', name: 'properties', component: Properties, meta: { requiresAuth: true } },
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