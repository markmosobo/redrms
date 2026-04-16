<template>
  <aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav">

      <li
        v-for="item in visibleMenu"
        :key="item.label"
        class="nav-item"
      >

        <!-- 🔹 Dropdown Items -->
        <template v-if="item.children">

          <a
            href="#"
            class="nav-link collapsed"
            data-bs-toggle="collapse"
            :data-bs-target="`#menu-${slug(item.label)}`"
            aria-expanded="false"
          >
            <i :class="`bi ${item.icon}`"></i>
            <span>{{ item.label }}</span>
            <i class="bi bi-chevron-down ms-auto"></i>
          </a>

          <ul
            class="nav-content collapse"
            :id="`menu-${slug(item.label)}`"
            data-bs-parent="#sidebar"
          >
            <li
              v-for="child in item.children"
              :key="child.to"
            >
              <router-link
                :to="child.to"
                custom
                v-slot="{ href, navigate, isActive }"
              >
                <a
                  :href="href"
                  class="nav-link"
                  :class="{ active: isActive }"
                  @click="navigate"
                >
                  {{ child.label }}
                </a>
              </router-link>
            </li>
          </ul>

        </template>

        <!-- 🔹 Normal Items -->
        <template v-else>
          <router-link
            :to="item.to"
            custom
            v-slot="{ href, navigate, isActive }"
          >
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
        </template>

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
          label: 'Manage Users',
          icon: 'bi-people',
          roles: ['admin'],
          children: [
            { label: 'Landlords', to: '/landlords' },
            { label: 'Managers', to: '/managers' },
            { label: 'Tenants', to: '/tenants' }
          ]
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
          label: 'Workflow',
          icon: 'bi-diagram-3',
          roles: ['admin', 'manager', 'landlord'],
          children: [
            { label: 'Termination Requests', to: '/termination-requests' },
            { label: 'Deposits Queue', to: '/deposits?action=queue' },
            { label: 'Inspection Queue', to: '/inspections?status=pending' }
          ]
        },


        {
          label: 'Finance & Settlements',
          icon: 'bi-cash-coin',
          roles: ['admin', 'landlord','manager'],
          children: [
            { label: 'Deposits', to: '/deposits' },
            { label: 'Deductions', to: '/deductions' },
            { label: 'Refunds', to: '/refunds' }
          ]
        },

        {
          label: 'Audit Logs',
          icon: 'bi-shield-check',
          to: '/audit-logs',
          roles: ['admin']
        },

        {
          label: 'Messages',
          icon: 'bi-bell',
          roles: ['admin', 'landlord', 'manager', 'tenant'],
          children: [
            {
              label: 'Inbox',
              to: '/notifications',
              roles: ['admin','landlord', 'manager', 'tenant']
            },
            {
              label: 'All Messages',
              to: '/all-notifications',
              roles: ['admin']
            }
          ]
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
      return this.menuItems
        .filter(item => item.roles.includes(this.userRole))
        .map(item => {
          // If no children, return as-is
          if (!item.children) return item;

          // Filter children by role
          const filteredChildren = item.children.filter(child =>
            !child.roles || child.roles.includes(this.userRole)
          );

          // Hide parent if no children left
          if (filteredChildren.length === 0) return null;

          return {
            ...item,
            children: filteredChildren
          };
        })
        .filter(Boolean); // remove nulls
    }
  },

  mounted() {
    const user = JSON.parse(localStorage.getItem('user'));
    this.userRole = user?.role ?? 'tenant';
  },

  methods: {
    slug(text) {
      return text
        .toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/&/g, 'and'); // IMPORTANT FIX
    }
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

.nav-content {
  padding-left: 10px;
}

.nav-content .nav-link {
  padding-left: 42px;
  font-size: 0.95rem;
}

.nav-item .bi-chevron-down {
  font-size: 0.75rem;
}
</style>