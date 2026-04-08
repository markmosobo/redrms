<template>
    <Master>
        <section class="section profile">
      <div class="row">
        <div class="col-xl-4">

          <div class="card">
            <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">

              <!-- <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle"> -->
              <h2>{{form.full_name}}</h2>
              <h3 v-if="form.role == 'admin'">Admin</h3>
              <h3 v-if="form.role == 'landlord'">Landlord</h3>
              <h3 v-if="form.role == 'caretaker'">Caretaker</h3>
              <h3 v-if="form.role == 'service_provider'">Service Provider</h3>
              <h3 v-if="form.role == 'tenant'">Tenant</h3>
              <!-- <div class="social-links mt-2">
                <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>
                <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
                <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
                <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
              </div> -->
            </div>
          </div>

        </div>

        <div class="col-xl-8">

          <div class="card">
            <div class="card-body pt-3">
              <!-- Bordered Tabs -->
              <ul class="nav nav-tabs nav-tabs-bordered" role="tablist">

                <li class="nav-item" role="presentation">
                  <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview" aria-selected="true" role="tab">Overview</button>
                </li>

                <li class="nav-item" role="presentation">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit" aria-selected="false" tabindex="-1" role="tab">Edit Profile</button>
                </li>


                <li class="nav-item" role="presentation">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password" aria-selected="false" tabindex="-1" role="tab">Change Password</button>
                </li>

              </ul>
              <div class="tab-content pt-2">

                <div class="tab-pane fade show active profile-overview" id="profile-overview" role="tabpanel">
                  <!-- <h5 class="card-title">About</h5>
                  <p class="small fst-italic">Sunt est soluta temporibus accusantium neque nam maiores cumque temporibus. Tempora libero non est unde veniam est qui dolor. Ut sunt iure rerum quae quisquam autem eveniet perspiciatis odit. Fuga sequi sed ea saepe at unde.</p> -->

                  <h5 class="card-title">Profile Details</h5>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label ">Full Name</div>
                    <div class="col-lg-9 col-md-8">{{form.full_name}} </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Phone</div>
                    <div class="col-lg-9 col-md-8">{{form.phone}}</div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Email</div>
                    <div class="col-lg-9 col-md-8">{{form.email}}</div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Status</div>
                    <div class="col-lg-9 col-md-8">{{form.status}}</div>
                  </div>

                </div>

                <div class="tab-pane fade profile-edit pt-3" id="profile-edit" role="tabpanel">

                  <!-- Profile Edit Form -->
                  <form @submit.prevent="editProfile()">

                    <div class="row mb-3">
                      <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Name</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="fullName" type="text" class="form-control" id="fullName" v-model="form.name">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="Phone" class="col-md-4 col-lg-3 col-form-label">Phone</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="phone" type="text" class="form-control" placeholder="Format(07XXXXXXXX)" id="Phone" v-model="form.phone">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="Email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="email" type="email" class="form-control" id="Email" v-model="form.email">
                      </div>
                    </div>

                    <div class="text-center">
                      <button type="submit" style="background-color: darkgreen; border-color: darkgreen;" class="btn btn-sm rounded-pill btn-primary">Save Changes</button>
                    </div>
                  </form><!-- End Profile Edit Form -->

                </div>

                <div class="tab-pane fade pt-3" id="profile-change-password" role="tabpanel">
                  <!-- Change Password Form -->
                  <form @submit.prevent="changePassword()">

                    <div class="row mb-3">
                      <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">New Password</label>
                      <div class="input-group">
                        <input
                          :type="isPasswordVisible ? 'text' : 'password'"
                          name="password"
                          class="form-control"
                          id="yourPassword"
                          v-model="form.new_password"
                          required
                        />
                        <span class="input-group-text" @click="togglePasswordVisibility" style="cursor: pointer;">
                          <i :class="isPasswordVisible ? 'fa fa-eye' : 'fa fa-eye-slash'"></i>
                        </span>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="renewPassword" class="col-md-4 col-lg-6 col-form-label">Re-enter New Password</label>
                      <div class="input-group">
                        <input
                          :type="isPasswordVisible ? 'text' : 'password'"
                          name="password"
                          class="form-control"
                          id="yourPassword"
                          v-model="form.conf_password"
                          required
                        />
                        <span class="input-group-text" @click="togglePasswordVisibility" style="cursor: pointer;">
                          <i :class="isPasswordVisible ? 'fa fa-eye' : 'fa fa-eye-slash'"></i>
                        </span>
                      </div>
                    </div>

                    <div class="text-center">
                      <button type="submit" style="background-color: darkgreen; border-color: darkgreen;" class="btn btn-sm rounded-pill btn-primary">Change Password</button>
                    </div>
                  </form><!-- End Change Password Form -->

                </div>

              </div><!-- End Bordered Tabs -->

            </div>
          </div>

        </div>
      </div>
    </section>
    </Master>
</template>
<script>
    import Master from '@/components/Master.vue'
    import axios from 'axios';
    // import axios from 'axios';
    import Swal from 'sweetalert2';
    const toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
    
    window.toast = toast;
    export default{
        data(){
            return {
                isPasswordVisible: false,
                form: {
                    first_name: '',
                    last_name: '',
                    email: '',
                    phone: '',
                    city: '',
                    county: '',
                    address: '',
                    new_password: '',
                    conf_password: ''
                }
            }
        },
        components: {
        Master,
      },
      methods: {
        togglePasswordVisibility() {
        this.isPasswordVisible = !this.isPasswordVisible
        },        
        editProfile(){
            axios.put('api/profile/'+this.form.id, this.form).then(()=> {
                toast.fire(
                'Success!',
                'Profile updated!',
                'success'
             )
             this.$router.push('/profile')
            })
        },
        changePassword(){
            axios.put('api/changepassword/'+this.form.id, this.form).then(() => {
                toast.fire(
                'Success!',
                'Password changed!',
                'success'
             )
             this.$router.push('/profile') 
            }).catch((error) => {
                Swail.fire(
                'Failed!',
                 error,
                'fail'
             )
             this.$router.push('/profile')
            })
        }
      },
      mounted(){
        this.user = localStorage.getItem('user');
        this.form = JSON.parse(this.user);
        // console.log("home", JSON.parse(this.user))

      }
    }
</script>    