    </main><!-- /.page-content -->
  </div><!-- /.main-area -->
</div><!-- /.app-layout -->

<!-- ── Global modal JS ──────────────────────────────────── -->
<script>
// Open / close modals
document.addEventListener('click', function(e) {
  // Open
  if (e.target.dataset.modal) {
    document.getElementById(e.target.dataset.modal)?.classList.add('open');
  }
  // Close via overlay click or close button
  if (e.target.classList.contains('modal-overlay') || e.target.classList.contains('modal-close')) {
    e.target.closest('.modal-overlay')?.classList.remove('open');
  }
});
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
  }
});

// Auto-dismiss alerts after 4 s
setTimeout(() => {
  document.querySelectorAll('.alert').forEach(a => {
    a.style.transition = 'opacity .4s';
    a.style.opacity = '0';
    setTimeout(() => a.remove(), 400);
  });
}, 4000);
</script>
</body>
</html>
