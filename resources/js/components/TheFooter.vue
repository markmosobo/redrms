<template>
    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer">
      <div class="copyright">
        &copy; Copyright <strong><span style="color: darkgreen;"> AlgoSpace Cyber {{currentYear}}</span></strong>. All Rights Reserved
      </div>
      <div class="credits">
        <!-- All the links in the footer should remain intact. -->
        <!-- You can delete the links only if you purchased the pro version. -->
        <!-- Licensing information: https://bootstrapmade.com/license/ -->
        <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ -->
        <!-- Designed by <a href="#">Openpath Solutions</a> -->
      </div>
    </footer><!-- End Footer -->
  </template>
  
  <script>
  export default {
    name: 'TheFooter',
    data(){
      return {
        currentYear: ''
      }
    },
    mounted()
    {
      const date = new Date();
      this.currentYear = date.getFullYear();
    }
    
  }
  </script>
  
  <style scoped>
  
  </style>
  