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

              <!-- TABLE -->
              <table class="table table-borderless">
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

                <tbody>

                  <!-- 🔄 LOADING STATE -->
                  <tr v-if="loading">
                    <td colspan="8" class="text-center py-5">
                      <div class="spinner-border text-primary mb-2"></div>
                      <div class="text-muted">Loading refunds…</div>
                    </td>
                  </tr>

                  <!-- 📭 EMPTY STATE -->
                  <tr v-else-if="refunds.length === 0">
                    <td colspan="8" class="text-center py-5">
                      <i class="ri-inbox-line fs-1 text-muted mb-2 d-block"></i>
                      <div class="fw-semibold">No refunds available</div>
                      <div class="text-muted">
                        All refundable deposits have already been processed.
                      </div>
                    </td>
                  </tr>

                  <!-- 📊 DATA STATE -->
                  <tr v-else v-for="refund in refunds" :key="refund.refund_id">

                    <td>{{ refund.tenant?.full_name || 'N/A' }}</td>

                    <td>{{ refund.property?.property_name || 'N/A' }}</td>

                    <td>{{ refund.unit?.unit_number || 'N/A' }}</td>

                    <td>KES {{ refund.amount_received }}</td>

                    <td>KES {{ refund.total_deductions || 0 }}</td>

                    <td>
                      <strong>KES {{ refund.refundable_amount }}</strong>
                    </td>

                    <td>
                      <span
                        class="badge"
                        :class="{
                          'bg-warning': refund.status === 'pending',
                          'bg-success': refund.status === 'approved',
                          'bg-danger': refund.status === 'rejected',
                          'bg-info': refund.status === 'paid'
                        }"
                      >
                        {{ refund.status }}
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
                          <a class="dropdown-item" @click="viewBreakdown(refund)">
                            View Breakdown
                          </a>

                          <a
                            v-if="refund.status === 'pending'"
                            class="dropdown-item"
                            @click="finalizeRefund(refund)"
                          >
                            Finalize Refund
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

        <!-- BREAKDOWN MODAL -->
        <div class="modal fade" id="refundBreakdownModal" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">

              <div class="modal-header">
                <h5 class="modal-title">Refund Breakdown</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body" v-if="selectedRefund">

                <p><strong>Deposit:</strong> KES {{ selectedRefund.amount_received }}</p>

                <hr>

                <p v-if="Number(selectedRefund.total_deductions) > 0">
                  <strong>Deductions:</strong>
                  KES {{ selectedRefund.total_deductions }}
                </p>

                <p v-else class="text-muted">
                  No deductions recorded
                </p>

                <hr>

                <p>
                  <strong>Refundable Amount:</strong>
                  KES {{ selectedRefund.refundable_amount }}
                </p>

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
      refunds: [],
      loading: false,
      selectedRefund: null,
    };
  },

  methods: {

    // -------------------------
    // LOAD REFUNDS
    // -------------------------
    async loadRefunds() {
      this.loading = true;

      try {
        const res = await axios.get("/api/refunds/pending");
        this.refunds = res.data;

      } catch (e) {
        console.error(e);
        toast.fire({ icon: "error", title: "Failed to load refunds" });

      } finally {
        this.loading = false;
      }
    },

    // -------------------------
    // REFUND CALC (SAFE)
    // -------------------------
    refundableAmount(refund) {
      return Number(refund.amount_received) -
             Number(refund.total_deductions || 0);
    },

    // -------------------------
    // FINALIZE REFUND
    // -------------------------
    async finalizeRefund(refund) {

      // 🔒 Guard
      if (refund.status !== 'pending') {
        toast.fire({ icon: "info", title: "Refund already processed" });
        return;
      }

      const refundable = Number(this.refundableAmount(refund)).toLocaleString();

      const result = await Swal.fire({
        title: "Finalize Refund?",
        html: `
          <p><strong>Tenant:</strong> ${refund.tenant?.full_name || "N/A"}</p>
          <p><strong>Amount:</strong> KES ${refundable}</p>
          <p class="text-muted">This action cannot be undone.</p>
        `,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Finalize",
        confirmButtonColor: "#198754",
      });

      if (!result.isConfirmed) return;

      try {
        await axios.post(`/api/refunds/${refund.refund_id}/finalize`);

        toast.fire({ icon: "success", title: "Refund finalized" });

        // 🔥 Clean reactive refresh
        await this.loadRefunds();

      } catch (e) {
        console.error(e);
        Swal.fire({
          icon: "error",
          title: "Failed",
          text: "Could not finalize refund",
        });
      }
    },

    // -------------------------
    // MODAL
    // -------------------------
    viewBreakdown(refund) {
      this.selectedRefund = refund;

      const modal = new bootstrap.Modal(
        document.getElementById("refundBreakdownModal")
      );

      modal.show();
    },
  },

  mounted() {
    this.loadRefunds();
  },
};
</script>