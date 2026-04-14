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
                      <h5 class="card-title">Managers <span>| Managers who oversee properties under REDRMS</span></h5>
                      <p class="card-text">
                        <div class="row">
                          <div class="col d-flex">
                   
                   
                                <a
                                  :href="href"
                                  :class="{ active: isActive }"
                                  class="btn btn-sm btn-primary rounded-pill"
                                  style="background-color: darkgreen; border-color: darkgreen;"
                                  @click="addManager()"
                                >
                                  Add Manager
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
    
                      <table id="ManagersTable" class="table table-borderless">
                        <thead>
                          <tr>
                            <th scope="col">Full Name</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Email</th>
                            <th scope="col">Status</th>
                            <th scope="col">Properties</th>
                            <th scope="col">Action</th>
                          </tr>
                        </thead>
                        <!-- Spinner shown while data is initializing -->
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
                          <tr v-for="item in managers" :key="item.id">
                            <td>{{item.full_name}}</td>
                            <td>{{item.phone ?? "N/A"}}</td>
                            <td>{{item.email ?? "N/A"}}</td>
                            <td>
                              <span v-if="item.status == 'active'" class="badge bg-success">Active</span>
                              <span v-else class="badge bg-secondary">Inactive</span>
                            </td>
                            <td>
                              <span class="badge bg-primary">
                                {{ item.managed_properties_count }}
                              </span>
                            </td>
                            <td>
                              <div class="btn-group" role="group">
                                  <button id="btnGroupDrop1" type="button" style="background-color: darkgreen; border-color: darkgreen;" class="btn btn-sm btn-primary rounded-pill dropdown-toggle" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                  Action
                                  </button>
                                  <div class="dropdown-menu">
                                    <a @click="viewManager(item)" class="dropdown-item">
                                      <i class="ri-eye-fill mr-2"></i> View
                                    </a>

                                    <a @click="editManager(item)" class="dropdown-item">
                                      <i class="ri-pencil-fill mr-2"></i> Edit
                                    </a>

                                    <a @click="manageProperties(item)" class="dropdown-item">
                                      <i class="ri-building-2-fill mr-2"></i>Assign Properties
                                    </a>

                                    <a @click="deleteManager(item.id)" class="dropdown-item">
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

                <!-- View Manager Modal -->
                <div
                  class="modal fade"
                  id="viewManagerModal"
                  tabindex="-1"
                  aria-labelledby="viewManagerModalLabel"
                  aria-hidden="true"
                >
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">

                      <div class="modal-header">
                        <h5 class="modal-title" id="viewManagerModalLabel">
                          View Manager Details
                        </h5>
                        <button
                          type="button"
                          class="btn-close"
                          data-bs-dismiss="modal"
                        ></button>
                      </div>

                      <div class="modal-body" v-if="selectedManager">

                        <div class="row g-3">

                          <!-- Full Name -->
                          <div class="col-md-6" v-if="selectedManager.full_name">
                            <strong>Full Name</strong><br>
                            {{ selectedManager.full_name }}
                          </div>

                          <!-- Email -->
                          <div class="col-md-6">
                            <strong>Email</strong><br>
                            {{ selectedManager.email }}
                          </div>

                          <!-- Phone -->
                          <div class="col-md-6" v-if="selectedManager.phone">
                            <strong>Phone</strong><br>
                            {{ selectedManager.phone }}
                          </div>

                          <!-- Role -->
                          <div class="col-md-6">
                            <strong>Role</strong><br>
                            <span class="badge bg-primary text-uppercase">
                              {{ selectedManager.role }}
                            </span>
                          </div>

                          <!-- Status -->
                          <div class="col-md-6">
                            <strong>Status</strong><br>
                            <span
                              class="badge"
                              :class="selectedManager.status === 'active'
                                ? 'bg-success'
                                : 'bg-secondary'"
                            >
                              {{ selectedManager.status }}
                            </span>
                          </div>

                          <!-- Email Verification -->
                          <div class="col-md-6">
                            <strong>Email Verified</strong><br>
                            <span v-if="selectedManager.email_verified_at" class="text-success">
                              Yes
                            </span>
                            <span v-else class="text-danger">
                              No
                            </span>
                          </div>

                          <!-- Created At -->
                          <div class="col-md-6">
                            <strong>Account Created</strong><br>
                            {{ formatDate(selectedManager.created_at) }}
                          </div>

                        </div>
                      </div>

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

                <!-- Add Manager Modal -->
                <div
                  class="modal fade"
                  id="addManagerModal"
                  tabindex="-1"
                  aria-labelledby="addManagerModalLabel"
                  aria-hidden="true"
                >
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">

                      <!-- Header -->
                      <div class="modal-header">
                        <h5 class="modal-title" id="addManagerModalLabel">
                          Add Manager
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

                          <!-- Hidden ID (for edit reuse) -->
                          <input type="hidden" v-model="data.id" />

                          <!-- Full Name -->
                          <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input
                              type="text"
                              class="form-control"
                              v-model="data.full_name"
                              required
                            >
                          </div>

                          <!-- Email -->
                          <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input
                              type="email"
                              class="form-control"
                              v-model="data.email"
                              required
                            >
                          </div>

                          <!-- Phone -->
                          <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input
                              type="text"
                              class="form-control"
                              v-model="data.phone"
                            >
                          </div>

                          <!-- Status -->
                          <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select
                              class="form-select"
                              v-model="data.status"
                            >
                              <option value="active">Active</option>
                              <option value="inactive">Inactive</option>
                            </select>
                          </div>

                          <!-- Password (create only) -->
                          <div class="col-md-6" v-if="!data.id">
                            <label class="form-label">Temporary Password</label>

                            <div class="input-group">
                              <input
                                type="text"
                                class="form-control"
                                v-model="data.password"
                                readonly
                              >
                              <button
                                class="btn btn-outline-secondary"
                                type="button"
                                @click="copyPassword"
                              >
                                Copy
                              </button>
                            </div>

                            <small class="text-muted">
                              User will be required to change this password on first login.
                            </small>
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
                          @click="submit"
                        >
                          Save Manager
                        </button>
                      </div>

                    </div>
                  </div>
                </div>

                <!-- EDIT Manager MODAL -->
                <div
                  class="modal fade"
                  id="editManagerModal"
                  tabindex="-1"
                  aria-labelledby="editManagerModalLabel"
                  aria-hidden="true"
                >
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">

                      <!-- Header -->
                      <div class="modal-header">
                        <h5 class="modal-title" id="editManagerModalLabel">
                          Edit Manager
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

                          <!-- Hidden ID -->
                          <input type="hidden" v-model="form.id" />

                          <!-- Full Name -->
                          <div class="col-md-12">
                            <label class="form-label">Full Name *</label>
                            <input
                              type="text"
                              class="form-control"
                              v-model="form.full_name"
                              required
                            >
                          </div>

                          <!-- Email -->
                          <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input
                              type="email"
                              class="form-control"
                              v-model="form.email"
                              required
                            >
                          </div>

                          <!-- Phone -->
                          <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input
                              type="text"
                              class="form-control"
                              v-model="form.phone"
                            >
                          </div>

                          <!-- Status -->
                          <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select
                              class="form-select"
                              v-model="form.status"
                            >
                              <option value="active">Active</option>
                              <option value="inactive">Inactive</option>
                            </select>
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
                          @click="submitChanges"
                        >
                          Save Changes
                        </button>
                      </div>

                    </div>
                  </div>
                </div>

                <!-- Assign Properties Modal -->
                <div
                  class="modal fade"
                  id="managePropertiesModal"
                  tabindex="-1"
                >
                  <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">

                      <div class="modal-header">
                        <h5 class="modal-title">
                          Properties — {{ selectedManager?.full_name }}
                        </h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                      </div>

                      <div class="modal-body">

                        <!-- Add Property -->
                        <div class="card mb-3">
                          <div class="card-header fw-bold">
                            Assign Properties
                          </div>

                          <div class="card-body">

                            <div class="row g-3">

                              <div class="col-md-9">
                                <select v-model="selectedPropertyIds" class="form-control" multiple>
                                  <option
                                    v-for="property in allProperties"
                                    :key="property.id"
                                    :value="property.id"
                                  >
                                    {{ property.property_name }} - {{ property.location }}
                                  </option>
                                </select>
                              </div>

                              <div class="col-md-3 d-grid">
                                <button class="btn btn-success" @click="assignProperties">
                                  Assign
                                </button>
                              </div>

                            </div>

                          </div>
                        </div>

                        <!-- Properties Table -->
                        <table class="table table-bordered table-striped">
                          <thead class="table-dark">
                            <tr>
                              <th>Name</th>
                              <th>Location</th>
                              <th>Units</th>
                              <th width="180">Actions</th>
                            </tr>
                          </thead>

                          <tbody>
                            <tr v-if="properties.length === 0">
                              <td colspan="4" class="text-center text-muted">
                                No properties added yet
                              </td>
                            </tr>

                            <tr v-for="property in properties" :key="property.id">
                              <td>{{ property.property_name }}</td>
                              <td>{{ property.location }}</td>
                              <td>{{ property.units_count ?? 0 }}</td>
                              <td>
                                <button
                                  class="btn btn-sm btn-outline-primary"
                                  @click="manageUnits(property)"
                                >
                                  Units
                                </button>

                                <button
                                  class="btn btn-sm btn-outline-danger"
                                  @click="deleteProperty(property)"
                                >
                                  Delete
                                </button>
                              </td>
                            </tr>
                          </tbody>
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
            managers: [],
            selectedManager: {},
            properties: [],
            allProperties: [],       // all available properties
            selectedPropertyIds: [],
            propertyForm: {
              property_name: '',
              location: '',
              description: ''
            },
            errors: {},
            initializing: true,
            submitting: false,

            data: {
              id: null,
              full_name: '',
              email: '',
              phone: '',
              status: 'active',
              role: 'manager',
              password: ''
            },

            form: {        // EDIT Manager
              id: null,
              full_name: '',
              email: '',
              phone: '',
              status: '',
              role: 'manager',
              password: ''
            }
        }
      },      
      methods: {
        copyPassword() {
          navigator.clipboard.writeText(this.data.password);

          window.toast.fire({
            icon: 'success',
            title: 'Temporary password copied to clipboard'
          });
        },         
        generateTempPassword(length = 10) {
          const chars =
            'ABCDEFGHJKLMNEME9M9cSy9FvfHvcx2gMPkp1H5Dj4YaKufPRsAyon8Tf!@#$';
          let password = '';
          for (let i = 0; i < length; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
          }
          return password;
        },        
        manageProperties(manager) {
          this.selectedManager = manager
          this.selectedPropertyIds = []

          this.fetchProperties(manager.id)
          this.fetchAllProperties()

          new bootstrap.Modal(
            document.getElementById('managePropertiesModal')
          ).show()
        },
        fetchProperties(managerId) {
          axios.get(`/api/managers/${managerId}/properties`)
            .then(res => this.properties = res.data)
            console.log("maja", this.properties)
        },
        fetchAllProperties() {
          axios.get('/api/properties')
            .then(res => this.allProperties = res.data.properties)
        },
        assignProperties() {
          axios.post(`/api/managers/${this.selectedManager.id}/assign-properties`, {
            property_ids: this.selectedPropertyIds
          })
          .then(() => {

            this.fetchProperties(this.selectedManager.id)

            window.toast.fire({
              icon: 'success',
              title: 'Properties assigned successfully'
            })

            this.selectedPropertyIds = []
          })
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
        viewManager(item)
        {
          console.log(this.selectedManager)
          this.selectedManager = item;
          // Show the modal after fetching data
          const modal = new bootstrap.Modal(document.getElementById('viewManagerModal'));
          modal.show();
        },
        editManager(item) {
        this.form = {
            id: item.id,
            full_name: item.full_name,
            email: item.email,
            phone: item.phone,
            status: item.status
        };

        const modal = new bootstrap.Modal(
            document.getElementById('editManagerModal')
        );
        modal.show();
        },

        async submitChanges() {
          if (!this.form.full_name || !this.form.email) {
            toast.fire('Error!', 'Full Name and Email are required', 'error');
            return;
          }

          this.submitting = true;

          try {
            const payload = {
              full_name: this.form.full_name,
              email: this.form.email,
              phone: this.form.phone,
              status: this.form.status,
              role: 'manager', // Always force role
            };

            await axios.put(`/api/users/${this.form.id}`, payload);

            toast.fire('Success!', 'Manager updated successfully', 'success');

            // Close modal
            const modal = bootstrap.Modal.getInstance(
              document.getElementById('editManagerModal')
            );
            modal.hide();

            // Reset form
            this.resetForm();

            // Reload managers list
            this.loadLists();

          } catch (err) {
            console.error(err);
            toast.fire('Error!', err.response?.data?.message || 'Update failed', 'error');
          } finally {
            this.submitting = false;
          }
        },

        resetForm() {
          this.form = {
            id: null,
            full_name: '',
            email: '',
            phone: '',
            status: 'active',
            role: 'manager'
          };
        },

        addManager()
        {
          this.data = {
            id: null,
            full_name: '',
            email: '',
            phone: '',
            status: 'active',
            password: this.generateTempPassword()
          };          
          // Show the modal after fetching data
          const modal = new bootstrap.Modal(document.getElementById('addManagerModal'));
          modal.show();
        },
        validateForm() {
          let isValid = true;

          // Full name
          if (!this.data.full_name) {
            isValid = false;
            document.querySelector('[v-model="data.full_name"]')?.classList.add('is-invalid');
          }

          // Email
          if (!this.data.email) {
            isValid = false;
            document.querySelector('[v-model="data.email"]')?.classList.add('is-invalid');
          }

          return isValid;
        },

        async submit() {
          if (!this.validateForm()) return;

          this.submitting = true;

          try {
            const payload = {
              full_name: this.data.full_name,
              email: this.data.email,
              phone: this.data.phone,
              status: this.data.status,
              role: 'manager',
              password: this.data.id ? undefined : this.data.password
            };

            if (this.data.id) {
              // UPDATE manager
              await axios.put(`/api/users/${this.data.id}`, payload);
              toast.fire('Success!', 'Manager updated successfully', 'success');
            } else {
              // CREATE manager
              await axios.post('/api/users', payload);
              toast.fire('Success!', 'Manager added successfully.Share the temporary password securely', 'success');
            }

            // Close modal
            const modal = bootstrap.Modal.getInstance(
              document.getElementById('addManagerModal')
            );
            modal.hide();

            // Reset form
            this.resetForm();

            // Reload list
            this.loadLists();

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

        resetForm() {
          this.data = {
            id: null,
            full_name: '',
            email: '',
            phone: '',
            status: 'active',
            role: 'manager',
            password: ''
          };
        },
        navigateTo(location){
            this.$router.push(location)
        },
        deleteManager(id){
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
                  axios.delete('/api/users/'+id).then(() => {
                  toast.fire(
                    'Deleted!',
                    'Manager has been deleted.',
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
          axios.get('/api/managers')
            .then((response) => {
              this.managers = response.data;
              console.log(response)

              setTimeout(() => {
                $("#ManagersTable").DataTable();
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
    
    
    