<template>
  <Master>
    <section class="section dashboard">

      <div class="card">
        <div class="card-body">

          <h5 class="card-title">
            Termination Requests
          </h5>

          <table id="RequestsTable" class="table table-borderless">
            <thead>
              <tr>
                <th>Tenant</th>
                <th>Unit</th>
                <th>Reason</th>
                <th>Requested End</th>
                <th>Status</th>
                <th v-if="canProcess">Action</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="req in requests" :key="req.id">

                <td>{{ req.tenancy?.tenant?.full_name }}</td>
                <td>{{ req.tenancy?.unit?.unit_number }}</td>

                <td>
                  {{ truncate(req.reason, 40) }}
                </td>

                <td>
                  {{ formatDate(req.requested_end_date) }}
                </td>

                <td>
                  <span :class="badgeClass(req.status)">
                    {{ req.status }}
                  </span>
                </td>

                <td v-if="canProcess">
                  <div class="btn-group btn-group-sm">

                    <button
                      class="btn btn-success"
                      :disabled="req.status !== 'pending'"
                      @click="approve(req.id)"
                    >
                      Approve
                    </button>

                    <button
                      class="btn btn-danger"
                      :disabled="req.status !== 'pending'"
                      @click="reject(req.id)"
                    >
                      Reject
                    </button>

                  </div>
                </td>

              </tr>
            </tbody>

          </table>

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
  components: { Master },

  data() {
    return {
      requests: [],
      role: null
    };
  },

  computed: {
    canProcess() {
      return ['admin', 'manager', 'landlord'].includes(this.role);
    }
  },

  methods: {

    async loadRequests() {
      const res = await axios.get('/api/termination-requests');
      this.requests = res.data;
        setTimeout(() => {
        $("#RequestsTable").DataTable();
        }, 10);      
    },

    async approve(id) {
      await axios.post(`/api/termination-requests/${id}/approve`);
      this.loadRequests();
    },

    async reject(id) {
      await axios.post(`/api/termination-requests/${id}/reject`);
      this.loadRequests();
    },

    truncate(text, len) {
      return text?.length > len ? text.substring(0, len) + "..." : text;
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString();
    },

    badgeClass(status) {
      return {
        pending: 'badge bg-warning',
        approved: 'badge bg-success',
        rejected: 'badge bg-danger'
      }[status];
    }

  },

  mounted() {
    this.role = JSON.parse(localStorage.getItem('user'))?.role;
    this.loadRequests();
  }
};
</script>