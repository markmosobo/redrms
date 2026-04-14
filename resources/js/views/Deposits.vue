<template>
  <Master>
    <section class="section dashboard">
      <div class="row">

        <!-- Deposits Card -->
        <div class="col-12">
          <div class="card top-selling overflow-auto">

            <div class="card-body pb-0">
              <h5 class="card-title">
                Deposits <span>| Held, Partially Deducted & Refunded</span>
              </h5>

              <!-- <div class="row mb-3">
                <div class="col d-flex">
                  <button
                    class="btn btn-sm btn-primary rounded-pill"
                    style="background-color: darkgreen; border-color: darkgreen;"
                    @click="openAddDepositModal"
                  >
                    Add Deposit
                  </button>
                </div>
              </div> -->

              <!-- Deposits Table -->
              <table id="DepositsTable" class="table table-borderless">
                <thead>
                  <tr>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Unit</th>

                    <!-- 🔥 NEW -->
                    <th>Required Amount</th>

                    <th>Amount Received</th>
                    <th>Progress</th>
                    <th>Received Date</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>

                <!-- LOADING -->
                <tbody v-if="initializing">
                  <tr>
                    <td colspan="9" class="text-center">
                      <div class="spinner-border text-primary"></div>
                    </td>
                  </tr>
                </tbody>

                <!-- DATA -->
                <tbody v-else>
                  <tr v-for="deposit in deposits" :key="deposit.id">

                    <!-- Tenant -->
                    <td>{{ deposit.tenancy.tenant.full_name }}</td>

                    <!-- Property -->
                    <td>{{ deposit.tenancy.unit.property.property_name }}</td>

                    <!-- Unit -->
                    <td>{{ deposit.tenancy.unit.unit_number }}</td>

                    <!-- REQUIRED AMOUNT -->
                    <td>
                      KES {{ deposit.required_amount }}
                    </td>

                    <!-- RECEIVED -->
                    <td>
                      KES {{ deposit.amount_received }}
                    </td>

                    <!-- PROGRESS BAR -->
                    <td style="min-width: 180px;">
                      <div class="d-flex flex-column">

                        <small class="text-muted mb-1">
                          {{ deposit.amount_received }} / {{ deposit.required_amount }}
                        </small>

                        <div class="progress" style="height: 6px;">
                          <div
                            class="progress-bar bg-success"
                            :style="{
                              width: (
                                deposit.required_amount > 0
                                  ? (deposit.amount_received / deposit.required_amount) * 100
                                  : 0
                              ) + '%'
                            }"
                          ></div>
                        </div>

                        <small class="text-muted mt-1">
                          {{
                            deposit.required_amount > 0
                              ? Math.round((deposit.amount_received / deposit.required_amount) * 100)
                              : 0
                          }}%
                        </small>

                      </div>
                    </td>

                    <!-- RECEIVED DATE -->
                    <td>
                      {{ formatDate(deposit.received_date) }}
                    </td>

                    <!-- STATUS -->
                    <td>
                      <span
                        class="badge"
                        :class="deposit.status === 'held'
                          ? 'bg-success'
                          : deposit.status === 'active'
                          ? 'bg-warning'
                          : deposit.status === 'under_inspection'
                          ? 'bg-info'
                          : deposit.status === 'pending_refund'
                          ? 'bg-secondary'
                          : 'bg-dark'"
                      >
                        {{ deposit.status }}
                      </span>
                    </td>

                    <!-- ACTIONS -->
                    <td>
                      <div class="btn-group">

                        <button class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                          Action
                        </button>

                        <div class="dropdown-menu">

                          <!-- VIEW -->
                          <a @click="viewDeposit(deposit)" class="dropdown-item">
                            View
                          </a>

                          <!-- RECEIVE PAYMENT -->
                          <a
                            v-if="deposit.amount_received < deposit.required_amount"
                            class="dropdown-item"
                            @click="openReceivePayment(deposit)"
                          >
                            Receive Payment
                          </a>

                          <!-- REFUND -->
                          <a
                            class="dropdown-item"
                            :class="{ disabled: !canRefund(deposit) }"
                            @click="canRefund(deposit) && refundDeposit(deposit)"
                          >
                            Refund
                          </a>

                        </div>
                      </div>
                    </td>

                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- View Deposit Modal -->
        <div class="modal fade" id="viewDepositModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">

              <div class="modal-header">
                <h5 class="modal-title">Deposit Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body" v-if="selectedDeposit">
                <div class="row g-3">

                  <div class="col-md-6">
                    <strong>Tenant</strong><br>
                    {{ selectedDeposit.tenancy.tenant.full_name }}
                  </div>

                  <div class="col-md-6">
                    <strong>Unit</strong><br>
                    {{ selectedDeposit.tenancy.unit.unit_number }}
                  </div>

                  <div class="col-md-6">
                    <strong>Property</strong><br>
                    {{ selectedDeposit.tenancy.unit.property.property_name }}
                  </div>

                  <div class="col-md-6">
                    <strong>Amount Received</strong><br>
                    KES {{ selectedDeposit.amount_received }}
                  </div>

                  <div class="col-md-6">
                    <strong>Received Date</strong><br>
                    {{ formatDate(selectedDeposit.received_date) }}
                  </div>

                  <div class="col-md-6">
                    <strong>Status</strong><br>
                    <span
                      class="badge"
                      :class="selectedDeposit.status === 'held'
                        ? 'bg-warning'
                        : selectedDeposit.status === 'partially_deducted'
                        ? 'bg-info'
                        : 'bg-success'"
                    >
                      {{ selectedDeposit.status }}
                    </span>
                  </div>

                </div>
              </div>

              <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                  Close
                </button>
              </div>

            </div>
          </div>
        </div>

        <!-- 🔥 ADD DEPOSIT MODAL -->
        <div class="modal fade" id="addDepositModal" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">

              <div class="modal-header">
                <h5 class="modal-title">Add Deposit</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">

                <!-- Tenancy -->
                <div class="mb-3">
                  <label class="form-label">Tenancy</label>
                  <select v-model="form.tenancy_id" class="form-control">
                    <option value="">Select Tenancy</option>
                    <option
                      v-for="t in tenancies"
                      :key="t.id"
                      :value="t.id"
                    >
                      {{ t.tenant.full_name }} - {{ t.unit.unit_number }}
                    </option>
                  </select>
                </div>

                <!-- Amount -->
                <div class="mb-3">
                  <label class="form-label">Amount Received</label>
                  <input v-model="form.amount_received" type="number" class="form-control">
                </div>

                <!-- Date -->
                <div class="mb-3">
                  <label class="form-label">Received Date</label>
                  <input v-model="form.received_date" type="date" class="form-control">
                </div>

              </div>

              <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success" @click="storeDeposit">
                  Save Deposit
                </button>
              </div>

            </div>
          </div>
        </div>

      <div class="modal fade" id="receivePaymentModal">
        <div class="modal-dialog">
          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title">Receive Payment</h5>
            </div>

            <div class="modal-body">

              <input type="number" v-model="payment.amount" class="form-control" placeholder="Amount received">

            </div>

            <div class="modal-footer">
              <button class="btn btn-success" @click="submitPayment">
                Confirm Payment
              </button>
            </div>

          </div>
        </div>
      </div>        

      </div>
    </section>
  </Master>
