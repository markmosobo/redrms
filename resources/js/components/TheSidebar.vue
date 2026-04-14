<template>
  <aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav">

      <li v-for="item in visibleMenu" :key="item.to" class="nav-item">
        <router-link :to="item.to" custom v-slot="{ href, navigate, isActive }">
          <a
            :href="href"
            class="nav-link"
            :class="{ active: isActive }"
            @click="navigate"
          >
            <i :class="`bi ${item.icon}`"></i>
            <span>{{ item.label }}</span>
          </a>
        </router-link>
      </li>

    </ul>
  </aside>
</template>

<script>
export default {
  name: 'TheSidebar',
  data() {
    return {
      userRole: '',
      menuItems: [
        {
          label: 'Dashboard',
          icon: 'bi-speedometer2',
          to: '/home',
          roles: ['admin', 'landlord', 'manager', 'tenant']
        },

        {
          label: 'Landlords',
          icon: 'bi-people',
          to: '/landlords',
          roles: ['admin']
        },

        {
          label: 'Managers',
          icon: 'bi-person-badge',
          to: '/managers',
          roles: ['admin']
        },

        {
          label: 'Tenants',
          icon: 'bi-person-badge',
          to: '/tenants',
          roles: ['admin']
        },                

        {
          label: 'Properties',
          icon: 'bi-buildings',
          to: '/properties',
          roles: ['admin', 'landlord', 'manager']
        },

        {
          label: 'Tenancies',
          icon: 'bi-house-door',
          to: '/tenancies',
          roles: ['admin', 'landlord', 'manager']
        },

        {
          label: 'Deposits',
          icon: 'bi-safe',
          to: '/deposits',
          roles: ['admin', 'landlord']
        },

        {
          label: 'Inspections',
          icon: 'bi-clipboard-check',
          to: '/inspections',
          roles: ['admin', 'landlord', 'manager']
        },

        {
          label: 'Deductions',
          icon: 'bi-scissors',
          to: '/deductions',
          roles: ['admin', 'landlord']
        },

        {
          label: 'Refunds',
          icon: 'bi-arrow-counterclockwise',
          to: '/refunds',
          roles: ['admin', 'landlord']
        },

        {
          label: 'Audit Logs',
          icon: 'bi-shield-check',
          to: '/audit-logs',
          roles: ['admin']
        },

        {
          label: 'My Profile',
          icon: 'bi-person-circle',
          to: '/profile',
          roles: ['admin', 'landlord', 'manager', 'tenant']
        }
      ]
    };
  },
  computed: {
    visibleMenu() {
      return this.menuItems.filter(item =>
        item.roles.includes(this.userRole)
      );
    }
  },  
  mounted() {
    const user = JSON.parse(localStorage.getItem('user'));
    this.userRole = user?.role ?? 'tenant';
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
