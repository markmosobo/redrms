<template>
  <Master>
    <section class="section dashboard">
      <div class="row">

        <!-- Inspections Card -->
        <div class="col-12">
          <div class="card top-selling overflow-auto">

            <div class="card-body pb-0">
              <h5 class="card-title">
                Inspections <span>| Move-in & Move-out</span>
              </h5>

              <div class="row mb-3">
                <div class="col d-flex">
                  <button
                    class="btn btn-sm btn-primary rounded-pill"
                    style="background-color: darkgreen; border-color: darkgreen;"
                    @click="addInspection"
                  >
                    Add Inspection
                  </button>
                </div>
              </div>

              <!-- Inspections Table -->
              <table id="InspectionsTable" class="table table-borderless">
                <thead>
                  <tr>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Unit</th>
                    <th>Inspection Date</th>
                    <th>Notes</th>
                    <th>Type</th>
                    <th>Created By</th>
                    <th>Action</th>
                  </tr>
                </thead>

                <!-- Spinner while loading -->
                <tbody v-if="initializing">
                  <tr>
                    <td colspan="8" class="text-center">
                      <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr v-for="inspection in inspections" :key="inspection.id">
                    <td>{{ inspection.tenancy.tenant.full_name }}</td>
                    <td>{{ inspection.tenancy.unit.property.property_name }}</td>
                    <td>{{ inspection.tenancy.unit.unit_number }}</td>
                    <td>{{ formatDate(inspection.inspection_date) }}</td>
                    <td>{{ truncateText(inspection.notes, 30) || '-' }}</td>
                    <td>
                      <span
                        class="badge"
                        :class="inspection.inspection_type === 'move_in' ? 'bg-success' : 'bg-secondary'"
                      >
                        {{ inspection.inspection_type }}
                      </span>
                    </td>
                    <td>{{ inspection.creator.full_name }}</td>
                    <td>
                      <div class="btn-group">
                        <button
                          class="btn btn-sm btn-primary dropdown-toggle"
                          data-bs-toggle="dropdown"
                        >
                          Action
                        </button>
                        <div class="dropdown-menu">
                          <a @click="viewInspection(inspection)" class="dropdown-item">
                            <i class="ri-eye-fill me-2"></i> View
                          </a>
                          <a @click="editInspection(inspection)" class="dropdown-item">
                            <i class="ri-pencil-fill me-2"></i> Edit
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

        <!-- View Inspection Modal -->
        <div class="modal fade" id="viewInspectionModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Inspection Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body" v-if="selectedInspection">
                <div class="row g-3">
                  <div class="col-md-6">
                    <strong>Tenant</strong><br>
                    {{ selectedInspection.tenancy.tenant.full_name }}
                  </div>
                  <div class="col-md-6">
                    <strong>Unit</strong><br>
                    {{ selectedInspection.tenancy.unit.unit_number }}
                  </div>
                  <div class="col-md-6">
                    <strong>Property</strong><br>
                    {{ selectedInspection.tenancy.unit.property.property_name }}
                  </div>
                  <div class="col-md-6">
                    <strong>Inspection Date</strong><br>
                    {{ formatDate(selectedInspection.inspection_date) }}
                  </div>
                  <div class="col-md-6">
                    <strong>Type</strong><br>
                    <span
                      class="badge"
                      :class="selectedInspection.inspection_type === 'move_in' ? 'bg-success' : 'bg-secondary'"
                    >
                      {{ selectedInspection.inspection_type }}
                    </span>
                  </div>
                  <div class="col-12 mt-2">
                    <strong>Notes</strong><br>
                    {{ selectedInspection.notes || '-' }}
                  </div>
                  <div class="col-md-6 mt-2">
                    <strong>Created By</strong><br>
                    {{ selectedInspection.creator.full_name }}
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Add/Edit Inspection Modal -->
        <div
        class="modal fade"
        id="addInspectionModal"
        tabindex="-1"
        aria-hidden="true"
        >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ inspectionForm.id ? 'Edit Inspection' : 'Add Inspection' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3">

                <input type="hidden" v-model="inspectionForm.id" />

                <!-- Tenancy -->
                <div class="col-md-6">
                    <label class="form-label">Tenancy *</label>
                    <select v-model="inspectionForm.tenancy_id" class="form-select">
                    <option v-for="t in tenancies" :value="t.id">
                        {{ t.tenant.full_name }} — {{ t.unit.property.property_name }} {{ t.unit.unit_number }}
                    </option>
                    </select>
                </div>

                <!-- Inspection Date -->
                <div class="col-md-6">
                    <label class="form-label">Inspection Date *</label>
                    <input type="date" v-model="inspectionForm.inspection_date" class="form-control" />
                </div>

                <!-- Inspection Type -->
                <div class="col-md-6">
                    <label class="form-label">Type *</label>
                    <select v-model="inspectionForm.inspection_type" class="form-select">
                    <option value="move_in">Move In</option>
                    <option value="move_out">Move Out</option>
                    </select>
                </div>

                <!-- Notes -->
                <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea
                    v-model="inspectionForm.notes"
                    class="form-control"
                    rows="3"
                    placeholder="List cost of deductions to be made (e.g., broken window, paint damage, missing keys)"
                ></textarea>
                </div>

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button
                class="btn btn-success"
                style="background: darkgreen; border-color: darkgreen;"
                @click="submitInspection"
                >
                Save Inspection
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
        inspections: [],
        selectedInspection: null,
        tenancies: [],
        inspectionForm: {
        id: null,
        tenancy_id: null,
        inspection_date: new Date().toISOString().slice(0, 10),
        inspection_type: "move_out",
        notes: "",
        },      
        initializing: true,
    };
  },
  methods: {
    // Format date as DD/MM/YYYY
    formatDate(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-based
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    },

    // Truncate text to a given length (default 50 chars)
    truncateText(text, maxLength = 50) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },    
    async loadInspections() {
      this.initializing = true;
      try {
        const response = await axios.get("/api/inspections?withTenancy=true");
        this.inspections = response.data;
        setTimeout(() => {
          $("#InspectionsTable").DataTable();
        }, 10);
      } catch (err) {
        console.error("Error fetching inspections:", err);
        toast.fire({ icon: "error", title: "Failed to load inspections" });
      } finally {
        this.initializing = false;
      }
    },
    async loadTenancies() {
        try {
        const response = await axios.get("/api/tenancies");
        this.tenancies = Array.isArray(response.data) ? response.data : [];
        } catch (err) {
        console.error("Failed to load tenancies:", err);
        toast.fire({ icon: "error", title: "Failed to load tenancies" });
        }
    },

    addInspection() {
        this.inspectionForm = {
        id: null,
        tenancy_id: null,
        inspection_date: new Date().toISOString().slice(0, 10),
        inspection_type: "move_out",
        notes: "",
        };
        this.loadTenancies();
        const modal = new bootstrap.Modal(document.getElementById("addInspectionModal"));
        modal.show();
    },

    async submitInspection() {
        try {
        if (!this.inspectionForm.tenancy_id || !this.inspectionForm.inspection_date) {
            toast.fire({ icon: "warning", title: "Tenancy and Inspection Date are required" });
            return;
        }

        if (this.inspectionForm.id) {
            // Update
            await axios.put(`/api/inspections/${this.inspectionForm.id}`, this.inspectionForm);
            toast.fire({ icon: "success", title: "Inspection updated" });
        } else {
            // Create
            await axios.post("/api/inspections", this.inspectionForm);
            toast.fire({ icon: "success", title: "Inspection added" });
        }

        const modalEl = document.getElementById("addInspectionModal");
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        this.loadInspections();

        // Reset form
        this.inspectionForm = {
            id: null,
            tenancy_id: null,
            inspection_date: new Date().toISOString().slice(0, 10),
            inspection_type: "move_out",
            notes: "",
        };
        } catch (err) {
        console.error(err);
        toast.fire({ icon: "error", title: "Failed to save inspection" });
        }
    },
    viewInspection(inspection) {
      this.selectedInspection = inspection;
      const modal = new bootstrap.Modal(document.getElementById("viewInspectionModal"));
      modal.show();
    },

    editInspection(inspection) {
      toast.fire({ icon: "info", title: "Inspection editing not implemented yet" });
    },

  },
  mounted() {
    this.loadInspections();
  },
};
</script>