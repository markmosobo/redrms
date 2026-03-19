<template>
  <main class="background-image">
      <section
        class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4"
      >
        <div class="container">
          <div class="row justify-content-center">
            <div
              class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center"
            >
              <div class="d-flex justify-content-center py-4">
                <router-link to="/" class="logo d-flex align-items-center w-auto">
                  <img src="@/assets/img/algospacelogo.png" alt="">
                  <span class="d-none d-lg-block" style="color: white;">REDRMS</span>
                </router-link>
              </div>
              <!-- End Logo -->

              <div class="card mb-3">
                <div class="card-body">
                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4" style="color: purple;">
                      Login to Your Account
                    </h5>
                    <p class="text-center small">
                      Enter your username & password to login
                    </p>
                  </div>

                  <form @submit.prevent="login_user" class="row g-3 needs-validation" novalidate>
                    <div class="col-12">
                      <label for="yourUsername" class="form-label">Email</label>
                      <input
                        type="text"
                        name="email"
                        class="form-control"
                        id="yourUsername"
                        v-model="form.email"
                        required
                      />
                      <div v-if="errors.email" class="text-danger">{{ errors.email }}</div>
                    </div>

                    <div class="col-12 password-container">
                      <label for="yourPassword" class="form-label">Password</label>
                      <div class="input-group">
                        <input
                          :type="isPasswordVisible ? 'text' : 'password'"
                          name="password"
                          class="form-control"
                          id="yourPassword"
                          v-model="form.password"
                          required
                        />
                        <span class="input-group-text" @click="togglePasswordVisibility" style="cursor: pointer;">
                          <i :class="isPasswordVisible ? 'fa fa-eye' : 'fa fa-eye-slash'"></i>
                        </span>
                      </div>
                      <div v-if="errors.password" class="text-danger">{{ errors.password }}</div>
                    </div>

                    <div class="col-12">
                      <div class="form-check">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          name="remember"
                          value="true"
                          id="rememberMe"
                        />
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                      </div>
                    </div>

                    <div class="col-12">
                      <button class="btn btn-success rounded-pill w-100" type="submit" :disabled="loading">
                        <span v-if="loading">
                          <i class="fa fa-spinner fa-spin"></i> Logging in...
                        </span>
                        <span v-else>Login</span>
                      </button>
                    </div>

                  <!-- Auto-Login Quick Buttons -->
                  <div class="mt-4 text-center">
                    <p class="small mb-2" style="color: purple;">Quick Auto-Login</p>
                    <div class="d-grid gap-2">
                      <button type="button" class="btn btn-outline-success rounded-pill" @click="autoLogin('landlord')">Landlord</button>
                      <button type="button" class="btn btn-outline-info rounded-pill" @click="autoLogin('manager')">Manager</button>
                      <button type="button" class="btn btn-outline-warning rounded-pill" @click="autoLogin('tenant')">Tenant</button>
                    </div>
                  </div>


                    <div class="col-12">
                      <p class="small mb-0">
                        Forgot your password?
                        <router-link to="/resetpassword" style="color: orange;">Reset Password</router-link>
                      </p>
                      <p class="small mb-0">
                        Don't have an account?
                        <router-link to="/register" style="color: orange;">Create Account</router-link>
                      </p>
                    </div>
                  </form>
                </div>
              </div>

              <div class="credits">
                <!-- Footer credits (optional) -->
              </div>
            </div>
          </div>
        </div>
      </section>
  </main>
</template>

<script>
import axios from 'axios'
import Swal from 'sweetalert2'
const toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
});

window.toast = toast;

export default {
  name: 'Login',
  data() {
    return {
      form: {
        email: '',
        password: ''
      },
      errors: {},
      isPasswordVisible: false,
      loading: false,
      current_user: '',
      current_user_id: ''
    }
  },
  methods: {
    togglePasswordVisibility() {
      this.isPasswordVisible = !this.isPasswordVisible
    },
    async login_user() {
    this.errors = {}

    if (!this.form.email) {
        this.errors.email = 'Email is required.'
        return
    }
    if (!this.form.password) {
        this.errors.password = 'Password is required.'
        return
    }

    this.loading = true

    try {
        const response = await axios.post('/api/login', this.form)
        this.loading = false
        this.form.email = ''
        this.form.password = ''

        if (response.status === 200 && response.data.token) {
        // ✅ Save both user info and token
        localStorage.setItem('token', response.data.token)
        localStorage.setItem('user', JSON.stringify(response.data.user))

        // Optionally set the token for all future axios calls
        axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`

        // Update UI state
        const loggedInUser = response.data.user
        this.current_user = `${loggedInUser.first_name || ''} ${loggedInUser.last_name || ''}`
        this.current_user_id = loggedInUser.id

        // Success alert (optional)
        toast.fire({
            title: 'Welcome!',
            text: `Hello ${this.current_user}, you are now logged in.`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        })

        // Redirect to dashboard
        this.$router.push('/home')
        } else {
        Swal.fire({
            title: 'Oops!',
            text: response.data.message || 'Login failed. Please try again.',
            icon: 'warning'
        })
        }
    } catch (error) {
        this.loading = false
        Swal.fire({
        title: 'Error',
        text: error.response?.data?.error || 'An error occurred during login. Please try again.',
        icon: 'error'
        })
    }
    },
    autoLogin(role) {
      const presets = {
        landlord: {
          email: "landlord@redrms.co.ke",
          password: "password123"
        },
        manager: {
          email: "manager@redrms.co.ke",
          password: "password123"
        },        
        tenant: {
          email: "tenant@redrms.co.ke",
          password: "password123"
        }        
      };

      if (presets[role]) {
        this.form.email = presets[role].email;
        this.form.password = presets[role].password;

        // Automatically trigger login
        this.login_user();
      }
    }


  }
}
</script>

<style scoped>
main {
  min-height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-size: cover;
  background-attachment: fixed;
  margin: 0;
  padding: 0;
}

/* Full background image */
.background-image {
  background-image: url('@/assets/img/real-estate.jpg');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  background-attachment: fixed;
  min-height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0;
  padding: 0;
}

/* Centers everything with NO margins */
.section.register {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Wrapper to avoid Bootstrap's container margins */
.login-wrapper {
  width: 100%;
  max-width: 420px;
}

/* Glass-like card */
.card {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
}

</style>
