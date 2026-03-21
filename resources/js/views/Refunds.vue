<template>
  <Master>
    <section class="section dashboard">
      <div class="row">
        <div class="col-12">
          <div class="card top-selling overflow-auto">
            <div class="card-body pb-0">

              <h5 class="card-title">
                Final Refunds <span>| Derived from Deposits</span>
              </h5>

              <table id="RefundsTable" class="table table-borderless">
                <thead>
                  <tr>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Unit</th>
                    <th>Deposit</th>
                    <th>Total Deductions</th>
                    <th>Refundable</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>

                <tbody v-if="loading">
                  <tr>
                    <td colspan="8" class="text-center">
                      <div class="spinner-border text-primary"></div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr v-for="deposit in deposits" :key="deposit.id">
                    <td>{{ deposit.tenancy.tenant.full_name }}</td>
                    <td>{{ deposit.tenancy.unit.property.property_name }}</td>
                    <td>{{ deposit.tenancy.unit.unit_number }}</td>

                    <td>KES {{ deposit.amount_received }}</td>
                    <td>KES {{ deposit.total_deductions || 0 }}</td>
                    <td><strong>KES {{ refundableAmount(deposit) }}</strong></td>

                    <td>
                      <span
                        class="badge"
                        :class="deposit.status === 'held' ? 'bg-warning' : 'bg-success'"
                      >
                        {{ deposit.status }}
                      </span>
                    </td>

                    <td>
                      <div class="btn-group">
                        <button class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                          Action
                        </button>
                        <div class="dropdown-menu">
                          <a @click="viewBreakdown(deposit)" class="dropdown-item">
                            <i class="ri-eye-fill me-2"></i> View Breakdown
                          </a>
                          <a v-if="deposit.status === 'held'" @click="finalizeRefund(deposit)" class="dropdown-item">
                            <i class="ri-refund-2-line me-2"></i> Finalize Refund
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

        <!-- Breakdown Modal -->
        <div class="modal fade" id="refundBreakdownModal" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">

              <div class="modal-header">
                <h5 class="modal-title">Refund Breakdown</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body" v-if="selectedDeposit">
                <p><strong>Deposit:</strong> KES {{ selectedDeposit.amount_received }}</p>
                <hr>
                <h6>Deductions</h6>
                <ul v-if="selectedDeposit.deductions.length">
                  <li v-for="d in selectedDeposit.deductions" :key="d.id">
                    {{ d.description }} — <strong>KES {{ d.amount }}</strong>
                  </li>
                </ul>
                <p v-else class="text-muted">No deductions recorded</p>
                <hr>
                <p><strong>Refundable Amount:</strong> KES {{ refundableAmount(selectedDeposit) }}</p>
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
import $ from "jquery";
import "datatables.net-dt/js/dataTables.dataTables";
import "datatables.net-dt/css/jquery.dataTables.min.css";

const toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 3000 });

export default {
  components: { Master },

  data() {
    return { deposits: [], selectedDeposit: null, loading: true };
  },

  methods: {
    refundableAmount(deposit) {
      return Number(deposit.amount_received) - Number(deposit.total_deductions || 0);
    },

    // Load refundable deposits
    async loadRefunds() {
        this.loading = true;
        try {
            const res = await axios.get("/api/deposits/refundable");
            this.deposits = res.data;

            setTimeout(() => {
                $("#RefundsTable").DataTable();
            }, 10);
        } catch (e) {
            toast.fire({ icon: "error", title: "Failed to load refunds" });
            console.error(e);
        } finally {
            this.loading = false;
        }
    },

    // Finalize refund
    async finalizeRefund(deposit) {
        const refundable = Number(deposit.amount_received) - Number(deposit.total_deductions || 0);

        const result = await Swal.fire({
            title: "Finalize Refund?",
            html: `<p><strong>Tenant:</strong> ${deposit.tenancy.tenant.full_name}</p>
                <p><strong>Refund Amount:</strong> KES ${refundable}</p>
                <p class="text-muted">This action cannot be undone.</p>`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Finalize",
            confirmButtonColor: "#198754",
        });

        if (!result.isConfirmed) return;

        try {
            await axios.post(`/api/deposits/${deposit.id}/finalize-refund`);

            toast.fire({ icon: "success", title: "Refund finalized" });
            this.loadRefunds();
        } catch (e) {
            Swal.fire({ icon: "error", title: "Failed", text: "Could not finalize refund" });
            console.error(e);
        }
    },

    viewBreakdown(deposit) {
      this.selectedDeposit = deposit;
      new bootstrap.Modal(document.getElementById("refundBreakdownModal")).show();
    },
  },

  mounted() { this.loadRefunds(); },
};
</script>