<template>
  <Master>
    <section class="section dashboard">
      <div class="row">
        <div class="col-12">
          <div class="card overflow-auto">
            <div class="card-body pb-0">

              <h5 class="card-title">
                Audit Logs <span>| System Activity</span>
              </h5>

              <table id="AuditLogsTable" class="table table-borderless">
                <thead>
                  <tr>
                    <th>User</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th>Date</th>
                  </tr>
                </thead>

                <tbody v-if="loading">
                  <tr>
                    <td colspan="4" class="text-center py-4">
                      <div class="spinner-border text-primary"></div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else-if="logs.length === 0">
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                      No audit logs found
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr v-for="log in logs" :key="log.id">
                    <td>
                      {{ log.user?.full_name || 'System' }}
                    </td>

                    <td>
                      <a href="#" @click.prevent="selectedLog = log">
                        {{ truncate(log.description, 30) }}
                      </a>
                    </td>

                    <td>
                      {{ log.ip_address || '—' }}
                    </td>

                    <td>
                      {{ formatDate(log.created_at) }}
                    </td>
                  </tr>
                </tbody>

              </table>

            </div>
          </div>
        </div>
      </div>

      <div v-if="selectedLog" class="modal d-block">
        <div class="modal-dialog">
          <div class="modal-content p-3">
            <h5>Audit Log</h5>
            <p>{{ selectedLog.description }}</p>
            <button class="btn btn-secondary" @click="selectedLog = null">
              Close
            </button>
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
import "datatables.net-dt/js/dataTables.dataTables";
import "datatables.net-dt/css/jquery.dataTables.min.css";

const toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 3000,
});

export default {
  components: { Master },

  data() {
    return {
      logs: [],
      loading: true,
      table: null,
      selectedLog: null,
    };
  },

  methods: {
    truncate(text, length = 80) {
      if (!text) return '';
      return text.length > length
        ? text.substring(0, length) + '...'
        : text;
    },
    async loadAuditLogs() {
      this.loading = true;

      try {
        const res = await axios.get("/api/audit-logs");

        this.logs = res.data.data ?? res.data;
        setTimeout(() => {
          $("#AuditLogsTable").DataTable();
        }, 10);

      } catch (e) {
        toast.fire({
          icon: "error",
          title: "Failed to load audit logs",
        });

        console.error(e);
      } finally {
        this.loading = false;
      }
    },

    formatDate(date) {
      return new Date(date).toLocaleString();
    },
  },

  mounted() {
    this.loadAuditLogs();
  },

  beforeUnmount() {
    if (this.table) {
      this.table.destroy();
    }
  },
};
</script>