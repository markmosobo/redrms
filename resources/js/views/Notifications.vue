<template>
  <Master>
    <section class="section dashboard">

      <!-- HEADER -->
      <div class="card">
        <div class="card-body">

          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">
              Notifications
              <span>| System Messages</span>
            </h5>

            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-primary" @click="filter = 'all'">
                All
              </button>
              <button class="btn btn-outline-warning" @click="filter = 'unread'">
                Unread
              </button>
              <button class="btn btn-outline-success" @click="filter = 'read'">
                Read
              </button>
            </div>

          </div>

          <!-- TABLE -->
          <table class="table table-borderless">
            <thead>
              <tr>
                <th>Title</th>
                <th>Message</th>
                <th>Type</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
              </tr>
            </thead>

            <tbody v-if="loading">
              <tr>
                <td colspan="6" class="text-center">
                  <div class="spinner-border text-primary"></div>
                </td>
              </tr>
            </tbody>

            <tbody v-else>
              <tr v-for="n in filteredNotifications" :key="n.id">

                <td>
                  <strong>{{ n.title }}</strong>
                </td>

                <td>
                  {{ truncate(n.message, 80) }}
                </td>

                <td>
                  <span class="badge bg-info text-dark">
                    {{ n.type || 'system' }}
                  </span>
                </td>

                <td>
                  <span v-if="!n.read_at" class="badge bg-warning">
                    Unread
                  </span>
                  <span v-else class="badge bg-success">
                    Read
                  </span>
                </td>

                <td>
                  {{ formatDate(n.created_at) }}
                </td>

                <td class="d-flex gap-2">

                  <button
                    class="btn btn-sm btn-primary"
                    @click="openNotification(n)"
                  >
                    View
                  </button>

                  <button
                    v-if="!n.read_at"
                    class="btn btn-sm btn-success"
                    @click="markAsRead(n.id)"
                  >
                    Mark Read
                  </button>

                </td>

              </tr>
            </tbody>

          </table>

        </div>
      </div>

      <!-- DETAIL MODAL -->
      <div v-if="selected" class="modal d-block">
        <div class="modal-dialog modal-lg">
          <div class="modal-content p-3">

            <h5>{{ selected.title }}</h5>

            <p class="text-muted">
              {{ selected.message }}
            </p>

            <!-- Optional: deep link -->
            <div v-if="selected.resource_id">
              <button class="btn btn-outline-primary btn-sm"
                      @click="goToResource(selected)">
                Open Related Item
              </button>
            </div>

            <div class="text-end mt-3">
              <button class="btn btn-secondary" @click="selected = null">
                Close
              </button>
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

export default {
  components: { Master },

  data() {
    return {
      notifications: [],
      loading: true,
      filter: 'all',
      selected: null
    };
  },

  computed: {

    filteredNotifications() {
      if (this.filter === 'all') return this.notifications;

      if (this.filter === 'unread') {
        return this.notifications.filter(n => !n.read_at);
      }

      if (this.filter === 'read') {
        return this.notifications.filter(n => n.read_at);
      }

      return this.notifications;
    }

  },

  methods: {

    async loadNotifications() {
      this.loading = true;

      try {
        const res = await axios.get('/api/notifications');
        this.notifications = res.data.data ?? [];
      } catch (e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },

    async markAsRead(id) {
      await axios.post(`/api/notifications/${id}/read`);
      this.loadNotifications();
    },

    openNotification(n) {
      this.selected = n;

      if (!n.read_at) {
        this.markAsRead(n.id);
      }
    },

    goToResource(n) {
      if (n.type === 'termination') {
        this.$router.push(`/termination-requests`);
      }

      if (n.type === 'inspection') {
        this.$router.push(`/inspections`);
      }

      if (n.type === 'deposit') {
        this.$router.push(`/deposits`);
      }

      this.selected = null;
    },

    truncate(text, len) {
      return text?.length > len
        ? text.substring(0, len) + '...'
        : text;
    },

    formatDate(date) {
      return new Date(date).toLocaleString();
    }

  },
  computed: {
    isAdmin() {
        return this.role === 'admin';
    }
  },
  mounted() {
    this.loadNotifications();
  }
};
</script>