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

              <div class="row mb-3">
                <div class="col d-flex">
                  <button
                    class="btn btn-sm btn-primary rounded-pill"
                    style="background-color: darkgreen; border-color: darkgreen;"
                    @click="addDeposit"
                  >
                    Add Deposit
                  </button>
                </div>
              </div>

              <!-- Deposits Table -->
              <table id="DepositsTable" class="table table-borderless">
                <thead>
                  <tr>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Unit</th>
                    <th>Amount Received</th>
                    <th>Received Date</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>

                <!-- Spinner while loading -->
                <tbody v-if="initializing">
                  <tr>
                    <td colspan="7" class="text-center">
                      <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr v-for="deposit in deposits" :key="deposit.id">
                    <td>{{ deposit.tenancy.tenant.full_name }}</td>
                    <td>{{ deposit.tenancy.unit.property.property_name }}</td>
                    <td>{{ deposit.tenancy.unit.unit_number }}</td>
                    <td>KES {{ deposit.amount_received }}</td>
                    <td>{{ deposit.received_date }}</td>
                    <td>
                      <span
                        class="badge"
                        :class="deposit.status === 'held' ? 'bg-warning' :
                                 deposit.status === 'partially_deducted' ? 'bg-info' :
                                 'bg-success'"
                      >
                        {{ deposit.status }}
                      </span>
                    </td>
                    <td>
                      <div class="btn-group">
                        <button
                          class="btn btn-sm btn-primary dropdown-toggle"
                          data-bs-toggle="dropdown"
                        >
                          Action
                        </button>
                        <div class="dropdown-menu">
                          <a @click="viewDeposit(deposit)" class="dropdown-item">
                            <i class="ri-eye-fill me-2"></i> View
                          </a>
                          <a @click="refundDeposit(deposit)" class="dropdown-item">
                            <i class="ri-refund-line me-2"></i> Refund
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
                    {{ selectedDeposit.received_date }}
                  </div>
                  <div class="col-md-6">
                    <strong>Status</strong><br>
                    <span
                      class="badge"
                      :class="selectedDeposit.status === 'held' ? 'bg-warning' :
                               selectedDeposit.status === 'partially_deducted' ? 'bg-info' :
                               'bg-success'"
                    >
                      {{ selectedDeposit.status }}
                    </span>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
import "jquery/dist/jquery.min.js";
import "datatables.net-dt/js/dataTables.dataTables";
import "datatables.net-dt/css/jquery.dataTables.min.css";
import $ from "jquery";

const toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 3000,
});

window.toast = toast;

export default {
  components: { Master },
  data() {
    return {
      deposits: [],
      selectedDeposit: null,
      initializing: true,
    };
  },
  methods: {
    async loadDeposits() {
      this.initializing = true;
      try {
        const response = await axios.get("/api/deposits?withTenancy=true");
        this.deposits = response.data;
        setTimeout(() => {
          $("#DepositsTable").DataTable();
        }, 10);
      } catch (err) {
        console.error("Error fetching deposits:", err);
        toast.fire({ icon: "error", title: "Failed to load deposits" });
      } finally {
        this.initializing = false;
      }
    },

    viewDeposit(deposit) {
      this.selectedDeposit = deposit;
      const modal = new bootstrap.Modal(document.getElementById("viewDepositModal"));
      modal.show();
    },

    refundDeposit() {
      // Optionally implement adding a standalone deposit
      toast.fire({ icon: "info", title: "Add deposit functionality not implemented yet" });
    },
  },
  mounted() {
    this.loadDeposits();
  },
};
</script>