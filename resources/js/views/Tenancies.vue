<template>
  <Master>
    <section class="section dashboard">
      <div class="row">

        <!-- Tenancies Card -->
        <div class="col-12">
          <div class="card top-selling overflow-auto">

            <div class="card-body pb-0">
              <h5 class="card-title">
                Tenancies <span>| Active & Past Occupancies</span>
              </h5>

              <div class="row mb-3">
                <div class="col d-flex">
                  <button
                    class="btn btn-sm btn-primary rounded-pill"
                    style="background-color: darkgreen; border-color: darkgreen;"
                    @click="addTenancy"
                  >
                    Add Tenancy
                  </button>
                </div>
                <div class="col-auto d-flex justify-content-end">
                  <div class="btn-group" role="group">
                    <button
                      type="button"
                      class="btn btn-sm btn-primary rounded-pill dropdown-toggle"
                      style="background-color: darkgreen; border-color: darkgreen;"
                      data-bs-toggle="dropdown"
                      aria-haspopup="true"
                      aria-expanded="false"
                    >
                      <i class="ri-add-line"></i>
                    </button>
                    <div class="dropdown-menu">
                      <a @click="navigateTo('/properties')" class="dropdown-item">
                        <i class="ri-building-2-line me-2"></i> Properties
                      </a>
                      <a @click="navigateTo('/tenancies')" class="dropdown-item">
                        <i class="ri-home-heart-line me-2"></i> Tenancies
                      </a>
                      <a @click="navigateTo('/landlords')" class="dropdown-item">
                        <i class="ri-user-settings-line me-2"></i> Landlords
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Tenancies Table -->
              <table id="TenanciesTable" class="table table-borderless">
                <thead>
                  <tr>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Unit</th>
                    <th>Start Date</th>
                    <th>Deposit</th>
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
                  <tr v-for="tenancy in tenancies" :key="tenancy.id">
                    <td>{{ tenancy.tenant.full_name }}</td>
                    <td>{{ tenancy.unit.property.property_name }}</td>
                    <td>{{ tenancy.unit.unit_number }}</td>
                    <td>{{ tenancy.start_date }}</td>
                    <td>KES {{ tenancy.deposit_amount }}</td>
                    <td>
                      <span
                        class="badge"
                        :class="tenancy.status === 'active' ? 'bg-success' : 'bg-secondary'"
                      >
                        {{ tenancy.status }}
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
                          <a @click="viewTenancy(tenancy)" class="dropdown-item">
                            <i class="ri-eye-fill me-2"></i> View
                          </a>
                          <a @click="editTenancy(tenancy)" class="dropdown-item">
                            <i class="ri-pencil-fill me-2"></i> Edit
                          </a>
                          <a
                            v-if="tenancy.status === 'active'"
                            @click="terminateTenancy(tenancy)"
                            class="dropdown-item text-danger"
                          >
                            <i class="ri-close-circle-line me-2"></i> Terminate
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

        <!-- View Tenancy Modal -->
        <div
          class="modal fade"
          id="viewTenancyModal"
          tabindex="-1"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Tenancy Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body" v-if="selectedTenancy">
                <div class="row g-3">
                  <div class="col-md-6">
                    <strong>Tenant</strong><br>
                    {{ selectedTenancy.tenant.full_name }}
                  </div>
                  <div class="col-md-6">
                    <strong>Unit</strong><br>
                    {{ selectedTenancy.unit.unit_number }}
                  </div>
                  <div class="col-md-6">
                    <strong>Property</strong><br>
                    {{ selectedTenancy.unit.property.property_name }}
                  </div>
                  <div class="col-md-6">
                    <strong>Deposit</strong><br>
                    KES {{ selectedTenancy.deposit_amount }}
                  </div>
                  <div class="col-md-6">
                    <strong>Start Date</strong><br>
                    {{ selectedTenancy.start_date }}
                  </div>
                  <div class="col-md-6">
                    <strong>Status</strong><br>
                    <span class="badge bg-success">{{ selectedTenancy.status }}</span>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Add/Edit Tenancy Modal -->
        <div
          class="modal fade"
          id="addTenancyModal"
          tabindex="-1"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">{{ tenancyForm.id ? 'Edit Tenancy' : 'Add Tenancy' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <form class="row g-3">

                  <input type="hidden" v-model="tenancyForm.id" />

                  <div class="col-md-6">
                    <label class="form-label">Tenant *</label>
                    <select v-model="tenancyForm.tenant_id" class="form-select">
                      <option v-for="t in tenants" :value="t.id">{{ t.full_name }}</option>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Unit *</label>
                    <select v-model="tenancyForm.unit_id" class="form-select">
                      <option v-for="u in vacantUnits" :value="u.id">
                        {{ u.property.property_name }} — {{ u.unit_number }}
                      </option>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="date" v-model="tenancyForm.start_date" class="form-control">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Deposit</label>
                    <input type="number" v-model="tenancyForm.deposit_amount" class="form-control">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select v-model="tenancyForm.status" class="form-select">
                      <option value="active">Active</option>
                      <option value="terminated">Terminated</option>
                    </select>
                  </div>

                </form>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button
                  class="btn btn-success"
                  style="background: darkgreen; border-color: darkgreen;"
                  @click="submitTenancy"
                >
                  Save Tenancy
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
      tenancies: [],
      tenants: [],
      vacantUnits: [],
      selectedTenancy: null,
      tenancyForm: {
        id: null,
        tenant_id: null,
        unit_id: null,
        start_date: "",
        deposit_amount: 0,
        status: "active",
      },
      initializing: true,
    };
  },
  methods: {
    async loadTenancies() {
        this.initializing = true; // Start spinner
        axios.get('/api/tenancies')
        .then((response) => {
            this.tenancies = response.data;
            console.log(response)

            setTimeout(() => {
            $("#TenanciesTable").DataTable();
            }, 10);
        })
        .catch((error) => {
            console.error('Error fetching list:', error);
        })
        .finally(() => {
            this.initializing = false; // Stop spinner
        });
    },    

    // Load tenants & vacant units for form selects
    async loadFormOptions() {
    try {
        // Parallel requests for tenants and vacant units
        const [tenantsRes, unitsRes] = await Promise.all([
        axios.get("/api/tenants"),
        axios.get("/api/units/vacant"),
        ]);

        // Assign results safely
        this.tenants = Array.isArray(tenantsRes.data) ? tenantsRes.data: [];
        this.vacantUnits = Array.isArray(unitsRes.data) ? unitsRes.data : [];

    } catch (err) {
        console.error("Error loading form options:", err);

        // Optional: show a toast to the user
        if (window.toast) {
        window.toast.fire({
            icon: "error",
            title: "Failed to load form options. Please try again."
        });
        }

        // Fallback: empty arrays to avoid template errors
        this.tenants = [];
        this.vacantUnits = [];
    }
    },

    // Open Add Tenancy Modal
    addTenancy() {
      this.tenancyForm = {
        id: null,
        tenant_id: null,
        unit_id: null,
        start_date: new Date().toISOString().slice(0, 10),
        deposit_amount: 0,
        status: "active",
      };
      this.loadFormOptions();
      const modal = new bootstrap.Modal(document.getElementById('addTenancyModal'));
      modal.show();
    },

    // Open Edit Tenancy Modal
    editTenancy(tenancy) {
      this.tenancyForm = { ...tenancy, tenant_id: tenancy.tenant.id, unit_id: tenancy.unit.id };
      this.loadFormOptions();
      const modal = new bootstrap.Modal(document.getElementById('addTenancyModal'));
      modal.show();
    },

    // Open View Tenancy Modal
    viewTenancy(tenancy) {
      this.selectedTenancy = tenancy;
      const modal = new bootstrap.Modal(document.getElementById('viewTenancyModal'));
      modal.show();
    },

    // Submit Add/Edit Tenancy
    async submitTenancy() {
    try {
        if (!this.tenancyForm.tenant_id || !this.tenancyForm.unit_id) {
        toast.fire({ icon: "warning", title: "Tenant and Unit are required" });
        return;
        }

        if (this.tenancyForm.id) {
        // Update tenancy
        await axios.put(`/api/tenancies/${this.tenancyForm.id}`, this.tenancyForm);
        toast.fire({ icon: "success", title: "Tenancy updated" });
        } else {
        // Create tenancy
        await axios.post("/api/tenancies", this.tenancyForm);
        toast.fire({ icon: "success", title: "Tenancy created" });
        }

        // Hide modal properly
        const modalEl = document.getElementById('addTenancyModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        this.loadTenancies();

        // Optional: reset form
        this.tenancyForm = { tenant_id: "", unit_id: "", start_date: "", end_date: "" };
    } catch (err) {
        console.error(err);
        toast.fire({ icon: "error", title: "Failed to save tenancy" });
    }
    },

    // Terminate tenancy
    async terminateTenancy(tenancy) {
    try {
        const result = await Swal.fire({
        title: "Are you sure?",
        text: "This will terminate the tenancy!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, terminate",
        cancelButtonText: "Cancel",
        });

        if (result.isConfirmed) {
        await axios.post(`/api/tenancies/${tenancy.id}/terminate`);
        tenancy.status = "terminated";
        toast.fire({ icon: "success", title: "Tenancy terminated" });
        }
    } catch (err) {
        console.error(err);
        toast.fire({ icon: "error", title: "Failed to terminate tenancy" });
    }
    },

    // Navigation helper
    navigateTo(path) {
      this.$router.push(path);
    },
  },
  mounted() {
    this.loadTenancies();
  },
};
</script>