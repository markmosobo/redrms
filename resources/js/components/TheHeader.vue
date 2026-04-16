<template>
  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="#" class="logo d-flex align-items-center"></a>

      <i class="bi bi-list toggle-sidebar-btn" @click="handleSidebar"></i>

      <span class="d-none d-lg-block" style="color: darkgreen;">
        <strong v-if="current_user.role === 'admin'">ADMIN PORTAL</strong> 
        <strong v-if="current_user.role === 'landlord'">LANDLORD PORTAL</strong> 
        <strong v-if="current_user.role === 'tenant'">TENANT PORTAL</strong> 
      </span>
    </div>

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <!-- Notifications -->
        <li class="nav-item dropdown pe-3">

          <a
            class="nav-link nav-icon"
            href="#"
            data-bs-toggle="dropdown"
          >
            <i class="bi bi-bell"></i>

            <!-- badge -->
            <span v-if="unreadCount > 0" class="badge bg-danger badge-number">
              {{ unreadCount }}
            </span>
          </a>

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">

            <li class="dropdown-header">
              You have {{ unreadCount }} new notifications
            </li>

            <li><hr class="dropdown-divider"></li>

            <li v-for="n in notifications" :key="n.id">
              <div class="dropdown-item d-flex align-items-start justify-content-between">

                <!-- notification content -->
                <div class="me-2" style="cursor: pointer;">
                  <div class="fw-bold" :class="{ 'text-muted': n.read_at }">
                    {{ n.title }}
                  </div>
                  <small class="text-muted">
                    {{ n.message }}
                  </small>
                </div>

                <!-- action button -->
                <button
                  v-if="!n.read_at"
                  class="btn btn-sm btn-outline-success"
                  @click.stop="markAsRead(n.id)"
                >
                  Mark read
                </button>

                <span v-else class="badge bg-secondary">
                  Read
                </span>

              </div>
            </li>

            <li v-if="notifications.length === 0" class="text-center p-2 text-muted">
              No notifications
            </li>

          </ul>
        </li>

        <!-- Profile -->
        <li class="nav-item dropdown pe-3">
          <a
            class="nav-link nav-profile d-flex align-items-center pe-0"
            href="#"
            data-bs-toggle="dropdown"
          >
            <i class="bi bi-person-fill"></i>
            <span class="d-none d-md-block dropdown-toggle ps-2">
              {{ current_user.full_name }}
            </span>
          </a>

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6>{{ current_user.full_name }}</h6>
              <span v-if="current_user.role === 'admin'">Admin</span>
              <span v-else-if="current_user.role === 'landlord'">Landlord</span>
              <span v-else-if="current_user.role === 'manager'">Manager</span>
              <span v-else-if="current_user.role === 'tenant'">Tenant</span>
              <span v-else-if="current_user.role === 'service_provider'">Service Provider</span>
            </li>

            <li><hr class="dropdown-divider" /></li>

            <li>
              <router-link to="/profile" custom v-slot="{ href, navigate, isActive }">
                <a
                  class="dropdown-item d-flex align-items-center"
                  :href="href"
                  :class="{ active: isActive }"
                  @click="navigate"
                >
                  <i class="bi bi-person"></i>
                  <span>My Profile</span>
                </a>
              </router-link>
            </li>

            <li><hr class="dropdown-divider" /></li>

            <li>
              <a
                href="#"
                class="dropdown-item d-flex align-items-center"
                @click.prevent="logout"
              >
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>
          </ul>
        </li>

      </ul>
    </nav>
  </header>

  <!-- Search Results Modal (unchanged) -->
  <div
    class="modal fade"
    id="searchResultsModal"
    tabindex="-1"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Search Results</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div v-if="searchResults.length">
            <ul>
              <li v-for="(item, index) in searchResults" :key="index">
                <strong>{{ item.table }}</strong>
                <ul>
                  <li v-for="(value, key) in item.result" :key="key">
                    <span v-if="value">{{ key }}: {{ value }}</span>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
          <p v-else>No results found for "{{ query }}".</p>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
  
<script>
import axios from 'axios';

export default {
  name: 'TheHeader',

  data() {
    return {
      current_user: {},
      query: '',
      searchResults: [],

      notifications: [],
      unreadCount: 0
    };
  },

  computed: {
    isLoggedIn() {
      return !!localStorage.getItem('token');
    }
  },

  methods: {
    handleSidebar() {
      document.body.classList.toggle('toggle-sidebar');
    },    
    async markAsRead(id) {
      try {
        await axios.post(`/api/notifications/${id}/read`);

        const notif = this.notifications.find(n => n.id === id);
        if (notif) notif.read_at = new Date();

        this.unreadCount = this.notifications.filter(n => !n.read_at).length;

      } catch (err) {
        console.error(err);
      }
    },   
    async fetchNotifications() {
      try {
        const res = await axios.get('/api/notifications/unread')

        this.notifications = res.data

        // unread count (if you later add read/unread column)
        this.unreadCount = res.data.filter(n => !n.read_at).length

      } catch (err) {
        console.error('Notification error:', err)
      }
    },
    async performSearch() {
      if (!this.query.trim()) {
        this.searchResults = [];
        return;
      }

      try {
        const res = await axios.post('/api/universalsearch', {
          query: this.query
        });

        this.searchResults = res.data || [];

        const modal = new bootstrap.Modal(
          document.getElementById('searchResultsModal')
        );
        modal.show();
      } catch (err) {
        console.error('Search error:', err);
        this.searchResults = [];
      }
    },

    async logout() {
      try {
        await axios.post(
          '/api/logout',
          {},
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('token')}`
            }
          }
        );
      } catch (_) {}

      finally {
        localStorage.removeItem('token');
        localStorage.removeItem('user');

        // stop polling
        if (this.notificationInterval) {
          clearInterval(this.notificationInterval);
        }

        this.$router.replace('/login');
      }
    }
  },

  mounted() {
    this.current_user = JSON.parse(localStorage.getItem('user')) || {};

    // only run if logged in
    if (this.isLoggedIn) {
      this.fetchNotifications();

      this.notificationInterval = setInterval(() => {
        if (this.isLoggedIn) {
          this.fetchNotifications();
        }
      }, 30000);
    }
  }
};
</script>
  
  
<style>
.hover-shadow:hover {
  background-color: #f8f9fa;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

</style>