</template>

<script>
import Master from "@/components/Master.vue";
import axios from "axios";
import Swal from "sweetalert2";
import $ from "jquery";

const toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 3000,
});

export default {
  components: { Master },

  data() {
    return {
      deposits: [],
      tenancies: [],
      selectedDeposit: null,
      initializing: true,
      payment: {
        deposit_id: null,
        amount: 0
      },
      form: {
        tenancy_id: "",
        amount_received: "",
        received_date: "",
      }
    };
  },

  methods: {
    openReceivePayment(deposit) {
      this.payment.deposit_id = deposit.id;
      this.payment.amount = deposit.id;

      const modal = new bootstrap.Modal(
        document.getElementById('receivePaymentModal')
      );
      modal.show();
    },

    async submitPayment() {
      await axios.post(`/api/deposits/${this.payment.deposit_id}/receive`, {
        amount: this.payment.amount
      });

      toast.fire({ icon: "success", title: "Payment received" });

      bootstrap.Modal.getInstance(
        document.getElementById('receivePaymentModal')
      ).hide();

      this.loadDeposits();
    },    
    canRefund(deposit) {
      return deposit.status === 'pending_refund';
    },
    // LOAD DEPOSITS
    async loadDeposits() {
      this.initializing = true;
      try {
        const res = await axios.get("/api/deposits?withTenancy=true");
        this.deposits = res.data;

        setTimeout(() => {
          $("#DepositsTable").DataTable();
        }, 10);

      } catch (e) {
        toast.fire({ icon: "error", title: "Failed to load deposits" });
      } finally {
        this.initializing = false;
      }
    },

    // LOAD TENANCIES FOR DROPDOWN
    async loadTenancies() {
      const res = await axios.get("/api/tenancies?withRelations=true");
      this.tenancies = res.data;
    },

    // OPEN MODAL
    openAddDepositModal() {
      this.form = {
        tenancy_id: "",
        amount_received: "",
        received_date: "",
      };

      const modal = new bootstrap.Modal(
        document.getElementById("addDepositModal")
      );
      modal.show();
    },

    // STORE DEPOSIT
    async storeDeposit() {
      try {
        await axios.post("/api/deposits", this.form);

        toast.fire({ icon: "success", title: "Deposit saved" });

        bootstrap.Modal.getInstance(
          document.getElementById("addDepositModal")
        ).hide();

        this.loadDeposits();

      } catch (e) {
        console.error(e);
        toast.fire({ icon: "error", title: "Failed to save deposit" });
      }
    },
    formatDate(date) {
      if (!date) return '—';

      const d = new Date(date);

      return d.toLocaleDateString('en-KE', {
        year: 'numeric',
        month: 'short',
        day: '2-digit'
      });
    },
    // VIEW
    viewDeposit(deposit) {
      console.log(this.selectedDeposit)
      this.selectedDeposit = deposit;
      // Show the modal after fetching data
      const modal = new bootstrap.Modal(document.getElementById('viewDepositModal'));
      modal.show();
    },

    refundDeposit() {
      toast.fire({ icon: "info", title: "Refund flow coming next" });
    }
  },

  mounted() {
    this.loadDeposits();
    this.loadTenancies();
  }
};
</script>