<template>
  <Master>
    <section class="section dashboard">
      <div class="row">

        <!-- ================= FINANCIAL SUMMARY (PERSONAL ONLY) ================= -->
        <div v-if="userRole == 'personal'" class="col-12 mb-4">
          <div class="p-3 rounded bg-light border shadow-sm">

            <!-- Primary total -->
            <div class="text-center mb-3">
              <div class="text-muted small">Total Worth</div>
              <div class="fs-4 fw-bold">
                KES {{ accountTotal }}
              </div>
            </div>

            <!-- Breakdown -->
            <div class="row g-2 text-center">

              <!-- Liquid -->
              <div class="col-6">
                <div class="p-2 rounded bg-success bg-opacity-10 border">
                  <div class="small text-muted">
                    <i class="bi bi-cash-coin me-1"></i>
                    Liquid (Spend Now)
                  </div>
                  <div class="fw-semibold text-success">
                    KES {{ liquidTotal }}
                  </div>
                  <div class="small text-muted fst-italic">
                    Safe to use today
                  </div>
                </div>
              </div>

              <!-- Semi-liquid -->
              <div class="col-6">
                <div class="p-2 rounded bg-warning bg-opacity-10 border">
                  <div class="small text-muted">
                    <i class="bi bi-wallet2 me-1"></i>
                    Semi-Liquid (Buffer)
                  </div>
                  <div class="fw-semibold text-warning">
                    KES {{ semiLiquidTotal }}
                  </div>
                  <div class="small text-muted fst-italic">
                    Emergency cushion
                  </div>
                </div>
              </div>

              <!-- Savings -->
              <div class="col-12">
                <div class="p-2 rounded bg-primary bg-opacity-10 border">
                  <div class="small text-muted">
                    <i class="bi bi-piggy-bank me-1"></i>
                    Savings & Shares
                  </div>
                  <div class="fw-semibold text-primary">
                    KES {{ savingsTotal }}
                  </div>
                  <div class="small text-muted fst-italic">
                    Do not spend
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
        <!-- ================= END FINANCIAL SUMMARY ================= -->

        <!-- ================= FINANCIAL SUMMARY (OFFICE ONLY) ================= -->
        <div v-if="userRole == 'office'" class="col-12 mb-4">
          <div class="p-3 rounded bg-light border shadow-sm">

            <!-- Primary total -->
            <div class="text-center mb-3">
              <div class="text-muted small">Total Office Funds</div>
              <div class="fs-4 fw-bold">
                KES {{ officeTotal }}
              </div>
            </div>

            <!-- Breakdown -->
            <div class="row g-2 text-center">

              <!-- Cash on Hand -->
              <div class="col-6">
                <div class="p-2 rounded bg-success bg-opacity-10 border">
                  <div class="small text-muted">
                    <i class="bi bi-cash-coin me-1"></i>
                    Cash on Hand
                  </div>
                  <div class="fw-semibold text-success">
                    KES {{ officeCash }}
                  </div>
                  <div class="small text-muted fst-italic">
                    Immediate liquidity
                  </div>
                </div>
              </div>

              <!-- Bank Accounts -->
              <div class="col-6">
                <div class="p-2 rounded bg-info bg-opacity-10 border">
                  <div class="small text-muted">
                    <i class="bi bi-bank me-1"></i>
                    Bank Accounts
                  </div>
                  <div class="fw-semibold text-info">
                    KES {{ officeBank }}
                  </div>
                  <div class="small text-muted fst-italic">
                    Accessible funds
                  </div>
                </div>
              </div>

              <!-- Receivables / Pending Payments -->
              <div class="col-12">
                <div class="p-2 rounded bg-warning bg-opacity-10 border">
                  <div class="small text-muted">
                    <i class="bi bi-wallet2 me-1"></i>
                    Accounts Receivable
                  </div>
                  <div class="fw-semibold text-warning">
                    KES {{ officeReceivables }}
                  </div>
                  <div class="small text-muted fst-italic">
                    Pending collections
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
        <!-- ================= END FINANCIAL SUMMARY ================= -->


        <!-- ================= DASHBOARD CARDS ================= -->
        <div
          v-for="(card, index) in dashboardCards"
          :key="index"
          class="col-xxl-2 col-md-3 col-sm-4 mb-3"
        >
          <div class="card info-card shadow-sm">
            <div class="card-body">
              <h5 class="card-title" :class="`text-${card.color}`">
                {{ card.title }}
              </h5>

              <div class="d-flex align-items-center">
                <div
                  class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-light"
                >
                  <i :class="`bi ${card.icon} text-${card.color}`"></i>
                </div>
                <div class="ps-3">
                  <h6
                    class="text-truncate"
                    :style="{
                      maxWidth: '120px',
                      fontSize: card.value > 999999 ? '0.9rem' : '1rem'
                    }"
                  >
                    {{ card.value ?? 0 }}
                  </h6>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- ================= END DASHBOARD CARDS ================= -->

      </div>
    </section>
  </Master>
