<template>
  <Master>
    <section class="section dashboard">
      <div class="row">

        <!-- ===== SUMMARY ===== -->
        <div v-if="summary" class="col-12 mb-4">
          <div class="p-3 rounded bg-light border shadow-sm text-center">

            <div class="text-muted small">Summary</div>
            <div v-if="summary.total !== null" class="fs-4 fw-bold">
              KES {{ summary.total }}
            </div>

            <div v-if="Object.keys(summary.breakdown).length" class="row g-2 mt-3">
              <div
                v-for="(value, key) in summary.breakdown"
                :key="key"
                class="col-6 col-md-4"
              >
                <div class="p-2 rounded border bg-opacity-10">
                  <div class="small text-muted text-capitalize">
                    {{ key.replace('_', ' ') }}
                  </div>
                  <div class="fw-semibold">
                    KES {{ value }}
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- ===== DASHBOARD CARDS ===== -->
        <div
          v-for="(card, index) in cards"
          :key="index"
          class="col-xxl-2 col-md-3 col-sm-4 mb-3"
        >
          <div class="card shadow-sm">
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
                  <h6>{{ card.value }}</h6>
                </div>
              </div>

            </div>
          </div>
        </div>

      </div>
    </section>
  </Master>
</template>


<script>
import Master from '@/components/Master.vue'
import axios from 'axios'

export default {
  name: 'Home',
  components: { Master },

  data() {
    return {
      role: null,
      summary: null,
      cards: [],
    }
  },

  methods: {
    async fetchDashboard() {
      try {
        const res = await axios.get('/api/dashboard')
        this.role = res.data.role
        this.summary = res.data.summary
        this.cards = res.data.cards
      } catch (error) {
        console.error('Dashboard error:', error)
      }
    },
  },

  mounted() {
    this.fetchDashboard()
  },
}
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