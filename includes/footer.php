  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
  
  <!-- Sidebar functionality -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Auto-close offcanvas when clicking navigation links on mobile
      const offcanvasElement = document.getElementById('sidebarOffcanvas');
      const navLinks = document.querySelectorAll('#sidebarOffcanvas .nav-link');
      
      if (offcanvasElement) {
        const offcanvas = new bootstrap.Offcanvas(offcanvasElement);
        
        // Close offcanvas when clicking navigation links
        navLinks.forEach(link => {
          link.addEventListener('click', function() {
            offcanvas.hide();
          });
        });
        
        // Ensure proper cleanup when offcanvas is hidden
        offcanvasElement.addEventListener('hidden.bs.offcanvas', function() {
          // Remove any lingering backdrop
          const backdrop = document.querySelector('.offcanvas-backdrop');
          if (backdrop) {
            backdrop.remove();
          }
        });
      }
    });
  </script>

</body>
</html>