</template>


<script>
import Master from '@/components/Master.vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000
});

export default {
  name: 'Home',
  components: {
    Master,
  },
  data() {
    return {
      currentYear: '',
      accountTotal: null,
      semiLiquidTotal: null,
      liquidTotal: null,
      savingsTotal: null, 
      officeTotal: 0,
      officeCash: 0,
      officeBank: 0,
      officeReceivables: 0,           
      user: {},
      currentUser: {},
      userRole: null,
      stats: {},
      properties: [],
      openproperties: [],
      closedproperties: [],
      users: [],
      badgeClasses: [
        'text-success',
        'text-danger',
        'text-primary',
        'text-info',
        'text-warning',
        'text-muted',
      ],
    };
  },
  computed: {
    dashboardCards() {
      const cards = {
        office: [
          { title: 'Services Offered', value: this.stats.services, icon: 'bi-people', color: 'primary' },
          { title: 'Suppliers', value: this.stats.suppliers, icon: 'bi-person-lines-fill', color: 'info' },
          { title: 'Customers', value: this.stats.customers, icon: 'bi-people', color: 'secondary' },
          { title: 'Supplies', value: this.stats.supplies, icon: 'bi-building', color: 'success' },
          { title: 'Payments', value: this.stats.payments, icon: 'bi-circle', color: 'warning' },
          { title: 'Invoices', value: this.stats.invoices, icon: 'bi-hourglass-split', color: 'info' },
        ],

        personal: [
          { title: 'Accounts', value: this.stats.personalAccounts, icon: 'bi-building', color: 'success' },
          { title: 'Categories', value: this.stats.personalCategories, icon: 'bi-house-door', color: 'warning' },
          { title: 'Transactions', value: this.stats.personalTransactions, icon: 'bi-box-arrow-right', color: 'danger' },
        ],

      };

      // Remove cards with null, 0, or 'N/A' values for cleaner UI
      return (cards[this.userRole] || []).filter(card => {
        return card.value !== null && card.value !== 0 && card.value !== 'N/A';
      });
    }
  },
  methods: {
    fetchDashboardStats() {
      axios
        .get('/api/dashboard/stats')
        .then(response => {
          this.stats = response.data.stats;
              this.accountTotal = response.data.stats.accountTotal;
              this.liquidTotal = response.data.stats.liquidTotal;
              this.semiLiquidTotal = response.data.stats.semiLiquidTotal;
              this.savingsTotal = response.data.stats.savingsTotal; 
              this.officeTotal = response.data.stats.officeTotal;
              this.officeCash = response.data.stats.officeCash;
              this.officeBank = response.data.stats.officeBank;
              this.officeReceivables = response.data.stats.officeReceivables;                        
        })
        .catch(error => {
          console.error('Dashboard stats error:', error);
        });
    },
    navigateTo(location) {
      this.$router.push(location);
    },
  },
  mounted() {
    const storedUser = JSON.parse(localStorage.getItem('user')) || {};
    this.user = storedUser;
    this.currentUser = storedUser;
    this.userRole = this.user.role;
    this.current_user_id = storedUser.id;
    this.current_user = storedUser.full_name;
    // this.getCurrentYear();
    this.fetchDashboardStats();
  }
};
</script>



<style scoped>
.card {
  transition: transform 0.2s;
}

.card:hover {
  transform: scale(1.02);
}

.bg-light {
  background-color: rgba(255, 255, 255, 0.8);
}
</style>