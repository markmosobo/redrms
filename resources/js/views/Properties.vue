<template>
    <Master>
        <section class="section dashboard">
          <div class="row">
    
                <!-- Top Selling -->
                <div class="col-12">
                  <div class="card top-selling overflow-auto">
    
                    <div class="filter">
                    <!--                       <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                      <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <li class="dropdown-header text-start">
                          <h6>Filter</h6>
                        </li>
    
                        <li><a class="dropdown-item" href="#">Today</a></li>
                        <li><a class="dropdown-item" href="#">This Month</a></li>
                        <li><a class="dropdown-item" href="#">This Year</a></li>
                      </ul> -->
                    </div>
    
                    <div class="card-body pb-0">
                      <h5 class="card-title">Properties <span>| Properties under REDRMS</span></h5>
                      <p class="card-text">
                        <div class="row">
                          <div class="col d-flex">
                   
                   
                                <a
                                  :href="href"
                                  :class="{ active: isActive }"
                                  class="btn btn-sm btn-primary rounded-pill"
                                  style="background-color: darkgreen; border-color: darkgreen;"
                                  @click="addProperty()"
                                >
                                  Add Property
                                </a>
                          </div>
                          <div class="col-auto d-flex justify-content-end">
                          <div class="btn-group" role="group">
                              <button id="btnGroupDrop1" type="button" style="background-color: darkgreen; border-color: darkgreen;" class="btn btn-sm btn-primary rounded-pill dropdown-toggle" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ri-add-line"></i>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                <a @click="navigateTo('/properties')" class="dropdown-item" href="#">
                                  <i class="ri-building-2-line me-2"></i>
                                  Properties
                                </a>

                                <a @click="navigateTo('/tenancies')" class="dropdown-item" href="#">
                                  <i class="ri-home-heart-line me-2"></i>
                                  Tenancies
                                </a>

                                <a @click="navigateTo('/landlords')" class="dropdown-item" href="#">
                                  <i class="ri-user-settings-line me-2"></i>
                                  Landlords
                                </a>
                              </div>
                              </div>
                            </div>
                        </div>   
            
                      </p>
    
                    <table id="PropertiesTable" class="table table-borderless">
                    <thead>
                        <tr>
                        <th>Property Name</th>
                        <th>Location</th>
                        <th>Description</th>
                        <th>Landlord</th>
                        <th>Action</th>
                        </tr>
                    </thead>
                    <tbody v-if="initializing">
                        <tr>
                            <td colspan="5" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                    <tr v-for="property in properties" :key="property.id">
                        <td>{{ property.property_name }}</td>
                        <td>{{ property.location }}</td>
                        <td>{{ property.description ?? 'N/A' }}</td>
                        <td>
                        {{ property.landlord?.full_name ?? 'N/A' }}
                        </td>

                        <td>
                        <div class="btn-group" role="group">
                            <button
                            type="button"
                            class="btn btn-sm btn-primary rounded-pill dropdown-toggle"
                            data-bs-toggle="dropdown"
                            style="background-color: darkgreen; border-color: darkgreen;"
                            >
                            Action
                            </button>

                            <div class="dropdown-menu">
                            <a @click="viewProperty(property)" class="dropdown-item" href="#">
                                <i class="ri-eye-fill mr-2"></i> View
                            </a>
                            <a @click="editProperty(property)" class="dropdown-item" href="#">
                                <i class="ri-pencil-fill mr-2"></i> Edit
                            </a>
                            <a @click="manageUnits(property)" class="dropdown-item">
                                <i class="ri-building-fill mr-2"></i> Units
                            </a>
                            <a @click="deleteProperty(property.id)" class="dropdown-item" href="#">
                                <i class="ri-delete-bin-line mr-2"></i> Delete
                            </a>
                            </div>
                        </div>
                        </td>
                    </tr>
                    </tbody>
                    </table>    
                    </div>
    
                  </div>
                </div><!-- End Top Selling -->

                <!-- View Property Modal -->
                <div
                class="modal fade"
                id="viewPropertyModal"
                tabindex="-1"
                aria-labelledby="viewPropertyModalLabel"
                aria-hidden="true"
                >
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">

                    <!-- Header -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewPropertyModalLabel">
                        View Property Details
                        </h5>
                        <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        ></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body" v-if="selectedProperty">

                        <div class="row g-3">

                        <!-- Property Name -->
                        <div class="col-md-6">
                            <strong>Property Name</strong><br>
                            {{ selectedProperty.property_name }}
                        </div>

                        <!-- Location -->
                        <div class="col-md-6">
                            <strong>Location</strong><br>
                            {{ selectedProperty.location }}
                        </div>

                        <!-- Description -->
                        <div class="col-md-12">
                            <strong>Description</strong><br>
                            {{ selectedProperty.description ?? 'N/A' }}
                        </div>

                        <!-- Landlord Name (relation) -->
                        <div class="col-md-6">
                            <strong>Landlord</strong><br>
                            {{ selectedProperty.landlord?.full_name ?? 'N/A' }}
                        </div>

                        <!-- Landlord Email -->
                        <div class="col-md-6">
                            <strong>Landlord Email</strong><br>
                            {{ selectedProperty.landlord?.email ?? 'N/A' }}
                        </div>

                        <!-- Created At -->
                        <div class="col-md-6">
                            <strong>Property Created</strong><br>
                            {{ formatDate(selectedProperty.created_at) }}
                        </div>

                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">
                        <button
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                        >
                        Close
                        </button>
                    </div>

                    </div>
                </div>
                </div>

                <!-- Add / Edit Property Modal -->
                <div
                class="modal fade"
                id="addPropertyModal"
                tabindex="-1"
                aria-labelledby="addPropertyModalLabel"
                aria-hidden="true"
                >
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">

                    <!-- Header -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPropertyModalLabel">
                        {{ data.id ? 'Edit Property' : 'Add Property' }}
                        </h5>
                        <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        ></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body">
                        <form class="row g-3">

                        <!-- Hidden ID (for edit) -->
                        <input type="hidden" v-model="data.id" />

                        <!-- Property Name -->
                        <div class="col-md-6">
                            <label class="form-label">Property Name *</label>
                            <input
                            type="text"
                            class="form-control"
                            v-model="data.property_name"
                            required
                            >
                        </div>

                        <!-- Location -->
                        <div class="col-md-6">
                            <label class="form-label">Location *</label>
                            <input
                            type="text"
                            class="form-control"
                            v-model="data.location"
                            required
                            >
                        </div>

                        <!-- Landlord -->
                        <div class="col-md-6">
                            <label class="form-label">Landlord *</label>
                            <select
                            class="form-select"
                            v-model="data.landlord_id"
                            required
                            >
                            <option value="" disabled>Select landlord</option>
                            <option
                                v-for="landlord in landlords"
                                :key="landlord.id"
                                :value="landlord.id"
                            >
                                {{ landlord.full_name }}
                            </option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea
                            class="form-control"
                            rows="3"
                            v-model="data.description"
                            ></textarea>
                        </div>

                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">
                        <button
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                        >
                        Close
                        </button>

                        <button
                        class="btn btn-success"
                        style="background: darkgreen; border-color: darkgreen;"
                        @click="submitProperty"
                        >
                        {{ data.id ? 'Update Property' : 'Save Property' }}
                        </button>
                    </div>

                    </div>
                </div>
                </div>

                <!-- Manage Units Modal -->
                <div
                class="modal fade"
                id="manageUnitsModal"
                tabindex="-1"
                aria-labelledby="manageUnitsModalLabel"
                aria-hidden="true"
                >
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">

                    <!-- Header -->
                    <div class="modal-header">
                        <h5 class="modal-title">
                        Units — {{ selectedProperty?.property_name }}
                        </h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body">

                        <!-- Add Unit Form -->
                        <div class="card mb-3">
                        <div class="card-header fw-bold">Add Unit</div>
                        <div class="card-body row g-3">

                            <div class="col-md-3">
                            <input
                                v-model="unitForm.unit_number"
                                class="form-control"
                                placeholder="Unit No"
                            >
                            </div>

                            <div class="col-md-3">
                            <input
                                v-model="unitForm.unit_type"
                                class="form-control"
                                placeholder="Type"
                            >
                            </div>

                            <div class="col-md-3">
                            <input
                                v-model="unitForm.rent_amount"
                                type="number"
                                class="form-control"
                                placeholder="Rent"
                            >
                            </div>

                            <div class="col-md-3 d-grid">
                            <button
                                class="btn btn-success"
                                @click="saveUnit"
                            >
                                Add Unit
                            </button>
                            </div>

                        </div>
                        </div>

                        <!-- Units Table -->
                        <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                            <th>Unit No</th>
                            <th>Type</th>
                            <th>Rent</th>
                            <th>Status</th>
                            <th width="120">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-if="units.length === 0">
                            <td colspan="5" class="text-center text-muted">
                                No units added yet
                            </td>
                            </tr>

                            <tr v-for="unit in units" :key="unit.id">
                            <td>{{ unit.unit_number }}</td>
                            <td>{{ unit.unit_type }}</td>
                            <td>KES {{ unit.rent_amount }}</td>
                            <td>
                                <span
                                class="badge"
                                :class="unit.status === 'vacant' ? 'bg-success' : 'bg-danger'"
                                >
                                {{ unit.status }}
                                </span>
                            </td>
                            <td>
                                <button
                                class="btn btn-sm btn-outline-danger"
                                @click="deleteUnit(unit)"
                                >
                                Delete
                                </button>
                            </td>
                            </tr>
                        </tbody>
                        </table>

                    </div>

                    <!-- Footer -->
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
    import Swal from 'sweetalert2';
    import "jquery/dist/jquery.min.js";
    import "datatables.net-dt/js/dataTables.dataTables";
    import "datatables.net-dt/css/jquery.dataTables.min.css";
    import DefaultProfile from '@/assets/img/default-profile.png'
    import $ from "jquery";
    
    const toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
    
    window.toast = toast;
    
    export default {
      data() {
        return {
            properties: [],
            units: [],
            landlords: [],
            selectedProperty: {},
            errors: {},
            unitForm: {
            unit_number: '',
            unit_type: '',
            rent_amount: ''
            },
            initializing: true,
            submitting: false,

            data: {
                id: null,
                property_name: '',
                location: '',
                landlord_id: '',
                description: ''
            },

        }
      },      
      methods: { 
        manageUnits(property) {
            this.selectedProperty = property
            this.units = []
            this.resetUnitForm()
            this.fetchUnits(property.id)

            const modal = new bootstrap.Modal(
            document.getElementById('manageUnitsModal')
            )
            modal.show()
        },

        fetchUnits(propertyId) {
            axios.get(`/api/properties/${propertyId}/units`)
            .then(res => this.units = res.data)
        },

        saveUnit() {
            axios.post(
            `/api/properties/${this.selectedProperty.id}/units`,
            this.unitForm
            ).then(res => {
            this.units.unshift(res.data)
            this.resetUnitForm()
            })
        },

        deleteUnit(unit) {
            if (!confirm('Delete this unit?')) return

            axios.delete(`/api/units/${unit.id}`)
            .then(() => {
                this.units = this.units.filter(u => u.id !== unit.id)
            })
        },

        resetUnitForm() {
            this.unitForm = {
            unit_number: '',
            unit_type: '',
            rent_amount: ''
            }
        },
        formatDate(date) {
          if (!date) return '—';

          const d = new Date(date);

          return d.toLocaleDateString('en-KE', {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
          });
        },
        viewProperty(property) {
            this.selectedProperty = { ...property }; // shallow copy
            const modal = new bootstrap.Modal(
                document.getElementById('viewPropertyModal') // a dedicated modal for viewing
            );
            modal.show();
        },
        editProperty(property) {
            this.data = { ...property }; // populate modal
            this.isViewMode = false;     // ensure inputs are editable
            const modal = new bootstrap.Modal(document.getElementById('addPropertyModal'));
            modal.show();
        },        
        addProperty(property = null) {
            if (property) {
                // Edit mode
                this.data = { ...property }; // shallow copy to avoid direct mutation
            } else {
                // Add mode
                this.resetPropertyForm();
            }

            const modal = new bootstrap.Modal(
                document.getElementById('addPropertyModal')
            );
            modal.show();
        },
        resetPropertyForm() {
    this.data = {
                id: null,
                property_name: '',
                location: '',
                landlord_id: '',
                description: ''
            };
        },
        validatePropertyForm() {
            let isValid = true;

            if (!this.data.property_name) isValid = false;
            if (!this.data.location) isValid = false;
            if (!this.data.landlord_id) isValid = false;

            return isValid;
        },
        async submitProperty() {
            if (!this.validatePropertyForm()) {
                toast.fire('Error!', 'Please fill all required fields', 'error');
                return;
            }

            this.submitting = true;

            try {
                const payload = {
                    property_name: this.data.property_name,
                    location: this.data.location,
                    landlord_id: this.data.landlord_id,
                    description: this.data.description
                };

                if (this.data.id) {
                    // UPDATE
                    await axios.put(`/api/properties/${this.data.id}`, payload);
                    toast.fire('Success!', 'Property updated successfully', 'success');
                } else {
                    // CREATE
                    await axios.post(`/api/properties`, payload);
                    toast.fire('Success!', 'Property added successfully', 'success');
                }

                const modal = bootstrap.Modal.getInstance(
                    document.getElementById('addPropertyModal')
                );
                modal.hide();

                this.resetPropertyForm();
                this.loadLists(); // reload table
            } catch (error) {
                console.error(error);
                toast.fire(
                    'Error!',
                    error.response?.data?.message || 'Something went wrong',
                    'error'
                );
            } finally {
                this.submitting = false;
            }
        },
        navigateTo(location){
            this.$router.push(location)
        },
        deleteProperty(id){
                Swal.fire({
                  title: 'Are you sure?',
                  text: "You won't be able to revert this!",
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#006400',
                  cancelButtonColor: '#FFA500',
                  confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                  if (result.isConfirmed) { 
                  //send request to the server
                  axios.delete('/api/properties/'+id).then(() => {
                  toast.fire(
                    'Deleted!',
                    'Property has been deleted.',
                    'success'
                  )
                  this.loadLists();
                  }).catch(() => {
                    Swal.fire(
                    'Failed!',
                    'There was something wrong.',
                    'warning'
                  )
                  }); 
                  }else if(result.isDenied) {
                    console.log('cancelled')
                  }
                                   
                })
        },
        loadLists() {
          this.initializing = true; // Start spinner
          axios.get('/api/properties')
            .then((response) => {
              this.properties = response.data.properties;
              this.landlords = response.data.landlords;
              console.log(response)

              setTimeout(() => {
                $("#PropertiesTable").DataTable();
              }, 10);
            })
            .catch((error) => {
              console.error('Error fetching user list:', error);
            })
            .finally(() => {
              this.initializing = false; // Stop spinner
            });
        },
      },
      components : {
          Master,
      },
      mounted(){
        this.loadLists();
        // this.user = localStorage.getItem('user');
        // this.user = JSON.parse(this.user);
        // this.userId = this.user.id;
        // this.currentUser = JSON.parse(localStorage.getItem('user')) || {};
        // this.current_user_id = this.currentUser.id;
        // this.current_user = this.currentUser.first_name + " " + this.currentUser.last_name;

      }
    }
    </script>
    
    
    