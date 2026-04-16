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

        <!-- ===== ACTION REQUIRED WIDGET ===== -->
        <div v-if="widgets?.action_required_deposits?.length" class="col-12 mb-4">
          <div class="card shadow-sm border-warning">
            <div class="card-body">

              <h5 class="card-title text-warning">
                🔔 Action Required Deposits
              </h5>

              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Tenant</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Amount</th>

                  </tr>
                </thead>

                <tbody>
                  <tr v-for="d in widgets.action_required_deposits" :key="d.id">
                    <td>{{ d.tenancy.tenant.full_name }}</td>
                    <td>{{ d.tenancy.unit.unit_number }}</td>

                    <td>
                      <span class="badge bg-warning">
                        {{ d.status }}
                      </span>
                    </td>

                    <td>KES {{ d.amount_received }}</td>
                  </tr>
                </tbody>

              </table>

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

        <!-- TERMINATION REQUEST CARD (TENANT ONLY) -->
        <div
          v-if="role === 'tenant'"
          class="col-xxl-3 col-md-4 col-sm-6 mb-3"
        >
          <div class="card shadow-sm border-danger">
            <div class="card-body">

              <!-- CARD TITLE -->
              <h5 class="card-title text-danger">
                {{ terminationRequest && terminationRequest.tenancy_id === tenancy?.id
                    ? 'Termination Request'
                    : 'My Tenancy'
                }}
              </h5>

              <!-- 🚫 NO TENANCY -->
              <div v-if="!tenancy" class="text-muted small">
                No active tenancy found
              </div>

              <!-- ✅ ACTIVE TENANCY -->
              <div v-else>

                <!-- 🔴 TERMINATION REQUEST EXISTS -->
                <div
                  v-if="terminationRequest && terminationRequest.tenancy_id === tenancy.id"
                  class="mb-3"
                >
                  <div class="small text-muted">
                    Status:
                    <span
                      class="badge"
                      :class="{
                        'bg-warning': terminationRequest.status === 'pending',
                        'bg-success': terminationRequest.status === 'approved',
                        'bg-danger': terminationRequest.status === 'rejected'
                      }"
                    >
                      {{ terminationRequest.status }}
                    </span>
                  </div>

                  <div class="small mt-2">
                    <strong>Reason:</strong>
                    {{ terminationRequest.reason }}
                  </div>

                  <div
                    v-if="terminationRequest.requested_end_date"
                    class="small"
                  >
                    <strong>Requested End:</strong>
                    {{ formatDate(terminationRequest.requested_end_date) }}
                  </div>
                </div>

                <!-- 🟢 NO TERMINATION REQUEST → SHOW TENANCY DETAILS -->
                <div v-else class="small mb-3">
                  <div>
                    <strong>Property:</strong>
                    {{ tenancy.unit.property.property_name }}
                  </div>

                  <div>
                    <strong>Unit:</strong>
                    {{ tenancy.unit.unit_number }}
                  </div>

                  <div>
                    <strong>Rent:</strong>
                    KES {{ tenancy.unit.rent_amount }}
                  </div>

                  <div>
                    <strong>Started:</strong>
                    {{ formatDate(tenancy.start_date) }}
                  </div>
                </div>

                <!-- REQUEST EXIT BUTTON -->
                <button
                  v-if="!terminationRequest || !terminationRequest.id"
                  class="btn btn-sm btn-danger w-100"
                  @click="openTerminationModal"
                >
                  Request Exit
                </button>

              </div>

            </div>
          </div>
        </div>

      </div>

      <div v-if="showTerminationModal" class="modal d-block">
        <div class="modal-dialog">
          <div class="modal-content p-3">

            <h5>Request Termination</h5>

            <textarea
              v-model="terminationReason"
              class="form-control mb-2"
              placeholder="Reason for leaving..."
            ></textarea>

            <input
              type="date"
              v-model="preferredDate"
              class="form-control mb-3"
            />

            <div class="d-flex justify-content-end gap-2">
              <button class="btn btn-secondary" @click="showTerminationModal = false">
                Cancel
              </button>

              <button class="btn btn-danger" @click="submitTermination">
                Submit
              </button>
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
      widgets: {
        action_required_deposits: []
      },
      
      tenancy: null,
      showTerminationModal: false,
      terminationReason: '',
      preferredDate: null,
      terminationRequest: null
    }
  },

  methods: {
    formatDate(date) {
      return new Date(date).toLocaleDateString()
    },
    async loadTerminationRequest() {
      try {
        const res = await axios.get('/api/my-termination-request')
        this.terminationRequest = res.data
      } catch (e) {
        this.terminationRequest = null
      }
    },    
    openTerminationModal() {
      this.showTerminationModal = true
    },

    async submitTermination() {
      try {
        await axios.post('/api/termination-requests', {
          reason: this.terminationReason,
          requested_end_date: this.preferredDate
        })

        this.showTerminationModal = false
        this.terminationReason = ''
        this.preferredDate = null

        // 🔥 IMPORTANT: refresh state from backend
        await this.loadTerminationRequest()

        toast.fire(
          'Success!',
          'Termination request submitted successfully',
          'success'
        )

      } catch (e) {
        console.error(e)

        toast.fire(
          'Error!',
          e.response?.data?.message || 'Failed to submit request',
          'error'
        )
      }
    },
    async loadMyTenancy() {
      try {
        const res = await axios.get('/api/my-active-tenancy')
        this.tenancy = res.data
      } catch (e) {
        this.tenancy = null
      }
    },    
   
    async fetchDashboard() {
      try {
        const res = await axios.get('/api/dashboard')
        this.role = res.data.role
        this.summary = res.data.summary
        this.cards = res.data.cards
        this.widgets = {
          action_required_deposits:
            res.data.widgets?.action_required_deposits || []
        }
      } catch (error) {
        console.error('Dashboard error:', error)
      }
    },
  },

  mounted() {
    this.fetchDashboard();
    this.loadMyTenancy();
    this.loadTerminationRequest();

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