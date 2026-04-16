<template>
  <Master>
    <section class="section dashboard">

      <!-- CARD -->
      <div class="card">
        <div class="card-body">

          <!-- HEADER -->
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">
              Notifications
              <span class="text-muted">| System Messages</span>
            </h5>

            <div class="btn-group btn-group-sm">
              <button
                class="btn btn-outline-primary"
                :disabled="loading"
                @click="filter = 'all'"
              >
                All
              </button>
              <button
                class="btn btn-outline-warning"
                :disabled="loading"
                @click="filter = 'unread'"
              >
                Unread
              </button>
              <button
                class="btn btn-outline-success"
                :disabled="loading"
                @click="filter = 'read'"
              >
                Read
              </button>
            </div>
          </div>

          <!-- TABLE -->
          <table class="table table-borderless align-middle">
            <thead>
              <tr>
                <th>Title</th>
                <th>Message</th>
                <th>Type</th>
                <th>Status</th>
                <th>Date</th>
                <th width="140">Action</th>
              </tr>
            </thead>

            <!-- LOADING -->
            <tbody v-if="loading">
              <tr>
                <td colspan="6" class="text-center py-5">
                  <div class="spinner-border text-primary"></div>
                </td>
              </tr>
            </tbody>

            <!-- CONTENT -->
            <tbody v-else>

              <!-- EMPTY STATE -->
              <tr v-if="!hasNotifications">
                <td colspan="6" class="text-center py-5 text-muted">
                  <div class="mb-3">
                    <i class="bi bi-inbox fs-1"></i>
                  </div>

                  <h6 class="mb-1">{{ emptyMessage }}</h6>
                  <small>
                    Notifications such as inspections, deposits, or exit requests
                    will appear here.
                  </small>
                </td>
              </tr>

              <!-- ROWS -->
              <tr
                v-else
                v-for="n in filteredNotifications"
                :key="n.id"
              >
                <td>
                  <strong>{{ n.title || 'Notification' }}</strong>
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
                  <span
                    v-if="!n.read_at"
                    class="badge bg-warning"
                  >
                    Unread
                  </span>
                  <span
                    v-else
                    class="badge bg-success"
                  >
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

      <!-- VIEW MODAL -->
      <div v-if="selected" class="modal d-block" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title">
                {{ selected.title || 'Notification' }}
              </h5>
              <button
                class="btn-close"
                @click="selected = null"
              ></button>
            </div>

            <div class="modal-body">
              <p class="text-muted">
                {{ selected.message }}
              </p>

              <div v-if="selected.resource_id" class="mt-3">
                <button
                  class="btn btn-outline-primary btn-sm"
                  @click="goToResource(selected)"
                >
                  Open Related Item
                </button>
              </div>
            </div>

            <div class="modal-footer">
              <button
                class="btn btn-secondary"
                @click="selected = null"
              >
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
      if (this.filter === 'unread') {
        return this.notifications.filter(n => !n.read_at);
      }

      if (this.filter === 'read') {
        return this.notifications.filter(n => n.read_at);
      }

      return this.notifications;
    },

    hasNotifications() {
      return this.filteredNotifications.length > 0;
    },

    emptyMessage() {
      if (this.filter === 'unread') return 'No unread messages 🎉';
      if (this.filter === 'read') return 'No read messages yet';
      return 'You have no messages';
    }

  },

  methods: {

    async loadNotifications() {
      this.loading = true;

      try {
        const res = await axios.get('/api/admin/notifications');

        // works for paginated & non-paginated
        this.notifications = res.data.data ?? res.data ?? [];
      } catch (e) {
        console.error('Failed to load notifications', e);
        this.notifications = [];
      } finally {
        this.loading = false;
      }
    },

    async markAsRead(id) {
      try {
        await axios.post(`/api/notifications/${id}/read`);
        this.loadNotifications();
      } catch (e) {
        console.error('Failed to mark as read', e);
      }
    },

    openNotification(n) {
      this.selected = n;

      if (!n.read_at) {
        this.markAsRead(n.id);
      }
    },

    goToResource(n) {
      if (n.type === 'termination') {
        this.$router.push('/termination-requests');
      }

      if (n.type === 'inspection') {
        this.$router.push('/inspections');
      }

      if (n.type === 'deposit') {
        this.$router.push('/deposits');
      }

      this.selected = null;
    },

    truncate(text, len) {
      if (!text) return '';
      return text.length > len
        ? text.substring(0, len) + '...'
        : text;
    },

    formatDate(date) {
      if (!date) return '—';
      return new Date(date).toLocaleString();
    }

  },

  mounted() {
    this.loadNotifications();
  }
};
</script>

<style scoped>
.bi-inbox {
  opacity: 0.6;
}
</style>