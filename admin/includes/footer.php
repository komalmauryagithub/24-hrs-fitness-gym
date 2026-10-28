    </main>
  </div>
</div>

<!-- Global Confirmation Modal -->
<div class="adm-modal-backdrop" id="admConfirmModal">
  <div class="adm-modal" style="max-width: 440px;">
    <div class="adm-modal-header">
      <h3 class="adm-modal-title" id="admConfirmTitle">Confirm Operation</h3>
      <button type="button" class="adm-modal-close" onclick="closeConfirmModal()">&times;</button>
    </div>
    <div class="adm-modal-body">
      <p id="admConfirmMessage" style="margin: 0; color: var(--adm-silver);">Are you sure you wish to proceed with this destructive action? This operation cannot be reversed.</p>
    </div>
    <div class="adm-modal-footer">
      <button type="button" class="adm-btn adm-btn-outline adm-btn-sm" onclick="closeConfirmModal()">Cancel</button>
      <form id="admConfirmForm" method="POST" action="" style="margin: 0;">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken ?? '') ?>">
        <input type="hidden" name="action" id="admConfirmAction" value="delete">
        <input type="hidden" name="id" id="admConfirmId" value="">
        <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm" id="admConfirmBtn">Execute</button>
      </form>
    </div>
  </div>
</div>

<script>
// Mobile Sidebar Toggle
const hamburger = document.getElementById('admHamburger');
const sidebar = document.getElementById('admSidebar');
const overlay = document.getElementById('admOverlay');

if (hamburger && sidebar && overlay) {
  hamburger.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('open');
  });

  overlay.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('open');
  });
}

// Global Confirmation Modal Controller
function openConfirmModal(actionUrl, id, title = 'Confirm Deletion', message = null, btnText = 'Delete Permanently') {
  const modal = document.getElementById('admConfirmModal');
  const form = document.getElementById('admConfirmForm');
  const idInput = document.getElementById('admConfirmId');
  const titleEl = document.getElementById('admConfirmTitle');
  const msgEl = document.getElementById('admConfirmMessage');
  const btnEl = document.getElementById('admConfirmBtn');

  if (modal && form) {
    form.action = actionUrl;
    idInput.value = id;
    if (title) titleEl.textContent = title;
    if (message) msgEl.textContent = message;
    if (btnEl) btnEl.textContent = btnText;
    modal.classList.add('open');
  }
}

function closeConfirmModal() {
  const modal = document.getElementById('admConfirmModal');
  if (modal) {
    modal.classList.remove('open');
  }
}

// Close modal on Escape
window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeConfirmModal();
  }
});

// Live UTC Clock
const clockEl = document.getElementById('admClock');
if (clockEl) {
  setInterval(() => {
    const now = new Date();
    clockEl.textContent = now.toISOString().replace('T', ' ').substring(0, 19) + ' UTC';
  }, 1000);
}
</script>
</body>
</html>
