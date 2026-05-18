<template>
  <Master>
    <section class="section dashboard">
      <div class="row">

        <div class="col-12">
          <div class="card top-selling overflow-auto">
            <div class="card-body pb-0">

              <h5 class="card-title">
                Reports <span>| Deductions and Refunds Derived from Deposits</span>
              </h5>

              <!-- LOADING -->
              <div v-if="loading" class="text-center py-4">
                <div class="spinner-border text-primary"></div>
                <div class="text-muted mt-2">Loading refunds…</div>
              </div>

              <!-- TABLE -->
              <table
                v-show="!loading"
                id="ReportsTable"
                class="table table-borderless"
              >
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
                  <tr v-for="refund in refunds" :key="refund.refund_id">

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
                        :class="statusClass(refund.status)"
                      >
                        {{ refund.status }}
                      </span>
                    </td>

                    <td>
                      <div class="btn-group">
                        <button class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                          Action
                        </button>

                        <div class="dropdown-menu">

                          <a class="dropdown-item" @click="viewBreakdown(refund)">
                            View Breakdown
                          </a>

                          <a class="dropdown-item" @click="openReceipts(refund)">
                            View Receipts
                          </a>

                          <a
                            v-if="refund.status === 'approved'"
                            class="dropdown-item"
                            @click="payRefund(refund)"
                          >
                            Pay Refund
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

        <!-- MODALS (UNCHANGED UI) -->
        <div class="modal fade" id="refundBreakdownModal" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">

              <div class="modal-header">
                <h5 class="modal-title">Refund Breakdown</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body" v-if="selectedRefund">

                <p><strong>Tenant:</strong> {{ selectedRefund.tenant?.full_name }}</p>
                <p><strong>Property:</strong> {{ selectedRefund.property?.property_name }}</p>
                <p><strong>Unit:</strong> {{ selectedRefund.unit?.unit_number }}</p>

                <hr>

                <p><strong>Deposit:</strong> KES {{ selectedRefund.amount_received }}</p>
                <p><strong>Deductions:</strong> KES {{ selectedRefund.total_deductions || 0 }}</p>

                <hr>

                <p>
                  <strong>Refundable:</strong>
                  <span class="text-success fw-bold">
                    KES {{ selectedRefund.refundable_amount }}
                  </span>
                </p>

                <hr>

                <p>
                  <strong>Status:</strong>
                  <span class="badge" :class="statusClass(selectedRefund.status)">
                    {{ selectedRefund.status }}
                  </span>
                </p>

              </div>

              <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>

            </div>
          </div>
        </div>

        <!-- RECEIPTS -->
        <div class="modal fade" id="receiptsModal" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">

              <div class="modal-header">
                <h5 class="modal-title">Receipts</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">

                <div v-if="receiptsLoading" class="text-center">
                  <div class="spinner-border text-primary"></div>
                </div>

                <table v-else class="table table-sm table-bordered">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Amount</th>
                      <th>Method</th>
                      <th>Date</th>
                      <th>Action</th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr v-for="r in receipts" :key="r.id">
                      <td>{{ r.receipt_number }}</td>
                      <td>KES {{ r.amount }}</td>
                      <td>{{ r.payment_method }}</td>
                      <td>{{ formatDate(r.issued_at) }}</td>
                      <td>
                        <button class="btn btn-sm btn-primary" @click="printReceipt(r.id)">
                          Print
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>

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
import "datatables.net-dt";

let dt = null;

export default {
  components: { Master },

  data() {
    return {
      refunds: [],
      loading: false,
      selectedRefund: null,

      receipts: [],
      receiptsLoading: false,
      selectedDepositId: null,
    };
  },

  methods: {

    statusClass(status) {
      return {
        pending: "bg-warning",
        approved: "bg-success",
        rejected: "bg-danger",
        paid: "bg-info",
      }[status] || "bg-secondary";
    },

    async loadRefunds() {
      this.loading = true;

      try {
        const res = await axios.get("/api/refunds/finalized");

        this.refunds = res.data;

        await this.$nextTick();

        // destroy old table safely
        if (dt) {
          dt.destroy();
          dt = null;
        }

        dt = $("#ReportsTable").DataTable({
          destroy: true,
          pageLength: 10,
          autoWidth: false,
        });

      } catch (e) {
        console.error(e);
        Swal.fire("Error", "Failed loading refunds", "error");
      } finally {
        this.loading = false;
      }
    },

    async reloadRefunds() {
      try {
        const res = await axios.get("/api/refunds/finalized");

        this.refunds = [];

        await this.$nextTick();

        this.refunds = res.data;

        await this.$nextTick();

        if (dt) {
          dt.clear();
          dt.destroy();
          dt = null;
        }

        dt = $("#ReportsTable").DataTable({
          destroy: true,
          pageLength: 10,
          autoWidth: false,
        });

      } catch (e) {
        console.error(e);
      }
    },

    async payRefund(refund) {

      const result = await Swal.fire({
        title: "Pay Refund?",
        text: `Pay KES ${refund.refundable_amount}`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes Pay",
      });

      if (!result.isConfirmed) return;

      try {
        const res = await axios.post(
          `/api/refunds/${refund.refund_id}/pay`,
          {}
        );

        Swal.fire("Success", "Refund paid", "success");

        window.open(`/receipts/${res.data.receipt_id}/print`, "_blank");

        await this.reloadRefunds();

      } catch (e) {
        Swal.fire("Error", "Payment failed", "error");
      }
    },

    async openReceipts(refund) {
      this.selectedDepositId = refund.deposit_id;
      this.receiptsLoading = true;

      const modal = new bootstrap.Modal(
        document.getElementById("receiptsModal")
      );

      modal.show();

      try {
        const res = await axios.get(
          `/api/deposits/${refund.deposit_id}/receipts`
        );

        this.receipts = res.data;

      } finally {
        this.receiptsLoading = false;
      }
    },

    printReceipt(id) {
      window.open(`/receipts/${id}/print`, "_blank");
    },

    finalizeRefund(refund) {
      axios.post(`/api/refunds/${refund.refund_id}/finalize`)
        .then(() => this.reloadRefunds());
    },

    viewBreakdown(refund) {
      this.selectedRefund = refund;

      new bootstrap.Modal(
        document.getElementById("refundBreakdownModal")
      ).show();
    },

    formatDate(date) {
      return date ? new Date(date).toLocaleDateString() : "-";
    }
  },

  mounted() {
    this.loadRefunds();
  }
};
</script>