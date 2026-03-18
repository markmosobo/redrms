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

      <!-- Ledger Reports -->
      <li v-show="userRole === 'personal'" class="nav-item">
        <router-link to="/ledger-report" custom v-slot="{ href, navigate }">
          <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
            <i class="bi bi-bar-chart-line"></i>
            <span>Profit & Tithe</span>
          </a>
        </router-link>
      </li>
      
      <!-- Personal Accounts -->
      <li v-show="userRole === 'personal'" class="nav-item">
        <router-link to="/personal-accounts" custom v-slot="{ href, navigate }">
          <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
            <i class="bi bi-wallet2"></i>
            <span>Accounts</span>
          </a>
        </router-link>
      </li>

      <!-- Personal Transactions -->
      <li v-show="userRole === 'personal'" class="nav-item">
        <router-link to="/personal-transactions" custom v-slot="{ href, navigate }">
          <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
            <i class="bi bi-wallet2"></i>
            <span>Transactions</span>
          </a>
        </router-link>
      </li>


    <li v-show="userRole === 'office'" class="nav-item">
      <router-link to="/quick-sale" custom v-slot="{ href, navigate, isActive }">
        <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
          <i class="bi bi-lightning-charge"></i>
          <span>Quick Sale</span>
        </a>
      </router-link>
    </li>

    <li v-show="userRole === 'office'" class="nav-item">
      <router-link to="/expenses" custom v-slot="{ href, navigate, isActive }">
        <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
          <i class="bi bi-cash-stack"></i>
          <span>Expenses</span>
        </a>
      </router-link>
    </li>


    <li v-show="userRole === 'office'" class="nav-item">
      <router-link to="/reports" custom v-slot="{ href, navigate, isActive }">
        <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
          <i class="bi bi-graph-up-arrow"></i>
          <span>Reports</span>
        </a>
      </router-link>
    </li>

      <!-- Payments -->
      <!-- <li v-show="userRole === 'office'" class="nav-item">
        <router-link to="/payments" custom v-slot="{ href, navigate }">
          <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
            <i class="bi bi-cash-coin"></i>
            <span>Payments</span>
          </a>
        </router-link>
      </li> -->
      
      <!-- Configurations -->
      <li v-show="userRole === 'personal'" class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" href="#listing-nav">
          <i class="bi bi-gear"></i>
          <span>Configurations</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="listing-nav" class="nav-content collapse">
          <li>
            <router-link to="/personal-accounts" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-circle"></i> Accounts
              </a>
            </router-link>
          </li>
          <li>
            <router-link to="/personal-categories" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-circle"></i> Categories
              </a>
            </router-link>
          </li>
        </ul>
      </li>

      <li v-show="userRole === 'office'" class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" href="#office-config-nav">
          <i class="bi bi-gear"></i>
          <span>Configurations</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="office-config-nav" class="nav-content collapse">
          <li>
            <router-link to="/services" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-circle"></i> Services
              </a>
            </router-link>
          </li>
          <li>
            <router-link to="/provider-services" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-circle"></i> Provider Services
              </a>
            </router-link>
          </li>
        </ul>
      </li>

      <li v-show="userRole === 'farm'" class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" href="#farm-config-nav">
          <i class="bi bi-sliders"></i>
          <span>Configurations</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="farm-config-nav" class="nav-content collapse">
          <li>
            <router-link to="/farms" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-circle"></i> Farms
              </a>
            </router-link>
          </li>
          <li>
            <router-link to="/farm-ventures" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-circle"></i> Farm Ventures
              </a>
            </router-link>
          </li>
        </ul>
      </li>      

      <li v-show="userRole === 'farm'" class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" href="#prod-config-nav">
          <i class="bi bi-bar-chart-line"></i>
          <span>Crop & Production</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="prod-config-nav" class="nav-content collapse">
          <li>
            <router-link to="/crops" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-tree"></i> Crops
              </a>
            </router-link>
          </li>
          <li>
            <router-link to="/harvests" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-basket"></i> Harvests
              </a>
            </router-link>
          </li>
        </ul>
      </li>

      <li v-show="userRole === 'farm'" class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" href="#seedling-config-nav">
          <i class="bi bi-diagram-3"></i>
          <span>Seedling & Nursery</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="seedling-config-nav" class="nav-content collapse">
          <li>
            <router-link to="/seedlings" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-tree"></i> Seedlings
              </a>
            </router-link>
          </li>
          <li>
            <router-link to="/seedling-sales" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-cart"></i> Seedling Sales
              </a>
            </router-link>
          </li>
        </ul>
      </li>      

      <li v-show="userRole === 'farm'" class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" href="#inputs-config-nav">
          <i class="bi bi-wallet2"></i> <!-- Represents finances/expenses -->
          <span>Expenses & Inputs</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="inputs-config-nav" class="nav-content collapse">
          <!-- Farm Expenses -->
          <li>
            <router-link to="/farm-expenses" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-cash-stack"></i> Farm Expenses
              </a>
            </router-link>
          </li>

          <!-- Farm Inputs -->
          <li>
            <router-link to="/farm-inputs" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-box-seam"></i> Farm Inputs
              </a>
            </router-link>
          </li>
        </ul>
      </li>  

      <li v-show="userRole === 'farm'" class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" href="#income-config-nav">
          <i class="bi bi-wallet2"></i> <!-- Represents finances/expenses -->
          <span>Sales & Income</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="income-config-nav" class="nav-content collapse">
          <!-- Farm Sales -->
          <li>
            <router-link to="/farm-sales" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-cash-stack"></i> Farm Sales
              </a>
            </router-link>
          </li>
        </ul>
      </li>  

      <li v-show="userRole === 'farm'" class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" href="#labour-config-nav">
          <i class="bi bi-people"></i> <!-- Represents workforce/team -->
          <span>Labour & Workforce</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="labour-config-nav" class="nav-content collapse">
          <!-- Farm Workers -->
          <li>
            <router-link to="/farm-workers" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-person-badge"></i> Farm Workers
              </a>
            </router-link>
          </li>

          <!-- Worker Tasks -->
          <li>
            <router-link to="/worker-tasks" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-list-task"></i> Worker Tasks
              </a>
            </router-link>
          </li>
        </ul>
      </li>      
      
      <li v-show="userRole === 'farm'" class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" href="#assets-config-nav">
          <i class="bi bi-tools"></i> <!-- Represents tools/equipment -->
          <span>Assets & Equipment</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="assets-config-nav" class="nav-content collapse">
          <!-- Farm Assets -->
          <li>
            <router-link to="/farm-assets" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-box-seam"></i> Farm Assets
              </a>
            </router-link>
          </li>
        </ul>
      </li>     
      
      <!-- Inventory -->
      <li v-show="userRole === 'office'" class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" href="#supplies-nav">
          <i class="bi bi-boxes"></i>
          <span>Inventory</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="supplies-nav" class="nav-content collapse">
          <li>
            <router-link to="/supplies" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-circle"></i> Supplies
              </a>
            </router-link>
          </li>
          <li>
            <router-link to="/restocks" custom v-slot="{ href, navigate, isActive }">
              <a :href="href" :class="{ active: isActive }" class="nav-link" @click="navigate">
                <i class="bi bi-circle"></i> Restocks
              </a>
            </router-link>
          </li>
        </ul>
      </li>

      <!-- Resources -->
      <li v-show="userRole === 'office'" class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" href="#users-nav">
          <i class="bi bi-people"></i>
          <span>Resources</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="users-nav" class="nav-content collapse">
          <li><RouterLink to="/customers" class="nav-link"><i class="bi bi-circle"></i> Customers</RouterLink></li>
          <li><RouterLink to="/suppliers" class="nav-link"><i class="bi bi-circle"></i> Suppliers</RouterLink></li>
          <li><RouterLink to="/service-providers" class="nav-link"><i class="bi bi-circle"></i> Service Providers</RouterLink></li>
          <li><RouterLink to="/partners" class="nav-link"><i class="bi bi-circle"></i> Partners</RouterLink></li>
          <li><RouterLink to="/borrowers" class="nav-link"><i class="bi bi-circle"></i> Book Borrowers</RouterLink></li>
        </ul>
      </li>

      <!-- Invoices -->
      <li v-show="userRole === 'office'" class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" href="#invoice-nav">
          <i class="bi bi-receipt"></i>
          <span>Invoices</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="invoice-nav" class="nav-content collapse">
          <li><RouterLink to="/pending-invoices" class="nav-link"><i class="bi bi-circle"></i> Pending</RouterLink></li>
          <li><RouterLink to="/invoices" class="nav-link"><i class="bi bi-circle"></i> Paid</RouterLink></li>
        </ul>
      </li>

      <!-- Library Section -->
      <li v-show="userRole === 'office'" class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" href="#library-nav">
          <i class="bi bi-book-half"></i>
          <span>Library</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="library-nav" class="nav-content collapse">
          <li>
            <RouterLink to="/book-rentals" class="nav-link">
              <i class="bi bi-circle"></i> Book Rentals
            </RouterLink>
          </li>
          <li>
            <RouterLink to="/books" class="nav-link">
              <i class="bi bi-circle"></i> Books Catalog
            </RouterLink>
          </li>
        </ul>
      </li>

      <!-- Logs -->
      <li v-show="userRole === 'office'" class="nav-item">
        <RouterLink to="/system-logs" class="nav-link">
          <i class="bi bi-shield-check"></i>
          <span>System Logs</span>
        </RouterLink>
      </li>

      <!-- Diary -->
      <li class="nav-item mt-3">
        <RouterLink to="/diary" :class="{ active: isActive }" class="nav-link">
          <i class="bi bi-journal-text"></i>
          <span>My Diary</span>
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
