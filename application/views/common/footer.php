        <!-- Footer -->
        <footer class="bg-white text-center text-muted py-4 mt-5" style="border-top: 2px solid var(--primary-red);">
            <div class="container">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> KPI Portal. All rights reserved.</p>
            </div>
        </footer>
    </div> <!-- End Main Content -->

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-hide alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
        
        // Confirm delete actions
        $('.btn-delete').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
        
        // Toggle sidebar for mobile
        function toggleSidebar() {
            $('#sidebar').toggleClass('active');
        }
        
        // Close sidebar when clicking outside on mobile
        $(document).click(function(e) {
            if ($(window).width() <= 768) {
                if (!$(e.target).closest('.sidebar, .menu-toggle').length) {
                    $('#sidebar').removeClass('active');
                }
            }
        });
    </script>
</body>
</html>
