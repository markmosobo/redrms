<template>
    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">
  
      <div class="d-flex align-items-center justify-content-between">
        <a href="#" class="logo d-flex align-items-center">
          <!-- <img src="@/assets/img/apex-logo.png" alt=""> -->
        </a>
        <i class="bi bi-list toggle-sidebar-btn" @click="handleSidebar"></i>
        <span class="d-none d-lg-block" style="color: darkgreen;"><strong>ALGOSPACE CYBER</strong> PORTAL</span>

      </div><!-- End Logo -->
  
      <!-- <div class="search-bar">
        <form @submit.prevent="performSearch" class="search-form d-flex align-items-center">
          <input
            v-model="query"
            type="text"
            name="query"
            placeholder="Search"
            title="Enter search keyword"
          />
          <button type="submit" title="Search">
            <i class="bi bi-search"></i>
          </button>
        </form>
      </div> -->



      <!-- End Search Bar -->
  
      <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">
  
          <li class="nav-item dropdown">
            <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
              <i class="bi bi-bell"></i>
              <span v-if="notificationCount > 0" class="badge bg-danger badge-number">
                {{ notificationCount }}
              </span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications p-2" style="min-width: 320px;">
              
              <!-- OVERDUE -->
              <li v-if="reminders.filter(r => r.status==='overdue').length" class="dropdown-header d-flex justify-content-between align-items-center">
                <span>Overdue</span>
                <span class="badge bg-danger">{{ reminders.filter(r => r.status==='overdue').length }}</span>
              </li>
              <li v-for="r in reminders.filter(r => r.status==='overdue')" :key="r.id" 
                  class="notification-item d-flex align-items-start p-2 rounded hover-shadow mb-1">
                <i class="bi bi-exclamation-triangle text-danger me-2 fs-5"></i>
                <div class="flex-grow-1">
                  <h6 class="mb-0">{{ r.title }}</h6>
                  <small class="text-muted">{{ r.date }}</small>
                </div>
                <button class="btn btn-sm btn-success ms-2" @click.stop="markAsDone(r.id)">Mark Done</button>
              </li>

              <!-- TODAY -->
              <li v-if="reminders.filter(r => r.status==='today').length" class="dropdown-header d-flex justify-content-between align-items-center mt-2">
                <span>Today</span>
                <span class="badge bg-warning text-dark">{{ reminders.filter(r => r.status==='today').length }}</span>
              </li>
              <li v-for="r in reminders.filter(r => r.status==='today')" :key="r.id" 
                  class="notification-item d-flex align-items-start p-2 rounded hover-shadow mb-1">
                <i class="bi bi-alarm text-warning me-2 fs-5"></i>
                <div class="flex-grow-1">
                  <h6 class="mb-0">{{ r.title }}</h6>
                  <small class="text-muted">{{ r.time }}</small>
                </div>
                <button class="btn btn-sm btn-success ms-2" @click.stop="markAsDone(r.id)">Mark Done</button>
              </li>

              <!-- TOMORROW -->
              <li v-if="reminders.filter(r => r.status==='tomorrow').length" class="dropdown-header d-flex justify-content-between align-items-center mt-2">
                <span>Tomorrow</span>
                <span class="badge bg-info text-dark">{{ reminders.filter(r => r.status==='tomorrow').length }}</span>
              </li>
              <li v-for="r in reminders.filter(r => r.status==='tomorrow')" :key="r.id" 
                  class="notification-item d-flex align-items-start p-2 rounded hover-shadow mb-1">
                <i class="bi bi-alarm text-info me-2 fs-5"></i>
                <div class="flex-grow-1">
                  <h6 class="mb-0">{{ r.title }}</h6>
                  <small class="text-muted">{{ r.time }}</small>
                </div>
                <button class="btn btn-sm btn-success ms-2" @click.stop="markAsDone(r.id)">Mark Done</button>
              </li>

              <!-- NO REMINDERS -->
              <li v-if="notificationCount === 0" class="px-3 py-2 text-center text-muted">
                No pending reminders 🎉
              </li>

              <li><hr class="dropdown-divider"></li>

              <!-- FOOTER LINK -->
              <li class="dropdown-footer text-center">
                <a href="/diary?filter=reminders">View all reminders</a>
              </li>
            </ul>
          </li>
  
          <li class="nav-item dropdown pe-3">
  
            <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
              <!-- <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle"> -->
              <i class="bi bi-person-fill"></i>
              <span class="d-none d-md-block dropdown-toggle ps-2">{{current_user.name}}</span>
            </a><!-- End Profile Image Icon -->
  
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
              <li class="dropdown-header">
                <h6>{{current_user.name}}</h6>
                <span v-if="current_user.role == 'admin'">Admin</span>
                <span v-else-if="current_user.role == 'landlord'">Landlord</span>
                <span v-else-if="current_user.role == 'caretaker'">Caretaker</span>
                <span v-else-if="current_user.role == 'tenant'">Tenant</span>
                <span v-else-if="current_user.role == 'service_provider'">Service Provider</span>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
  
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
              <li>
                <hr class="dropdown-divider">
              </li>
  
              <li>
                <hr class="dropdown-divider">
              </li>
  
              <li>
                <a @click.prevent="logout" class="dropdown-item d-flex align-items-center" href="#">
                  <i class="bi bi-box-arrow-right"></i>
                  <span>Sign Out</span>
                </a>
              </li>
  
            </ul><!-- End Profile Dropdown Items -->
          </li><!-- End Profile Nav -->
  
        </ul>
      </nav>
      <!-- End Icons Navigation -->
  
    </header><!-- End Header -->

    <!-- Search Results Modal -->
    <div
      class="modal fade"
      id="searchResultsModal"
      tabindex="-1"
      aria-labelledby="searchResultsModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="searchResultsModalLabel">Search Results</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div v-if="searchResults.length > 0">
              <ul>
                <li v-for="(item, index) in searchResults" :key="index">
                  <strong>{{ item.table }}</strong>:
                  <ul>
                    <li v-for="(value, key) in item.result" :key="key">
                      <span v-if="value">{{ key }}: {{ value }}</span>
                    </li>
                  </ul>
                </li>
              </ul>
            </div>
            <div v-else>
              <p>No results found for "{{ query }}".</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
