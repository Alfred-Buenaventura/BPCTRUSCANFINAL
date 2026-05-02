function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

window.showLogoutConfirm = function() {
    const modal = document.getElementById('logoutConfirmModal');
    if (modal) {
        openModal('logoutConfirmModal');
    } else if (confirm("Are you sure you want to log out?")) {
        window.location.href = 'logout.php';
    }
};

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
};

document.addEventListener('click', function(e) {
    const settingsBtn = e.target.closest('#userSettingsBtn');
    const settingsMenu = document.getElementById('settings-menu');
    if (settingsBtn && settingsMenu) {
        e.preventDefault();
        e.stopPropagation();
        settingsMenu.classList.toggle('active');
        return;
    }

    if (settingsMenu && settingsMenu.classList.contains('active')) {
        if (!e.target.closest('#settings-menu') && !e.target.closest('#userSettingsBtn')) {
            settingsMenu.classList.remove('active');
        }
    }

    const sidebarToggle = e.target.closest('#sidebarToggle');
    if (sidebarToggle) {
        const sidebar = document.getElementById('sidebar');
        const dashboardContainer = document.getElementById('dashboardContainer');
        
        if (sidebar && dashboardContainer) {
            if (window.innerWidth <= 768) {
                dashboardContainer.classList.toggle('sidebar-mobile-open');
            } else {
                const isCurrentlyCollapsed = dashboardContainer.classList.toggle('sidebar-collapsed');
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', isCurrentlyCollapsed ? 'true' : 'false');
                setTimeout(() => { window.dispatchEvent(new Event('resize')); }, 300);
            }
        }
    }

    if (window.innerWidth <= 768) {
        const dashboardContainer = document.getElementById('dashboardContainer');
        const sidebar = document.getElementById('sidebar');
        
        if (dashboardContainer && sidebar && 
            dashboardContainer.classList.contains('sidebar-mobile-open') && 
            !sidebar.contains(e.target) && 
            !e.target.closest('#sidebarToggle') &&
            !e.target.closest('#mobileMenuBtn')) {
            dashboardContainer.classList.remove('sidebar-mobile-open');
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const dashboardContainer = document.getElementById('dashboardContainer');
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

    if (isCollapsed && sidebar && dashboardContainer && window.innerWidth > 768) {
        sidebar.classList.add('collapsed');
        dashboardContainer.classList.add('sidebar-collapsed');
    }

    if (mobileBtn) {
        mobileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (dashboardContainer) dashboardContainer.classList.toggle('sidebar-mobile-open');
        });
    }

    const liveTimeEl = document.getElementById('live-time');
    const liveDateEl = document.getElementById('live-date');

    if (liveTimeEl && liveDateEl) {
        const updateTime = () => {
            const now = new Date();
            liveTimeEl.textContent = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            liveDateEl.textContent = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
        };
        updateTime();
        setInterval(updateTime, 30000);
    }

    const scannerWidget = document.getElementById('scanner-status-widget');
    
    const isEnrollmentPage = document.getElementById('deviceStatusContainer') !== null || 
                             window.location.href.includes('fingerprint_registration');

    if (scannerWidget) {
        if (isEnrollmentPage) {
            scannerWidget.style.display = 'none';
        } else {
            initScannerSocket(scannerWidget); 
        }
    }
});

function markAllAsRead() {
    const badge = document.getElementById('notif-badge');
    if (!badge) return;

    fetch('create_account.php?action=mark_read_all', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrfToken}`
    })
    .then(response => {
        badge.style.display = 'none';
    })
    .catch(err => console.error('Notification update failed:', err));
}

function initScannerSocket(widget) {
    const statusText = widget.querySelector('.scanner-status-text-sub');
    const statusBadge = widget.querySelector('.scanner-status-badge');
    
    function setStatus(online, msg) {
    const widget = document.getElementById('scanner-status-widget');
    const statusMsg = document.getElementById('scanner-status-msg');
    const statusLabel = document.getElementById('scanner-status-label');
    const modalIcon = document.getElementById('modal-scanner-icon');
    const modalDesc = document.getElementById('modal-scanner-desc');

    if (online) {
        widget.classList.remove('offline');
        widget.classList.add('online');
        
        if (statusLabel) statusLabel.textContent = 'ONLINE';

        if (statusMsg) statusMsg.textContent = 'Hardware Ready';

        if (modalIcon) modalIcon.style.color = '#10b981';

        if (modalDesc) modalDesc.textContent = "The biometric scanner device is connected and ready for fingerprint processing. Ensure device is clean for proper use.";
    } else {
        widget.classList.remove('online');
        widget.classList.add('offline');
        
        if (statusLabel) statusLabel.textContent = 'OFFLINE';

        if (statusMsg) statusMsg.textContent = msg || 'Check connection';
        
        if (modalIcon) modalIcon.style.color = '#ef4444';

        if (modalDesc) modalDesc.textContent = "The scanner service is currently unreachable. Ensure device is properly connected before use.";
    }
}

    try {
        const socket = new WebSocket("ws://127.0.0.1:8080");
        socket.onopen = () => setStatus(true);
        socket.onclose = () => { setStatus(false, "Device Not Detected"); setTimeout(() => initScannerSocket(widget), 5000); };
        socket.onerror = () => { setStatus(false, "Connection Error"); socket.close(); };
    } catch (e) {
        setStatus(false, "Service Error");
    }
}

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const body = document.body;
    
    if (!sidebar || !overlay) return;

    if (sidebar.classList.contains('active')) {
        sidebar.classList.remove('active');
        overlay.style.display = 'none';
        body.classList.remove('menu-open');
    } else {
        sidebar.classList.add('active');
        overlay.style.display = 'block';
        body.classList.add('menu-open');
    }
}

const phoneInput = document.getElementById('phoneInput');

if (phoneInput) {
    phoneInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/\D/g, '');
        
        if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
        }
    });

    phoneInput.addEventListener('keypress', function(e) {
        if (!/[0-9]/.test(e.key)) {
            e.preventDefault();
        }
    });
}