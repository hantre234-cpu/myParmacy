// assets/js/app.js — PharmaCare global JS

'use strict';

/* ── Auto-dismiss flash alerts ─────────────────────────────── */
document.querySelectorAll('.alert-float').forEach(el => {
  setTimeout(() => {
    const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
    bsAlert.close();
  }, 4500);
});

/* ── Confirm delete dialogs ────────────────────────────────── */
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', function (e) {
    if (!confirm(this.dataset.confirm || 'Confirmer la suppression ?')) {
      e.preventDefault();
    }
  });
});

/* ── Table row search (client-side) ────────────────────────── */
const liveSearch = document.getElementById('liveSearch');
if (liveSearch) {
  liveSearch.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('[data-searchable]').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}

/* ── Number formatting ──────────────────────────────────────── */
window.fmt = {
  currency: v => new Intl.NumberFormat('fr-DZ', { style: 'currency', currency: 'DZD' }).format(v),
  date:     v => new Date(v).toLocaleDateString('fr-FR'),
};
