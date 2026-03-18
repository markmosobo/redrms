<template>
  <TheHeader />
  <TheSidebar />
  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <!-- Optional breadcrumbs -->
          <!-- <li class="breadcrumb-item"><a href="#">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li> -->
        </ol>
      </nav>
    </div>
    <!-- End Page Title -->

    <slot></slot>
  </main><!-- End #main -->
  <TheFooter />
</template>

<script>
import TheHeader from "./TheHeader.vue";
import TheSidebar from "./TheSidebar.vue";
import TheFooter from "./TheFooter.vue";

export default {
  name: "Master",
  components: {
    TheHeader,
    TheSidebar,
    TheFooter,
  },
  mounted() {
    document.body.classList.remove("toggle-sidebar");
  },
};
</script>
