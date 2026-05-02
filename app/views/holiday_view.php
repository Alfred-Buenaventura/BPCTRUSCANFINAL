<?php 
require_once __DIR__ . '/partials/header.php'; 
$success = $success ?? null;
$error = $error ?? null;
?>

<style>
/* Synchronize with your modal.css sidebar variable */
:root {
    --sidebar-width: 280px; 
}

/* UNIFIED MODAL OVERLAY:
   Uses 'fixed' with an offset to leave the sidebar untouched. */
.modal, 
.status-modal-overlay {
    display: none; 
    position: fixed; /* Changed from absolute to fixed */
    top: 0;
    left: var(--sidebar-width); /* Offsets from the sidebar */
    width: calc(100% - var(--sidebar-width)); /* Limits width to content area */
    height: 100vh;
    background: rgba(15, 23, 42, 0.7); 
    backdrop-filter: blur(8px); 
    z-index: 99999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

/* Responsiveness: Go full width on small screens */
@media (max-width: 1024px) {
    .modal, .status-modal-overlay { left: 0; width: 100%; }
}

/* Control logic for showing the overlays */
.status-modal-overlay.active,
.modal.active,
.modal[style*="display: flex"] {
    display: flex !important;
}

/* Standardized Modal Design - Reduced roundness (12px) */
.modal-content.modal-small {
    background: #ffffff;
    width: 95%;
    max-width: 420px;
    padding: 0;
    border-radius: 12px; 
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    animation: modalSlideUp 0.3s ease-out;
}

/* Header/Footer styling to match Account Management page */
.modal-header {
    padding: 1.25rem 1.5rem;
    background: #059669; /* Emerald Green */
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header.header-danger { background: #dc2626; } /* Red for Delete */

.modal-header h3 { 
    margin: 0; 
    font-size: 1.1rem; 
    font-weight: 700; 
    color: white; 
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-close {
    background: transparent;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    line-height: 1;
}

.modal-body { padding: 2rem 1.5rem; }

.modal-footer {
    padding: 1.25rem 1.5rem;
    background-color: #f8fafc;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: center;
    gap: 12px;
}

/* Status Icon Styling */
.status-icon-circle {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin: 0 auto 1.25rem;
}

.bg-success-light { background: #f0fdf4; color: #22c55e; }
.bg-danger-light { background: #fef2f2; color: #ef4444; }
.bg-emerald-light { background: #ecfdf5; color: #059669; }

@keyframes modalSlideUp { from { transform: translateY(10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<div class="main-body holiday-management">
    <div class="info-card-header" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; align-items: center; gap: 20px;">
        <div style="background: rgba(255,255,255,0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="fa-solid fa-file-signature"></i>
        </div>
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">DTR Management</h2>
            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem;">Manage holidays and dynamic signatory settings.</p>
        </div>
    </div>

    <div class="info-guide-wrapper" style="margin-bottom: 2.5rem; padding: 0 5px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="background: #f1f5f9; color: #64748b; width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <div style="flex: 1;">
                <p style="margin: 0; font-size: 0.92rem; color: #475569; line-height: 1.6;">
                    <span style="font-weight: 700; color: #1e293b; margin-right: 5px;">Notice:</span> 
                    <span style="color: #6366f1; font-weight: 600;">The DTR Management</span> page is used to manage the DTR for holidays and signatures. Use the 
                    forms below to adjust <span style="color: #6366f1; font-weight: 600;">Holidays</span> 
                    according to the calendar year or change the DTR <span style="color: #6366f1; font-weight: 600;">Signatory</span> section.
                </p>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 380px; gap: 2rem; align-items: start;">
        
        <div class="card" style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div style="padding: 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-calendar-alt" style="color: #059669;"></i> Holiday Records
                </h3>
                <button type="button" class="btn-sm" onclick="toggleModal('filterHolidaysModal', true)" style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer;">
                    <i class="fa-solid fa-filter"></i> Filters
                </button>
            </div>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f1f5f9; text-align: left;">
                        <th style="padding: 1rem; font-size: 0.85rem; color: #64748b;">DATE</th>
                        <th style="padding: 1rem; font-size: 0.85rem; color: #64748b;">EVENT</th>
                        <th style="padding: 1rem; font-size: 0.85rem; color: #64748b;">TYPE</th>
                        <th style="padding: 1rem; text-align: right; font-size: 0.85rem; color: #64748b;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($holidays as $h): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem; font-weight: 700; color: #059669;"><?= date('M d, Y', strtotime($h['holiday_date'])) ?></td>
                            <td style="padding: 1rem;"><?= htmlspecialchars($h['description']) ?></td>
                            <td style="padding: 1rem;">
                                <span style="background: <?= $h['type'] === 'Regular' ? '#dcfce7' : '#fef3c7' ?>; color: <?= $h['type'] === 'Regular' ? '#166534' : '#92400e' ?>; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                                    <?= $h['type'] ?>
                                </span>
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <button type="button" class="btn-sm" style="color: #ef4444; background: none; border: none; font-size: 1.1rem; cursor: pointer;" 
                                        onclick="prepDelete(<?= $h['id'] ?>, '<?= addslashes(htmlspecialchars($h['description'])) ?>')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="display: flex; flex-direction: column; gap: 2rem;">
            
            <div class="card" style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h3 style="margin-bottom: 1.5rem; font-weight: 700;"><i class="fa-solid fa-plus-circle" style="color: #059669; margin-right: 10px;"></i> Add New Holiday</h3>
                <form method="POST">
                    <?php csrf_field(); ?>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Date</label>
                        <input type="date" name="holiday_date" class="form-control" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Independence Day" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Type</label>
                        <select name="type" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                            <option value="Regular">Regular Holiday</option>
                            <option value="Special">Special Non-Working Day</option>
                        </select>
                    </div>
                    <button type="submit" name="add_holiday" style="width: 100%; background: #059669; color: white; border: none; padding: 0.85rem; border-radius: 8px; font-weight: 700; cursor: pointer;">Save Holiday</button>
                </form>
            </div>

            <div class="card" style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h3 style="margin-bottom: 1.5rem; font-weight: 700;"><i class="fa-solid fa-pen-nib" style="color: #3b82f6; margin-right: 10px;"></i> DTR Signatory</h3>
                <form method="POST">
                    <?php csrf_field(); ?>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">In-Charge Name</label>
                        <input type="text" name="in_charge_name" class="form-control" value="<?= htmlspecialchars($settings['dtr_in_charge_name'] ?? '') ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Designation/Title</label>
                        <input type="text" name="in_charge_title" class="form-control" value="<?= htmlspecialchars($settings['dtr_in_charge_title'] ?? '') ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                    </div>
                    <button type="submit" name="update_signatory" style="width: 100%; background: #334155; color: white; border: none; padding: 0.85rem; border-radius: 8px; font-weight: 700; cursor: pointer;">Update DTR Signature</button>
                </form>
            </div>
            
        </div>
    </div>
</div>

<div id="filterHolidaysModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h3><i class="fa-solid fa-filter"></i> Filter Records</h3>
            <button type="button" class="modal-close" onclick="toggleModal('filterHolidaysModal', false)">&times;</button>
        </div>

        <div class="modal-body">
            <div class="status-icon-circle bg-emerald-light">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            
            <form method="GET" action="holiday_management.php" id="filterForm">
                <div style="margin-bottom: 1.25rem; text-align: left;">
                    <label style="font-weight: 700; font-size: 0.85rem; color: #475569; margin-bottom: 8px; display: block;">Keyword Search</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>" 
                        placeholder="Search event name..." 
                        style="width:100%; padding:0.85rem; border:1.5px solid #e2e8f0; border-radius:10px; outline: none;">
                </div>
                
                <div style="display: flex; gap: 1rem; margin-bottom: 0.5rem; text-align: left;">
                    <div style="flex:1;">
                        <label style="font-weight: 700; font-size: 0.85rem; color: #475569; margin-bottom: 8px; display: block;">Start Date</label>
                        <input type="date" name="start_date" value="<?= htmlspecialchars($filters['start_date']) ?>" 
                            style="width:100%; padding:0.85rem; border:1.5px solid #e2e8f0; border-radius:10px;">
                    </div>
                    <div style="flex:1;">
                        <label style="font-weight: 700; font-size: 0.85rem; color: #475569; margin-bottom: 8px; display: block;">End Date</label>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($filters['end_date']) ?>" 
                            style="width:100%; padding:0.85rem; border:1.5px solid #e2e8f0; border-radius:10px;">
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary w-50" onclick="toggleModal('filterHolidaysModal', false)">Cancel</button>
            <button type="submit" form="filterForm" class="btn btn-primary w-50" style="background: #059669; border:none; font-weight: 700;">Apply Filters</button>
        </div>
    </div>
</div>

<style>
#filterHolidaysModal {
    display: none; 
}

#filterHolidaysModal[style*="display: flex"] {
    display: flex !important;
}
</style>

<div id="deleteHolidayModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header header-danger">
            <h3>Confirm Removal</h3>
            <button type="button" class="modal-close" onclick="toggleModal('deleteHolidayModal', false)">&times;</button>
        </div>
        <div class="modal-body text-center" style="padding: 2.5rem 1.5rem;">
            <div class="status-icon-circle bg-danger-light">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <p id="del_name" style="color: #64748b; font-weight: 500; font-size: 0.95rem;"></p>
            <form method="POST">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" id="del_id">
                <div class="modal-footer" style="padding: 2rem 0 0; border: none;">
                    <button type="button" class="btn btn-secondary w-50" onclick="toggleModal('deleteHolidayModal', false)">Cancel</button>
                    <button type="submit" name="delete_holiday" class="btn btn-danger w-50">Confirm Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($success || $error): ?>
<div class="status-modal-overlay active">
    <div class="modal-content modal-small">
        <div class="modal-header <?= $error ? 'header-danger' : '' ?>">
            <h3>Status Notification</h3>
            <button type="button" class="modal-close" onclick="this.closest('.status-modal-overlay').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body text-center">
            <div class="status-icon-circle <?= $success ? 'bg-success-light' : 'bg-danger-light' ?>">
                <i class="fa-solid <?= $success ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
            </div>
            <p style="color: #64748b; font-weight: 600; font-size: 0.95rem;">
                <?= htmlspecialchars(($success ?? $error) ?? '') ?>
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" style="min-width: 160px; border-radius: 8px;" 
                    onclick="this.closest('.status-modal-overlay').classList.remove('active')">
                Got it!
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function toggleModal(id, show) {
    const el = document.getElementById(id);
    if (el) el.style.display = show ? 'flex' : 'none';
}

function prepDelete(id, name) {
    document.getElementById('del_id').value = id;
    document.getElementById('del_name').textContent = "Are you sure you want to remove '" + name + "'?";
    toggleModal('deleteHolidayModal', true);
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>