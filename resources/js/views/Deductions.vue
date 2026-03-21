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

              <!-- Deductions Table -->
              <table id="DeductionsTable" class="table table-borderless">
                <thead>
                  <tr>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Unit</th>
                    <th>Deposit Amount</th>
                    <th>Inspection</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Approved By</th>
                    <th>Approved At</th>
                    <th>Action</th>
                  </tr>
                </thead>

                <tbody v-if="initializing">
                  <tr>
                    <td colspan="10" class="text-center">
                      <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr v-for="deduction in deductions" :key="deduction.id">
                    <td>{{ deduction.deposit.tenancy.tenant.full_name }}</td>
                    <td>{{ deduction.deposit.tenancy.unit.property.property_name }}</td>
                    <td>{{ deduction.deposit.tenancy.unit.unit_number }}</td>
                    <td>KES {{ deduction.deposit.amount_received }}</td>
                    <td>
                      {{ deduction.inspection ? formatDate(deduction.inspection.inspection_date) : '-' }}
                    </td>
                    <td>{{ truncateText(deduction.description, 30) || '-' }}</td>
                    <td>KES {{ deduction.amount }}</td>
                    <td>{{ deduction.approver ? deduction.approver.full_name : '-' }}</td>
                    <td>{{ deduction.approved_at ? formatDate(deduction.approved_at) : '-' }}</td>
                    <td>
                      <div class="btn-group">
                        <button class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                          Action
                        </button>
                        <div class="dropdown-menu">
                          <a @click="viewDeduction(deduction)" class="dropdown-item">
                            <i class="ri-eye-fill me-2"></i> View
                          </a>
                            <a
                            v-if="!deduction.approved_at"
                            @click="approveDeduction(deduction)"
                            class="dropdown-item"
                            >
                            <i class="ri-check-double-line me-2"></i> Approve
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

        <!-- View Deduction Modal -->
        <div class="modal fade" id="viewDeductionModal" tabindex="-1" aria-hidden="true">
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
                    {{ selectedDeduction.deposit.tenancy.tenant.full_name }}
                  </div>
                  <div class="col-md-6">
                    <strong>Unit</strong><br>
                    {{ selectedDeduction.deposit.tenancy.unit.unit_number }}
                  </div>
                  <div class="col-md-6">
                    <strong>Property</strong><br>
                    {{ selectedDeduction.deposit.tenancy.unit.property.property_name }}
                  </div>
                  <div class="col-md-6">
                    <strong>Deposit Amount</strong><br>
                    KES {{ selectedDeduction.deposit.amount_received }}
                  </div>
                  <div class="col-md-6">
                    <strong>Inspection</strong><br>
                    {{ selectedDeduction.inspection ? formatDate(selectedDeduction.inspection.inspection_date) : '-' }}
                  </div>
                  <div class="col-12 mt-2">
                    <strong>Description</strong><br>
                    {{ selectedDeduction.description || '-' }}
                  </div>
                  <div class="col-md-6 mt-2">
                    <strong>Amount</strong><br>
                    KES {{ selectedDeduction.amount }}
                  </div>
                  <div class="col-md-6 mt-2">
                    <strong>Approved By</strong><br>
                    {{ selectedDeduction.approver ? selectedDeduction.approver.full_name : '-' }}
                  </div>
                  <div class="col-md-6 mt-2">
                    <strong>Approved At</strong><br>
                    {{ selectedDeduction.approved_at ? formatDate(selectedDeduction.approved_at) : '-' }}
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Add/Edit Deduction Modal -->
        <div class="modal fade" id="addDeductionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ deductionForm.id ? 'Edit Deduction' : 'Add Deduction' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3">

                <input type="hidden" v-model="deductionForm.id" />

                <!-- Deposit -->
                <div class="col-md-6">
                    <label class="form-label">Deposit *</label>
                    <select v-model="deductionForm.deposit_id" class="form-select">
                    <option v-for="d in deposits" :key="d.id" :value="d.id">
                        {{ d.tenancy.tenant.full_name }} — {{ d.tenancy.unit.unit_number }} — KES {{ d.amount_received }}
                    </option>
                    </select>
                </div>

                <!-- Optional Inspection -->
                <div class="col-md-6">
                    <label class="form-label">Inspection (Optional)</label>
                    <select v-model="deductionForm.inspection_id" class="form-select">
                    <option value="">-- None --</option>
                    <option v-for="i in inspections" :key="i.id" :value="i.id">
                        {{ i.tenancy.tenant.full_name }} — {{ formatDate(i.inspection_date) }}
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
                    placeholder="Briefly describe the deduction (e.g., repair of window, cleaning fees)"
                    ></textarea>
                </div>

                <!-- Amount -->
                <div class="col-md-6">
                    <label class="form-label">Amount *</label>
                    <input type="number" v-model="deductionForm.amount" class="form-control" />
                </div>

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
      selectedDeduction: null,
      deposits: [],
      inspections: [],
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
    'deductionForm.inspection_id'(newVal) {
        if (!newVal) {
        // No inspection selected → clear description
        this.deductionForm.description = '';
        return;
        }

        const inspection = this.inspections.find(i => i.id === newVal);
        if (inspection && inspection.notes) {
        // Prefill description with inspection notes as guidance
        this.deductionForm.description = `Based on inspection notes: ${inspection.notes}`;
        } else {
        this.deductionForm.description = '';
        }
    }
    },  
    methods: {
    async approveDeduction(deduction) {
    try {
        const result = await Swal.fire({
        title: 'Approve Deduction?',
        html: `
            <div class="text-start">
            <p><strong>Tenant:</strong> ${deduction.deposit.tenancy.tenant.full_name}</p>
            <p><strong>Amount:</strong> KES ${deduction.amount}</p>
            <p><strong>Description:</strong><br>${deduction.description || '-'}</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Approve',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#198754',
        reverseButtons: true,
        });

        if (!result.isConfirmed) return;

        // Show loading
        Swal.fire({
        title: 'Approving...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
        });

        // Call backend
        const response = await axios.post(
        `/api/deductions/${deduction.id}/approve`
        );

        // Update local state (no reload needed)
        deduction.approved_by = response.data.approved_by;
        deduction.approved_at = response.data.approved_at;
        deduction.approver = response.data.approver;

        Swal.fire({
        icon: 'success',
        title: 'Approved!',
        text: 'Deduction has been approved successfully.',
        timer: 2000,
        showConfirmButton: false,
        });

    } catch (error) {
        console.error(error);
        Swal.fire({
        icon: 'error',
        title: 'Approval Failed',
        text: 'Something went wrong while approving the deduction.',
        });
    }
    },    
    formatDate(dateStr) {
      if (!dateStr) return "";
      const date = new Date(dateStr);
      const day = String(date.getDate()).padStart(2, "0");
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const year = date.getFullYear();
      return `${day}/${month}/${year}`;
    },

    truncateText(text, maxLength = 50) {
      if (!text) return "";
      return text.length > maxLength ? text.substring(0, maxLength) + "..." : text;
    },

    async loadDeductions() {
      this.initializing = true;
      try {
        const response = await axios.get("/api/deductions?withRelations=true");
        this.deductions = response.data;
        setTimeout(() => $("#DeductionsTable").DataTable(), 10);
      } catch (err) {
        console.error("Failed to load deductions:", err);
        toast.fire({ icon: "error", title: "Failed to load deductions" });
      } finally {
        this.initializing = false;
      }
    },

    async loadDeposits() {
      try {
        const response = await axios.get("/api/deposits?withTenancy=true");
        this.deposits = Array.isArray(response.data) ? response.data : [];
      } catch (err) {
        console.error("Failed to load deposits:", err);
      }
    },

    async loadInspections() {
      try {
        const response = await axios.get("/api/inspections?withTenancy=true");
        this.inspections = Array.isArray(response.data) ? response.data : [];
      } catch (err) {
        console.error("Failed to load inspections:", err);
      }
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
      const modal = new bootstrap.Modal(document.getElementById("addDeductionModal"));
      modal.show();
    },

    async submitDeduction() {
      try {
        if (!this.deductionForm.deposit_id || !this.deductionForm.amount) {
          toast.fire({ icon: "warning", title: "Deposit and Amount are required" });
          return;
        }

        if (this.deductionForm.id) {
          await axios.put(`/api/deductions/${this.deductionForm.id}`, this.deductionForm);
          toast.fire({ icon: "success", title: "Deduction updated" });
        } else {
          await axios.post("/api/deductions", this.deductionForm);
          toast.fire({ icon: "success", title: "Deduction added" });
        }

        const modalEl = document.getElementById("addDeductionModal");
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        this.loadDeductions();

        // Reset form
        this.deductionForm = { id: null, deposit_id: null, inspection_id: null, description: "", amount: 0 };
      } catch (err) {
        console.error(err);
        toast.fire({ icon: "error", title: "Failed to save deduction" });
      }
    },

    viewDeduction(deduction) {
      this.selectedDeduction = deduction;
      const modal = new bootstrap.Modal(document.getElementById("viewDeductionModal"));
      modal.show();
    },

    editDeduction(deduction) {
      toast.fire({ icon: "info", title: "Deduction editing not implemented yet" });
    },
  },
  mounted() {
    this.loadDeductions();
  },
};
</script>