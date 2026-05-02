<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$isMasterAdmin = Helper::isAdmin(); 
$isSchedAdmin  = (isset($_SESSION['role']) && $_SESSION['role'] === 'Schedule Admin');
$anyAdmin      = ($isMasterAdmin || $isSchedAdmin);

$db = Database::getInstance();

$notifStmt = $db->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0", [$_SESSION['user_id']], "i");
$unreadCount = $notifStmt->get_result()->fetch_assoc()['count'] ?? 0;
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <i class="fa-solid fa-fingerprint"></i>
            </div>
            <div class="sidebar-title">
                <h1>BPC Attendance</h1>
                <p>
                    <?php 
                    if ($isMasterAdmin) echo 'System Admin';
                    elseif ($isSchedAdmin) echo 'Schedule Admin';
                    else echo 'Staff Dashboard';
                    ?>
                </p>
            </div>
        </div>
        <button class="btn sidebar-toggle-btn" id="sidebarToggle" title="Toggle Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <a href="index.php" class="nav-item <?= ($currentPage == 'index.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-house nav-icon"></i>
            <span class="nav-text">Home</span>
        </a>
        
        <?php if ($isMasterAdmin): ?>
        <a href="create_account.php" class="nav-item <?= ($currentPage == 'create_account.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-user-plus nav-icon"></i>
            <span class="nav-text">Manage User Accounts</span>
        </a>
        <a href="create_admin.php" class="nav-item <?= ($currentPage == 'create_admin.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-user-shield nav-icon"></i>
            <span class="nav-text">Manage Admin Accounts</span>
        </a>
        <a href="complete_registration.php" class="nav-item <?= ($currentPage == 'complete_registration.php') ? 'active' : '' ?>">
             <i class="fa-solid fa-fingerprint nav-icon"></i>
            <span class="nav-text">Fingerprint Registration</span>
        </a>
        <?php endif; ?>
        
        <?php if (!$isSchedAdmin): ?>
        <a href="attendance_reports.php" class="nav-item <?= ($currentPage == 'attendance_reports.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-clipboard-list nav-icon"></i>
            <span class="nav-text">Attendance Reports</span>
        </a>
        <?php endif; ?>

        <a href="schedule_management.php" class="nav-item <?= ($currentPage == 'schedule_management.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-days nav-icon"></i>
            <span class="nav-text">Schedule Management</span>
            <?php 
            if ($anyAdmin) {
                // Fetch current pending count directly to ensure sidebar badge is always accurate
                $countQuery = $db->query("SELECT COUNT(*) as total FROM class_schedules WHERE status = 'pending'");
                $pendingCount = $countQuery->get_result()->fetch_assoc()['total'] ?? 0;
                
                if ($pendingCount > 0): ?>
                    <span class="sidebar-badge" style="background: #ef4444; color: #fff; padding: 2px 7px; border-radius: 10px; font-size: 0.7rem; font-weight: 800; margin-left: auto; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);">
                        <?= $pendingCount ?>
                    </span>
                <?php endif; 
            } ?>
        </a>

        <?php if ($anyAdmin): ?>
        <a href="holiday_management.php" class="nav-item <?= ($currentPage == 'holiday_management.php') ? 'active' : '' ?>"> 
            <i class="fa-solid fa-umbrella-beach nav-icon"></i>
            <span class="nav-text">DTR Management</span>
        </a>
        <?php endif; ?>
        
        <button type="button" class="nav-item nav-item-button" onclick="openNotificationModal()" id="notificationsBtn">
            <i class="fa-solid fa-bell nav-icon"></i>
            <span class="nav-text">Notifications</span>
            <?php if ($unreadCount > 0): ?>
                <span class="notification-badge" id="notif-badge"><?= $unreadCount ?></span>
            <?php endif; ?>
        </button>
    </nav>

    <div class="sidebar-footer">
        <div id="settings-menu">
            <a href="profile.php" class="settings-menu-item <?= ($currentPage == 'profile.php') ? 'active-settings' : '' ?>">
                <i class="fa-solid fa-user"></i>
                <span>My Profile</span>
            </a>
            <a href="about.php" class="settings-menu-item <?= ($currentPage == 'about.php') ? 'active-settings' : '' ?>">
                <i class="fa-solid fa-circle-info"></i>
                <span>About Us</span>
            </a>
            <?php if (!$isMasterAdmin): ?>
            <a href="contact.php" class="settings-menu-item <?= ($currentPage == 'contact.php') ? 'active-settings' : '' ?>">
                <i class="fa-solid fa-envelope"></i>
                <span>Contact Support</span>
            </a>
            <?php endif; ?>
        </div>
        
        <div class="user-info">
            <div class="user-info-inner">
                <div class="user-avatar">
                    <?php 
                    $profileImg = $_SESSION['profile_image_path'] ?? null;
                    if (!empty($profileImg) && file_exists(__DIR__ . '/../../../' . $profileImg)): ?>
                        <img src="<?= htmlspecialchars($profileImg) ?>?v=<?= time() ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block;">
                    <?php else: ?>
                        <?= strtoupper(substr($_SESSION['first_name'] ?? 'U', 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="user-details">
                    <p>Logged in as</p>
                    <div class="user-name"><?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></div>
                </div>
                <button class="btn user-settings-btn" id="userSettingsBtn" type="button">
                    <i class="fa-solid fa-gear"></i>
                </button>
            </div>
        </div>

        <button class="btn logout-btn" onclick="showLogoutConfirm()">
             <i class="fa-solid fa-right-from-bracket logout-icon"></i>
            <span class="logout-text">Log out</span>
        </button>
    </div>
</aside>

<style>
.nav-item.active {
    background: rgba(67, 189, 148, 0.33) !important;
    color: #065f46 !important;
    border-left: 4px solid #10b981;
    font-weight: 700;
    transition: all 0.3s ease;
}

.nav-item.active i {
    color: #10b981 !important;
}

.nav-item:not(.active):hover {
    background: #f8fafc;
    color: #1e293b;
}

.settings-menu-item.active-settings {
    background: #ecfdf5 !important;                
    color: #065f46 !important;                    
    border-radius: 8px;                            
    font-weight: 700;
}

.settings-menu-item.active-settings i {
    color: #10b981 !important;
}

.kiosk-link {
    background: rgba(5, 150, 105, 0.1);
    border-left: 4px solid #059669;
    margin-top: 10px;
}

.kiosk-link:hover {
    background: rgba(5, 150, 105, 0.2) !important;

}

.notif-filters {
    display: flex !important;
    gap: 10px;
    background: rgba(67, 189, 148, 0.15) !important;
    border-radius: 10px 10px 0 0;
    padding: 8px 12px 0;
    border-bottom: 2px solid #10b981;
}

.notif-filter-btn {
    background: none !important;
    border: none !important;
    padding: 10px 15px;
    font-size: 0.9rem;
    font-weight: 800;
    color: #065f46 !important;
    cursor: pointer;
    border-bottom: 4px solid transparent !important;
    transition: all 0.2s ease;
    opacity: 0.5;
}

.notif-filter-btn.active {
    opacity: 1;
    border-bottom: 4px solid #065f46 !important;
}

.notification-item {
    display: flex !important;
    padding: 15px;
    margin-bottom: 12px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.notification-item:hover {
    transform: translateX(5px);
    border-color: #10b981;
    background: #f0fdf4;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08);
}

.notification-item.unread {
    border-left: 5px solid #10b981;
    background: #f0fdf9;
}

.notification-content p {
    margin: 0;
    font-weight: 700;
    color: #1e293b;
    font-size: 0.92rem;
    line-height: 1.4;
}

.notification-time {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-top: 5px;
    display: block;
}

.notif-tab-count {
    background: #065f46;
    color: white !important;
    font-size: 0.7rem;
    padding: 1px 6px;
    border-radius: 8px;
    margin-left: 5px;
}

.notif-delete-btn {
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 8px;
    margin-left: auto;
    opacity: 0;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.notification-item:hover .notif-delete-btn {
    opacity: 1;
}

.notif-delete-btn:hover {
    color: #ef4444;
    transform: scale(1.2);
}

.notif-fade-out {
    opacity: 0 !important;
    transform: translateX(30px) !important;
    margin-bottom: -70px !important;
    pointer-events: none;
}
</style>

<div id="notificationsModal" class="modal">
    <div class="modal-content modal-small" style="border-radius: 15px; overflow: hidden; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <?php
        $db = Database::getInstance();
        $notifData = $db->query(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 30", 
            [$_SESSION['user_id']], 
            "i"
        );
        $notifications = $notifData->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
        ?>
        
        <div class="modal-header" style="padding: 20px 20px 0; border: none;">
            <div style="width: 100%;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="margin:0; color: #1e293b; font-weight: 800;"><i class="fa-solid fa-bell" style="color: #10b981; margin-right: 8px;"></i> Notifications</h3>
                    <button type="button" class="modal-close" onclick="closeModal('notificationsModal')">&times;</button>
                </div>
                <div class="notif-filters">
                    <button class="notif-filter-btn active" onclick="filterNotifications('all', this)">All</button>
                    <button class="notif-filter-btn" onclick="filterNotifications('unread', this)">
                        Unread 
                        <?php if ($unreadCount > 0): ?>
                            <span class="notif-tab-count"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-body" style="max-height: 55vh; overflow-y: auto; padding: 15px; background: #ffffff;">
            <div class="notifications-list">
                <?php if (empty($notifications)): ?>
                    <div style="text-align: center; padding: 3rem; color: #94a3b8;">
                        <i class="fa-solid fa-bell-slash" style="font-size: 2.5rem; margin-bottom: 1rem; display: block; opacity: 0.5;"></i>
                        <p>No notifications yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="notification-item <?= $notif['is_read'] ? 'read' : 'unread' ?>" data-id="<?= $notif['id'] ?>">
                        <div class="notification-icon <?= $notif['type'] ?>">
                            <i class="fa-solid <?= ($notif['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-info') ?>"></i>
                        </div>
                        <div class="notification-content">
                            <p><?= htmlspecialchars($notif['message']) ?></p>
                            <span class="notification-time"><?= date('M d, g:i A', strtotime($notif['created_at'])) ?></span>
                        </div>
                        
                        <button class="notif-delete-btn" onclick="event.stopPropagation(); deleteSingleNotification(<?= $notif['id'] ?>)" title="Delete notification">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; background: #f8fafc; border-top: 1px solid #f1f5f9;">
            <button type="button" class="btn-text-danger" onclick="openModal('clearNotificationsModal')" style="color: #ef4444; background: none; border: none; font-size: 0.85rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-trash-can"></i> Clear History
            </button>
            <button type="button" class="btn btn-secondary" onclick="closeModal('notificationsModal')" style="padding: 8px 20px; font-weight: 700; border-radius: 8px; font-size: 0.85rem;">
                Close
            </button>
        </div>
    </div>
</div>

<div id="clearNotificationsModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 450px; border-top: 6px solid #ef4444; border-radius: 15px;">
        <div class="modal-header">
            <h3 style="font-weight: 800; color: #1e293b;">Clear All History</h3>
            <span class="close-btn" onclick="closeModal('clearNotificationsModal')">&times;</span>
        </div>
        <div class="modal-body text-center" style="padding: 2.5rem 2rem;">
            <div style="background: #fef2f2; color: #ef4444; width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 0 auto 1.5rem;">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <h3 style="font-weight: 800; color: #1e293b; margin-bottom: 0.5rem;">Delete Everything?</h3>
            <p style="color: #64748b; font-size: 0.95rem; line-height: 1.5;">
                This will permanently erase your entire notification history. This action cannot be undone.
            </p>
        </div>
        <div class="modal-footer" style="background: #f8fafc; padding: 15px 25px; display: flex; gap: 12px; border-top: 1px solid #f1f5f9;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('clearNotificationsModal')" style="flex: 1; font-weight: 600;">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="executeClearAll()" style="flex: 1; background: #ef4444; border: none; font-weight: 700;">Yes, Clear All</button>
        </div>
    </div>
</div>

<div id="sidebar-overlay" onclick="toggleSidebar()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9998;"></div>


<script>

function filterNotifications(filter, btn) {
    document.querySelectorAll('.notif-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const items = document.querySelectorAll('.notification-item');
    let itemsFound = 0;

    items.forEach(item => {
        if (filter === 'all') {
            item.style.setProperty('display', 'flex', 'important');
            itemsFound++;
        } else if (filter === 'unread') {
            if (item.classList.contains('unread')) {
                item.style.setProperty('display', 'flex', 'important');
                itemsFound++;
            } else {
                item.style.setProperty('display', 'none', 'important');
            }
        }
    });

    const emptyMsg = document.getElementById('notif-none-found');
    if (emptyMsg) {
        emptyMsg.style.display = (itemsFound === 0) ? 'block' : 'none';
    }
}

function openNotificationModal() {
    openModal('notificationsModal');

    const allBtn = document.querySelector('.notif-filter-btn[onclick*="all"]');
    if (allBtn) filterNotifications('all', allBtn);

    const badge = document.getElementById('notif-badge');
    const tabCount = document.getElementById('notif-tab-count');
    const unreadItems = document.querySelectorAll('.notification-item.unread');

    if (badge || unreadItems.length > 0) {
        fetch('api.php?action=mark_all_notifications_read', { method: 'POST' });

        setTimeout(() => {
            if (badge) badge.remove();
            if (tabCount) tabCount.remove();
            unreadItems.forEach(item => {
                item.classList.remove('unread');
                item.classList.add('read');
            });
        }, 5000); 
    }
}

function executeClearAll() {
    fetch('api.php?action=delete_all_notifications', { method: 'POST' })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('clearNotificationsModal');

            const list = document.querySelector('.notifications-list');
            if (list) {
                list.innerHTML = `
                    <div style="text-align: center; padding: 3rem; color: #94a3b8;">
                        <i class="fa-solid fa-check-double" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: #10b981;"></i>
                        <p>Your history is clean!</p>
                    </div>`;
            }

            if (document.getElementById('notif-badge')) document.getElementById('notif-badge').remove();
            if (document.getElementById('notif-tab-count')) document.getElementById('notif-tab-count').remove();
        }
    })
    .catch(err => console.error('Error:', err));
}

function deleteSingleNotification(id) {
    fetch('api.php?action=delete_notification', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ notification_id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`.notification-item[data-id="${id}"]`);
            if (item) {
                item.classList.add('notif-fade-out');
                setTimeout(() => {
                    item.remove();

                    const list = document.querySelector('.notifications-list');
                    const remaining = list.querySelectorAll('.notification-item');
                    if (remaining.length === 0) {
                        list.innerHTML = `
                            <div style="text-align: center; padding: 3rem; color: #94a3b8;">
                                <i class="fa-solid fa-bell-slash" style="font-size: 2.5rem; margin-bottom: 1rem; display: block; opacity: 0.5;"></i>
                                <p>No notifications yet</p>
                            </div>`;
                    }
                }, 400);
            }
        }
    })
    .catch(err => console.error('Error:', err));
}
</script>