import axios from 'axios';

  export default {
    name: 'TheHeader',
    data(){
      return {
        current_user: [],
        reminderInterval: null,
        query: "",
        searchResults: [],
        idleTimeout: null,
        idleTime: 600000, // 10 minutes in milliseconds
        notificationCount: 0,
        reminders: []
      }
    },
    computed: {
      isLoggedIn() {
        return !!localStorage.getItem('token');
      }
    },

      methods: {
      handleSidebar() {
        if (document.body.classList.contains("toggle-sidebar")) {
          document.body.classList.remove("toggle-sidebar");
        } else {
          document.body.classList.add("toggle-sidebar");
        }
      },
      async performSearch() {
      if (!this.query.trim()) {
        this.searchResults = [];
        return;
      }

      try {
        const response = await axios.post('/api/universalsearch', { query: this.query });
        this.searchResults = response.data || [];
        
        // Show the modal when results are fetched
        const modal = new bootstrap.Modal(document.getElementById('searchResultsModal'));
        modal.show();
      } catch (error) {
        console.error("Search error:", error);
        this.searchResults = [];
      }
    },
    async logout() {
      try {
        if (this.reminderInterval) {
          clearInterval(this.reminderInterval);
        }

        await axios.post('/api/logout', {}, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });

        localStorage.removeItem('token');
        localStorage.removeItem('user');

        this.$router.replace('/login2');

      } catch (error) {
        localStorage.removeItem('token');
        this.$router.push('/login2');
      }
    },

    async loadReminders() {
      if (!this.isLoggedIn) return;

      try {
        const res = await axios.get('/api/reminders/overview', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });

        this.reminders = res.data;
        this.notificationCount = res.data.length;
      } catch (err) {
        if (err.response?.status === 401) {
          // token expired / invalid
          this.reminders = [];
          this.notificationCount = 0;
        }
        console.error('Failed to load reminders', err);
      }
    },


    async markAsDone(id) {
      try {
        await axios.put(`/api/diary-entries/${id}/done`);
        this.loadReminders(); // refresh list & bell
      } catch (err) {
        console.error('Failed to mark as done', err);
      }
    }


    },
    mounted() {
      this.current_user = JSON.parse(localStorage.getItem('user'));

      if (this.isLoggedIn) {
        this.loadReminders();

        this.reminderInterval = setInterval(() => {
          this.loadReminders();
        }, 120000);
      }
    },
    beforeUnmount() {
      if (this.reminderInterval) {
        clearInterval(this.reminderInterval);
      }
    }


    
  }
  </script>
  
  
<style>
.hover-shadow:hover {
  background-color: #f8f9fa;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

</style>