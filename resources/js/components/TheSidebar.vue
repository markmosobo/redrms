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

        // 🔥 NEW STRUCTURE STARTS HERE

        {
          label: 'Operations',
          icon: 'bi-diagram-3',
          roles: ['admin', 'manager', 'landlord'],
          children: [
            { label: 'Terminations', to: '/termination-requests' },
            { label: 'Inspections', to: '/inspections?status=pending' }
          ]
        },

        {
          label: 'Finance & Settlements',
          icon: 'bi-cash-coin',
          roles: ['admin', 'landlord', 'manager'],
          children: [
            { label: 'Deposits', to: '/deposits' },
            { label: 'Deductions', to: '/deductions' },
            { label: 'Refunds (Pending)', to: '/refunds' },
            { label: 'Reports', to: '/reports' }
          ]
        },

        // 🔥 NEW STRUCTURE ENDS HERE

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
          if (!item.children) return item;

          const filteredChildren = item.children.filter(child =>
            !child.roles || child.roles.includes(this.userRole)
          );

          if (filteredChildren.length === 0) return null;

          return {
            ...item,
            children: filteredChildren
          };
        })
        .filter(Boolean);
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
        .replace(/&/g, 'and');
    }
  }
};
</script>