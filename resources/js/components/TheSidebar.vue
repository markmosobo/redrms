<template>
  <aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">

      <!-- Dashboard -->
      <li class="nav-item">
        <router-link to="/dashboard" custom v-slot="{ href, navigate, isActive }">
          <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
          </a>
        </router-link>
      </li>

      <!-- Landlords -->
      <li v-show="userRole === 'landlord'" class="nav-item">
        <router-link to="/landlords" custom v-slot="{ href, navigate }">
          <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
            <i class="bi-people"></i>
            <span>Landlords</span>
          </a>
        </router-link>
      </li>
      
      <!-- Properties -->
      <li v-show="userRole === 'landlord'" class="nav-item">
        <router-link to="/properties" custom v-slot="{ href, navigate }">
          <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
            <i class="bi bi-buildings"></i>
            <span>Properties</span>
          </a>
        </router-link>
      </li>

      <!-- Tenancies -->
      <li v-show="userRole === 'landlord'" class="nav-item">
        <router-link to="/tenancies" custom v-slot="{ href, navigate }">
          <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
            <i class="bi bi-house-door"></i>
            <span>Tenancies</span>
          </a>
        </router-link>
      </li>


    <li v-show="userRole === 'landlord'" class="nav-item">
      <router-link to="/deposits" custom v-slot="{ href, navigate, isActive }">
        <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
          <i class="bi bi-safe"></i>
          <span>Deposits</span>
        </a>
      </router-link>
    </li>

    <li v-show="userRole === 'landlord'" class="nav-item">
      <router-link to="/inspections" custom v-slot="{ href, navigate, isActive }">
        <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
          <i class="bi bi-clipboard-check"></i>
          <span>Inspections</span>
        </a>
      </router-link>
    </li>


    <li v-show="userRole === 'landlord'" class="nav-item">
      <router-link to="/deductions" custom v-slot="{ href, navigate, isActive }">
        <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
          <i class="bi bi-scissors"></i>
          <span>Deductions</span>
        </a>
      </router-link>
    </li>    

    <!-- Refunds -->
    <li class="nav-item mt-3">
    <RouterLink to="/refunds" :class="{ active: isActive }" class="nav-link">
        <i class="bi bi-arrow-counterclockwise"></i>
        <span>Refunds</span>
    </RouterLink>
    </li>

      <!-- Logs -->
      <li v-show="userRole === 'landlord'" class="nav-item">
        <RouterLink to="/audit-logs" class="nav-link">
          <i class="bi bi-shield-check"></i>
          <span>Audit Logs</span>
        </RouterLink>
      </li>




      <!-- Profile -->
      <li class="nav-item mt-3">
        <RouterLink to="/profile" :class="{ active: isActive }" class="nav-link">
          <i class="bi bi-person-circle"></i>
          <span>My Profile</span>
        </RouterLink>
      </li>

    </ul>
  </aside>
</template>


<script>
export default {
  name: 'TheSidebar',
  data() {
    return {
      userRole: ''
    };
  },
  mounted() {
    const user = JSON.parse(localStorage.getItem('user'));
    this.userRole = user?.role || '';
  }
};
</script>

<style scoped>
.sidebar .nav-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.sidebar .nav-link i {
  font-size: 1.1rem;
}

.sidebar .nav-link:hover {
  background: #f4f6f9;
  color: #0d6efd;
}

.sidebar .nav-link.active {
  background: #0d6efd;
  color: #fff;
}

.nav-content .nav-link {
  padding-left: 42px;
  font-size: 0.95rem;
}

</style>
