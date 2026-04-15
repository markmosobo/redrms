<template>
  <Master>
    <section class="section dashboard">
      <div class="row">

        <!-- Deductions Card -->
        <div class="col-12">
          <div class="card top-selling overflow-auto">
            <div class="card-body pb-0">
              <h5 class="card-title">
                Deductions <span>| From Deposits & Inspections</span>
              </h5>

              <div class="row mb-3">
                <div class="col d-flex">
                  <button
                    class="btn btn-sm btn-primary rounded-pill"
                    style="background-color: darkgreen; border-color: darkgreen;"
                    @click="addDeduction"
                  >
                    Add Deduction
                  </button>
                </div>
              </div>

              <!-- TABLE -->
              <table id="DeductionsTable" class="table table-borderless">
                <thead>
                  <tr>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Unit</th>
                    <th>Inspection Date</th>
                    <th class="text-end">Total Deductions (KES)</th>
                    <th>Action</th>
                  </tr>
                </thead>

                <tbody v-if="initializing">
                  <tr>
                    <td colspan="6" class="text-center py-4">
                      <div class="spinner-border text-primary"></div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr
                    v-for="inspection in inspectionSummaries"
                    :key="inspection.inspection_id"
                  >
                    <td>{{ inspection.tenant?.full_name || 'N/A' }}</td>

                    <td>
                      {{ inspection.unit?.property?.property_name || 'N/A' }}
                    </td>

                    <td>
                      {{ inspection.unit?.unit_number || 'N/A' }}
                    </td>

                    <td>{{ formatDate(inspection.inspection_date) }}</td>

                    <td class="text-end fw-bold">
                      KES {{ (inspection.total_deductions || 0).toLocaleString() }}
                    </td>

                    <td>
                      <button
                        class="btn btn-sm btn-outline-primary"
                        @click="viewInspectionDeductions(inspection)"
                      >
                        View Deductions
                      </button>
                    </td>
                  </tr>

                  <tr v-if="inspectionSummaries.length === 0">
                    <td colspan="6" class="text-center text-muted py-4">
                      No move-out inspections with deductions found
                    </td>
                  </tr>
                </tbody>
              </table>

            </div>
          </div>
        </div>

        <!-- VIEW DEDUCTION MODAL -->
        <div class="modal fade" id="viewDeductionModal" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">

              <div class="modal-header">
                <h5 class="modal-title">Deduction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body" v-if="selectedDeduction">

                <div class="row g-3">

                  <div class="col-md-6">
                    <strong>Tenant</strong><br>
                    {{ selectedDeduction.deposit?.tenancy?.tenant?.full_name || 'N/A' }}
                  </div>

                  <div class="col-md-6">
                    <strong>Unit</strong><br>
                    {{ selectedDeduction.deposit?.tenancy?.unit?.unit_number || 'N/A' }}
                  </div>

                  <div class="col-md-6">
                    <strong>Property</strong><br>
                    {{ selectedDeduction.deposit?.tenancy?.unit?.property?.property_name || 'N/A' }}
                  </div>

                  <div class="col-md-6">
                    <strong>Deposit Amount</strong><br>
                    KES {{ selectedDeduction.deposit?.amount_received || 0 }}
                  </div>

                  <div class="col-md-6">
                    <strong>Inspection</strong><br>
                    {{ selectedDeduction.inspection?.inspection_date
                      ? formatDate(selectedDeduction.inspection.inspection_date)
                      : '-' }}
                  </div>

                  <div class="col-12 mt-2">
                    <strong>Description</strong><br>
                    {{ selectedDeduction.description || '-' }}
                  </div>

                  <div class="col-md-6 mt-2">
                    <strong>Amount</strong><br>
                    KES {{ selectedDeduction.amount || 0 }}
                  </div>

                  <div class="col-md-6 mt-2">
                    <strong>Approved By</strong><br>
                    {{ selectedDeduction.approver?.full_name || '-' }}
                  </div>

                  <div class="col-md-6 mt-2">
                    <strong>Approved At</strong><br>
                    {{ selectedDeduction.approved_at
                      ? formatDate(selectedDeduction.approved_at)
                      : '-' }}
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

        <!-- ADD/EDIT MODAL -->
        <div class="modal fade" id="addDeductionModal" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">

              <div class="modal-header">
                <h5 class="modal-title">
                  {{ deductionForm.id ? 'Edit Deduction' : 'Add Deduction' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">
                <form class="row g-3">

                  <!-- Deposit -->
                  <div class="col-md-6">
                    <label class="form-label">Deposit *</label>
                    <select v-model="deductionForm.deposit_id" class="form-select">
                      <option v-for="d in deposits" :key="d.id" :value="d.id">
                        {{ d.tenancy?.tenant?.full_name || 'N/A' }}
                        —
                        {{ d.tenancy?.unit?.unit_number || 'N/A' }}
                        —
                        KES {{ d.amount_received }}
                      </option>
                    </select>
                  </div>

                  <!-- Inspection -->
                  <div class="col-md-6">
                    <label class="form-label">Inspection (Optional)</label>
                    <select v-model="deductionForm.inspection_id" class="form-select">
                      <option value="">-- None --</option>
                      <option v-for="i in inspections" :key="i.id" :value="i.id">
                        {{ i.tenancy?.tenant?.full_name || 'N/A' }}
                        —
                        {{ formatDate(i.inspection_date) }}
                      </option>
                    </select>
                  </div>

                  <!-- Description -->
                  <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea
                      v-model="deductionForm.description"
                      class="form-control"
                      rows="2"
                    ></textarea>
                  </div>

                  <!-- Amount -->
                  <div class="col-md-6">
                    <label class="form-label">Amount *</label>
                    <input
                      type="number"
                      v-model="deductionForm.amount"
                      class="form-control"
                    />
                  </div>

                </form>
              </div>

              <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                  Close
                </button>

                <button
                  class="btn btn-success"
                  style="background: darkgreen; border-color: darkgreen;"
                  @click="submitDeduction"
                >
                  Save Deduction
                </button>
              </div>

            </div>
          </div>
        </div>

        <!-- INSPECTION DEDUCTIONS MODAL -->
        <div class="modal fade" id="inspectionDeductionsModal" tabindex="-1">
          <div class="modal-dialog modal-xl">
            <div class="modal-content">

              <div class="modal-header">
                <h5 class="modal-title">
                  Deductions — {{ selectedInspection?.tenant?.full_name || 'N/A' }}
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body" v-if="selectedInspection">

                <div class="row mb-3">
                  <div class="col-md-4">
                    <strong>Property</strong><br>
                    {{ selectedInspection.unit?.property?.property_name || 'N/A' }}
                  </div>

                  <div class="col-md-4">
                    <strong>Unit</strong><br>
                    {{ selectedInspection.unit?.unit_number || 'N/A' }}
                  </div>

                  <div class="col-md-4">
                    <strong>Inspection Date</strong><br>
                    {{ formatDate(selectedInspection.inspection_date) }}
                  </div>
                </div>

                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Description</th>
                      <th class="text-end">Amount (KES)</th>
                      <th>Status</th>
                      <th>Approved By</th>
                      <th>Approved At</th>
                      <th class="text-center">Action</th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr
                      v-for="deduction in selectedInspection.deductions"
                      :key="deduction.id"
                    >
                      <td>{{ deduction.description || '-' }}</td>

                      <td class="text-end">
                        {{ (deduction.amount || 0).toLocaleString() }}
                      </td>

                      <td>
                        <span
                          class="badge"
                          :class="{
                            'bg-warning': deduction.status === 'pending',
                            'bg-success': deduction.status === 'approved',
                            'bg-danger': deduction.status === 'rejected'
                          }"
                        >
                          {{ deduction.status }}
                        </span>
                      </td>

                      <td>{{ deduction.approver?.full_name || '-' }}</td>

                      <td>
                        {{ deduction.approved_at ? formatDate(deduction.approved_at) : '-' }}
                      </td>

                      <!-- ACTIONS -->
                      <td class="text-center">

                        <button
                          class="btn btn-sm btn-success me-1"
                          :disabled="deduction.status !== 'pending'"
                          @click="approveDeduction(deduction)"
                        >
                          Approve
                        </button>

                        <button
                          class="btn btn-sm btn-danger"
                          :disabled="deduction.status !== 'pending'"
                          @click="rejectDeduction(deduction)"
                        >
                          Reject
                        </button>

                      </td>
                    </tr>

                    <tr v-if="selectedInspection.deductions.length === 0">
                      <td colspan="6" class="text-center text-muted">
                        No deductions recorded for this inspection
                      </td>
                    </tr>
                  </tbody>

                  <tfoot>
                    <tr class="fw-bold">
                      <td>Total</td>
                      <td class="text-end">
                        KES {{ (selectedInspection.total_deductions || 0).toLocaleString() }}
                      </td>
                      <td></td>
                    </tr>
                  </tfoot>

                </table>

              </div>

              <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                  Close
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
      deductions: [],
      inspectionSummaries: [],
      deposits: [],
      inspections: [],

      selectedDeduction: null,
      selectedInspection: null,

      deductionForm: {
        id: null,
        deposit_id: null,
        inspection_id: null,
        description: "",
        amount: 0,
      },

      initializing: true,
    };
  },

  watch: {
    "deductionForm.inspection_id"(newVal) {
      if (!newVal) {
        this.deductionForm.description = "";
        return;
      }

      const inspection = this.inspections.find(i => i.id === newVal);

      if (inspection?.notes) {
        // only autofill if empty (prevents overwriting user input)
        if (!this.deductionForm.description) {
          this.deductionForm.description =
            `Based on inspection notes: ${inspection.notes}`;
        }
      }
    },
  },

  methods: {

async rejectDeduction(deduction) {
  try {
    const { value: reason, isConfirmed } = await Swal.fire({
      title: "Reject Deduction?",
      input: "textarea",
      inputLabel: "Reason (optional)",
      inputPlaceholder: "Enter rejection reason...",
      showCancelButton: true,
      confirmButtonColor: "#dc3545",
      confirmButtonText: "Reject",
    });

    if (!isConfirmed) return;

    Swal.fire({
      title: "Processing...",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    const res = await axios.post(
      `/api/deductions/${deduction.id}/reject`,
      { reason }
    );

    // =========================
    // 1. Update clicked deduction
    // =========================
    deduction.status = "rejected";
    deduction.approved_by = res.data.approved_by;
    deduction.approved_at = res.data.approved_at;
    deduction.approver = res.data.approver;

    // =========================
    // 2. Sync modal list
    // =========================
    if (this.selectedInspection?.deductions) {
      const target = this.selectedInspection.deductions.find(
        d => d.id === deduction.id
      );

      if (target) {
        target.status = "rejected";
        target.approved_by = res.data.approved_by;
        target.approved_at = res.data.approved_at;
        target.approver = res.data.approver;
      }

      // =========================
      // 3. Recalculate total live
      // =========================
      this.selectedInspection.total_deductions =
        this.selectedInspection.deductions.reduce(
          (sum, d) => sum + (d.amount || 0),
          0
        );
    }

    Swal.close();

    Swal.fire({
      icon: "success",
      title: "Rejected",
      timer: 1200,
      showConfirmButton: false,
    });

  } catch (err) {
    console.error(err);

    Swal.close();

    Swal.fire({
      icon: "error",
      title: "Rejection failed",
    });
  }
},
    /* =========================
       MODALS
    ========================= */

    viewInspectionDeductions(inspection) {
      this.selectedInspection = inspection || {
        deductions: [],
        tenant: {},
        unit: { property: {} },
        total_deductions: 0,
      };

      const modal = new bootstrap.Modal(
        document.getElementById("inspectionDeductionsModal")
      );
      modal.show();
    },

    viewDeduction(deduction) {
      this.selectedDeduction = deduction;

      const modal = new bootstrap.Modal(
        document.getElementById("viewDeductionModal")
      );
      modal.show();
    },

    addDeduction() {
      this.deductionForm = {
        id: null,
        deposit_id: null,
        inspection_id: null,
        description: "",
        amount: 0,
      };

      this.loadDeposits();
      this.loadInspections();

      const modal = new bootstrap.Modal(
        document.getElementById("addDeductionModal")
      );
      modal.show();
    },

    /* =========================
       APPROVAL
    ========================= */

    async approveDeduction(deduction) {
      try {
        const result = await Swal.fire({
          title: "Approve Deduction?",
          html: `
            <div class="text-start">
              <p><strong>Tenant:</strong> ${
                deduction.deposit?.tenancy?.tenant?.full_name || "N/A"
              }</p>
              <p><strong>Amount:</strong> KES ${deduction.amount}</p>
              <p><strong>Description:</strong><br>${deduction.description || "-"}</p>
            </div>
          `,
          icon: "question",
          showCancelButton: true,
          confirmButtonColor: "#198754",
          confirmButtonText: "Yes, Approve",
        });

        if (!result.isConfirmed) return;

        Swal.fire({
          title: "Approving...",
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading(),
        });

        const res = await axios.post(
          `/api/deductions/${deduction.id}/approve`
        );

        // =========================
        // 1. Update clicked row
        // =========================
        deduction.status = "approved";
        deduction.approved_by = res.data.approved_by;
        deduction.approved_at = res.data.approved_at;
        deduction.approver = res.data.approver;

        // =========================
        // 2. Update modal list
        // =========================
        if (this.selectedInspection?.deductions) {
          const target = this.selectedInspection.deductions.find(
            d => d.id === deduction.id
          );

          if (target) {
            target.status = "approved";
            target.approved_by = res.data.approved_by;
            target.approved_at = res.data.approved_at;
            target.approver = res.data.approver;
          }

          // =========================
          // 3. Recalculate totals live
          // =========================
          this.selectedInspection.total_deductions =
            this.selectedInspection.deductions.reduce(
              (sum, d) => sum + (d.amount || 0),
              0
            );
        }

        Swal.close();

        Swal.fire({
          icon: "success",
          title: "Approved",
          timer: 1200,
          showConfirmButton: false,
        });

      } catch (err) {
        console.error(err);

        Swal.close();

        Swal.fire({
          icon: "error",
          title: "Approval Failed",
        });
      }
    },
    /* =========================
       FORMAT HELPERS
    ========================= */

    formatDate(dateStr) {
      if (!dateStr) return "";
      const date = new Date(dateStr);
      return `${String(date.getDate()).padStart(2, "0")}/${String(
        date.getMonth() + 1
      ).padStart(2, "0")}/${date.getFullYear()}`;
    },

    /* =========================
       DATA LOADERS
    ========================= */

    async loadDeductions() {
      this.initializing = true;

      try {
        const res = await axios.get("/api/deductions?withRelations=true");
        this.deductions = Array.isArray(res.data) ? res.data : [];



      } catch (err) {
        console.error(err);
        toast.fire({ icon: "error", title: "Failed to load deductions" });
      } finally {
        this.initializing = false;
      }
    },

    async loadDeposits() {
      try {
        const res = await axios.get("/api/deposits?withTenancy=true");
        this.deposits = Array.isArray(res.data) ? res.data : [];
      } catch (err) {
        console.error(err);
      }
    },

    async loadInspections() {
      try {
        const res = await axios.get("/api/inspections?withTenancy=true");
        this.inspections = Array.isArray(res.data) ? res.data : [];
      } catch (err) {
        console.error(err);
      }
    },

    async loadInspectionSummaries() {
      this.initializing = true;

      try {
        const res = await axios.get("/api/inspections/by-inspection");

        this.inspectionSummaries = Array.isArray(res.data)
          ? res.data
          : [];

      } catch (err) {
        console.error(err);
        this.inspectionSummaries = [];
        toast.fire({
          icon: "error",
          title: "Failed to load inspection summaries",
        });

      } finally {
        // 🔥 ALWAYS stop spinner no matter what
        this.initializing = false;
      }
    },

    /* =========================
       CREATE / UPDATE
    ========================= */

    async submitDeduction() {
      try {
        if (!this.deductionForm.deposit_id || !this.deductionForm.amount) {
          toast.fire({
            icon: "warning",
            title: "Deposit and Amount required",
          });
          return;
        }

        if (this.deductionForm.id) {
          await axios.put(
            `/api/deductions/${this.deductionForm.id}`,
            this.deductionForm
          );
          toast.fire({ icon: "success", title: "Updated" });
        } else {
          await axios.post("/api/deductions", this.deductionForm);
          toast.fire({ icon: "success", title: "Created" });
        }

        const modalEl = document.getElementById("addDeductionModal");
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        this.loadInspectionSummaries();

        this.deductionForm = {
          id: null,
          deposit_id: null,
          inspection_id: null,
          description: "",
          amount: 0,
        };

      } catch (err) {
        console.error(err);
        toast.fire({ icon: "error", title: "Save failed" });
      }
    },
  },

  mounted() {
    this.loadInspectionSummaries();
  },
};
</script>