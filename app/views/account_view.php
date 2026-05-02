<?php 
require_once __DIR__ . '/partials/header.php'; 
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>

.info-card-header {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.table-row-hover:hover {
    background-color: #f8fafc !important;
}

button:hover {
    filter: brightness(0.95);
    transform: translateY(-1px);
}

@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.phone-input-container {
    display: flex;
    align-items: center;
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.phone-input-container:focus-within {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.country-prefix {
    border: none;
    background: #f8fafc;
    padding: 10px 12px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #475569;
    border-right: 1px solid #e2e8f0;
    outline: none;
    cursor: pointer;
}

.phone-field {
    border: none !important;
    padding: 10px 15px !important;
    font-size: 0.95rem !important;
    width: 100%;
    outline: none !important;
    box-shadow: none !important;
}

/* Status Icon Circle Styling */
.status-icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    margin: 0 auto 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

/* Modal Status Colors */
.bg-success-light { background: #ecfdf5; color: #10b981; border: 2px solid #d1fae5; }
.bg-warning-light { background: #fffbeb; color: #f59e0b; border: 2px solid #fef3c7; }
.bg-danger-light { background: #fef2f2; color: #ef4444; border: 2px solid #fee2e2; }

/* Action Button Hover States */
.action-icon-btn:hover {
    transform: scale(1.1);
    filter: brightness(0.9);
}

</style>
<div class="main-body user-management">
    <div class="info-card-header" style="background: linear-gradient(135deg, #1e293b 0%, #1e293b 100%); color: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; align-items: center; gap: 20px;">
        <div style="background: rgba(255,255,255,0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="fa-solid fa-users-gear"></i>
        </div>
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Faculty & Staff Management</h2>
            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem;">Register new personnel, manage roles, and update account credentials.</p>
        </div>
    </div>

    <div class="info-guide-wrapper" style="margin-bottom: 2.5rem; padding: 0 5px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="background: #f1f5f9; color: #64748b; width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div style="flex: 1;">
                <p style="margin: 0; font-size: 0.92rem; color: #475569; line-height: 1.6;">
                    <span style="font-weight: 700; color: #1e293b; margin-right: 5px;">Security Notice:</span>
                    Changes to user roles or account status take effect immediately. Ensure <span style="color: #6366f1; font-weight: 600;">Faculty IDs</span> match their physical biometric records for seamless logging.
                </p>
            </div>
        </div>
    </div>
    <div id="toastContainer" class="toast-container"></div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon emerald"><i class="fa-solid fa-users"></i></div>
            <div class="stat-details">
                <p>Total Active</p>
                <div class="stat-value emerald"><?= $stats['total_active'] ?? 0 ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow"><i class="fa-solid fa-briefcase"></i></div>
            <div class="stat-details">
                <p>Staff Accounts</p>
                <div class="stat-value yellow"><?= $stats['non_admin_active'] ?? 0 ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class="fa-solid fa-user-shield"></i></div>
            <div class="stat-details">
                <p>Admins</p>
                <div class="stat-value red"><?= $stats['admin_active'] ?? 0 ?></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="tabs">
            <button class="tab-btn <?= $activeTab === 'csv' ? 'active' : '' ?>" onclick="showTab(event, 'csv')">
                <i class="fa-solid fa-file-csv"></i> CSV Bulk Import
            </button>
            <button class="tab-btn <?= $activeTab === 'create' ? 'active' : '' ?>" onclick="showTab(event, 'create')">
                <i class="fa-solid fa-user-plus"></i> Account Creation
            </button>
            <button class="tab-btn <?= $activeTab === 'view' ? 'active' : '' ?>" onclick="showTab(event, 'view')">
                <i class="fa-solid fa-list"></i> View All Accounts
            </button>
        </div>

        <div id="csvTab" class="tab-content <?= $activeTab === 'csv' ? 'active' : '' ?>">
            <div class="card-body">
                <div class="csv-section-header">
                    <i class="fa-solid fa-file-arrow-up"></i>
                    <h3>Bulk User Import (CSV)</h3>
                </div>
                <p class="csv-subtitle">Import multiple user accounts from a CSV file</p>

                <div class="download-template-box" style="border: 2px solid var(--blue-200); border-radius: 8px; padding: 1.5rem; background: #f8fafc; margin-bottom: 20px;">
                    <div class="download-template-inner" style="display: flex; align-items: center; gap: 20px;">
                        <div class="step-badge" style="background: var(--blue-600); color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800;">1</div>
                        <div class="download-template-content">
                            <h4 style="margin: 0;">Download CSV Template <span>to ensure correct data mapping.</span></h4>
                            <button type="button" class="btn btn-primary" style="margin-top: 10px;" onclick="confirmDownload()">
                                <i class="fa-solid fa-download"></i> Download Template
                            </button>
                        </div>
                    </div>
                </div>

                <div class="csv-requirements" style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 20px; margin-bottom: 25px; border-left: 5px solid #fbbf24;">
                    <h4 style="color: #92400e; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> CSV Format Requirements:
                    </h4>
                    <ul style="color: #78350f; font-size: 0.95rem; line-height: 1.6; padding-left: 20px;">
                        <li>Columns (in order): <strong>Faculty ID, Last Name, First Name, Middle Name, Username, Role, Email, Phone</strong></li>
                        <li>All users will be created with default password: <strong>@defaultpass123</strong></li>
                        <li>Users must change password on first login</li>
                        <li>Duplicate Faculty IDs will be skipped</li>
                    </ul>
                </div>

                <form method="POST" enctype="multipart/form-data" id="csvImportForm">
                    <?php csrf_field(); ?>
                    <div class="csv-upload-wrapper" style="border: 2px dashed #cbd5e1; padding: 2rem; border-radius: 12px; text-align: center; background: #f8fafc; transition: all 0.2s;">
                        <input type="file" name="csv_file" id="csv_file_input" accept=".csv" style="display: none;" onchange="displayFileName(this)">
                        
                        <label for="csv_file_input" style="cursor: pointer; display: block;">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size: 3rem; color: #10b981; margin-bottom: 1rem;"></i>
                            <p style="font-weight: 700; color: #1e293b; margin-bottom: 5px;">Click to upload or drag and drop</p>
                            <p style="color: #64748b; font-size: 0.85rem;">Only .csv files are supported</p>
                        </label>

                        <div id="file-name-display" style="margin-top: 15px; display: none; align-items: center; justify-content: center; gap: 10px; padding: 10px; background: #ecfdf5; border: 1px solid #10b981; border-radius: 8px;">
                            <i class="fa-solid fa-file-csv" style="color: #10b981;"></i>
                            <span id="file-name-text" style="font-weight: 600; color: #065f46; font-size: 0.9rem;"></span>
                            <button type="button" onclick="clearSelectedFile()" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1rem;"><i class="fa-solid fa-circle-xmark"></i></button>
                        </div>
                    </div>

                    <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                        <button type="submit" name="import_csv" class="btn btn-primary" style="background: #10b981; border: none; padding: 12px 30px; font-weight: 700; border-radius: 8px;">
                            <i class="fa-solid fa-file-import"></i> Start Bulk Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div id="createTab" class="tab-content <?= $activeTab === 'create' ? 'active' : '' ?>">
            <div class="card-body">
                <div class="user-creation-header">
                    <i class="fa-solid fa-user-plus"></i>
                    <h3>Create New User Account</h3>
                </div>
                <p class="user-creation-subtitle">Create a single user account with default password: <strong>@defaultpass123</strong></p>

                <form method="POST" style="margin-top: 1.5rem;">
                    <?php csrf_field(); ?>
                    <div class="user-creation-form-grid">
                        <div class="form-group">
                            <label>Faculty/ID Number <span class="required">*</span></label>
                            <input type="text" name="faculty_id" class="form-control" placeholder="e.g., STAFF001" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="e.g., staff@bulacan.edu.ph" required>
                        </div>
                        <div class="form-group">
                            <label>First Name <span class="required">*</span></label>
                            <input type="text" name="first_name" class="form-control" placeholder="Enter first name" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name <span class="required">*</span></label>
                            <input type="text" name="last_name" class="form-control" placeholder="Enter last name" required>
                        </div>
                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" placeholder="Enter middle name (optional)">
                        </div>
                        <div class="form-group">
                            <label>Phone Number <span class="required">*</span></label>
                            <div class="phone-input-container">
                                <select name="country_code" class="country-prefix">
                                    <option value="+63" selected>🇵🇭 +63</option>
                                    <option value="+1">🇺🇸 +1</option>
                                </select>
                                <input type="text" 
                                    name="phone" 
                                    id="phoneInput"
                                    class="form-control phone-field" 
                                    placeholder="9171234567" 
                                    maxlength="10" 
                                    required>
                            </div>
                            <small class="text-muted">Enter the 10 digits following the +63 prefix.</small>
                        </div>
                        <div class="form-group form-group-full">
                            <label>Role/Position <span class="required">*</span></label>
                            <select name="role" class="form-control" required>
                                <option value="">Select a role</option>
                                <option value="Full Time Teacher">Full Time Teacher</option>
                                <option value="Part Time Teacher">Part Time Teacher</option>
                                <option value="Registrar">Registrar</option>
                                <option value="Admission">Admission</option>
                                <option value="OPRE">OPRE</option>
                                <option value="Scholarship Office">Scholarship Office</option>
                                <option value="Doctor">Doctor</option>
                                <option value="Nurse">Nurse</option>
                                <option value="Guidance Office">Guidance Office</option>
                                <option value="Library">Library</option>
                                <option value="Finance">Finance</option>
                                <option value="Student Affair">Student Affair</option>
                                <option value="Security Personnel and Facility Operator">Security Personnel and Facility Operator</option>
                                <option value="OVPA">OVPA</option>
                                <option value="MIS">MIS</option>
                            </select>
                        </div>
                    </div>

                    <div class="password-info-box">
                        <i class="fa-solid fa-circle-info"></i>
                        <div>
                            <strong>Note:</strong> User will be assigned the default password <strong>@defaultpass123</strong> and will be prompted to change it on first login.
                        </div>
                    </div>

                    <button type="submit" name="create_user" class="btn btn-primary btn-full-width">
                        <i class="fa-solid fa-user-plus"></i> Create Account
                    </button>
                </form>
            </div>
        </div>

        <div id="viewTab" class="tab-content <?= $activeTab === 'view' ? 'active' : '' ?>">
    <div class="table-container" style="background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #f1f5f9; overflow: hidden;">
        
        <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9; background: #fafafa;">
            <h2 style="margin: 0; font-size: 1.25rem; color: #1e293b; font-weight: 800;">All Active Accounts</h2>
            <p style="margin: 4px 0 0; font-size: 0.85rem; color: #64748b;">
                Managing <strong><?= count($activeUsers) ?></strong> active personnel
            </p>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #f1f5f9;">
                        <th style="padding: 16px 24px; font-size: 0.75rem; font-weight: 800; color: #034cb3; text-transform: uppercase;">Personnel ID</th>
                        <th style="padding: 16px 24px; font-size: 0.75rem; font-weight: 800; color: #03742e; text-transform: uppercase;">Full Name</th>
                        <th style="padding: 16px 24px; font-size: 0.75rem; font-weight: 800; color: #b90c00; text-transform: uppercase;">Contact Info</th>
                        <th style="padding: 16px 24px; font-size: 0.75rem; font-weight: 800; color: #ac780a; text-transform: uppercase;">Role</th>
                        <th style="padding: 16px 24px; font-size: 0.75rem; font-weight: 800; color: #160288; text-transform: uppercase; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($activeUsers) > 0): ?>
                        <?php foreach ($activeUsers as $user): ?>
                        <tr class="table-row-hover" style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 16px 24px;">
                                <span style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 6px; font-family: monospace; font-weight: 700; font-size: 0.85rem;">
                                    <?= htmlspecialchars($user['faculty_id'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td style="padding: 16px 24px; font-weight: 700; color: #1e293b;">
                                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                            </td>
                            <td style="padding: 16px 24px;">
                                <div style="font-size: 0.85rem; color: #475569;">
                                    <div><i class="fa-solid fa-envelope" style="width: 18px; color: #cbd5e1;"></i> <?= htmlspecialchars($user['email'] ?? 'No Email') ?></div>
                                    <div><i class="fa-solid fa-phone" style="width: 18px; color: #cbd5e1;"></i> <?= htmlspecialchars($user['phone'] ?? 'No Phone') ?></div>
                                </div>
                            </td>
                            <td style="padding: 16px 24px;">
                                <span style="background: #eff6ff; color: #3b82f6; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; border: 1px solid #dbeafe;">
                                    <?= htmlspecialchars($user['role'] ?? 'User') ?>
                                </span>
                            </td>
                            <td style="padding: 16px 24px; text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <button class="action-icon-btn" onclick='editUser(<?= json_encode($user) ?>)' title="Edit Account">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button class="action-icon-btn archive-btn" onclick="confirmArchive(<?= $user['id'] ?>, '<?= htmlspecialchars($user['first_name']) ?>')" title="Archive User">
                                        <i class="fa-solid fa-box-archive"></i>
                                    </button>
                                    <button class="action-icon-btn qr-btn" 
                                            onclick="confirmQrRegen(<?= $user['id'] ?>, '<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>')" 
                                            title="Regenerate QR Code">
                                        <i class="fa-solid fa-qrcode"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="padding: 40px; text-align: center; color: #94a3b8;">No active accounts found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            <button class="btn-view-archive" onclick="openArchivedModal()" style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: #f59e0b; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2);">
                <i class="fa-solid fa-archive"></i> 
                View Archive (<?= count($archivedUsers) ?>)
            </button>
        </div>
    </div>
</div>

    <div id="archivedModal" class="modal">
        <div class="modal-content modal-xl"> 
            <div class="modal-header">
                <h3><i class="fa-solid fa-box-archive"></i> Archived Personnel Accounts</h3>
                <button class="modal-close" onclick="closeArchivedModal()">&times;</button>
            </div>
            <div class="modal-body" style="min-height: 400px; display: flex; flex-direction: column;">
                <div class="table-responsive">
                <?php if (count($archivedUsers) > 0): ?>
                    <table class="data-table">
                        <thead style="background: var(--gray-100);">
                            <tr>
                                <th style="padding: 15px;">FACULTY ID</th>
                                <th style="padding: 15px;">NAME</th>
                                <th style="padding: 15px;">EMAIL</th>
                                <th style="padding: 15px;">ROLE</th>
                                <th style="padding: 15px; text-align: center;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($archivedUsers as $user): ?>
                                <tr style="border-bottom: 1px solid var(--gray-200);">
                                    <td style="padding: 12px;"><span class="id-badge" style="background: var(--gray-100); padding: 4px 8px; border-radius: 4px; font-weight: 600;"><?= htmlspecialchars($user['faculty_id']) ?></span></td>
                                    <td style="padding: 12px; font-weight: 600;"><?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></td>
                                    <td style="padding: 12px;"><?= htmlspecialchars($user['email']) ?></td>
                                    <td style="padding: 12px;"><span class="role-badge"><?= htmlspecialchars($user['role']) ?></span></td>
                                    <td style="padding: 12px; text-align: center; white-space: nowrap;">
                                        <?php $name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES); ?>
                                        <button class="btn btn-sm btn-success" onclick="confirmRestore(<?= $user['id'] ?>, '<?= $name ?>')">
                                            <i class="fa-solid fa-rotate-left"></i> Restore
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $user['id'] ?>, '<?= $name ?>')">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="flex-grow: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; color: #94a3b8; padding: 40px;">
                        <i class="fa-solid fa-box-open" style="font-size: 5rem; margin-bottom: 20px; opacity: 0.4;"></i>
                        <h3 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #64748b;">No Archived Accounts</h3>
                        <p style="font-size: 1.1rem; margin-top: 10px;">When an account is archived, it will appear here for restoration or permanent removal.</p>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="editUserModal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h3><i class="fa-solid fa-user-pen"></i> Edit User Information</h3>
                <button type="button" class="modal-close" onclick="closeModal('editUserModal')">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div style="background: #f0f9ff; padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 12px; border: 1px solid #e0f2fe;">
                    <i class="fa-solid fa-circle-info" style="color: #0ea5e9; margin-top: 3px;"></i>
                    <div>
                        <p style="font-size: 0.95rem; color: #0369a1; margin: 0; font-weight: 600;">
                            You are editing the account details of <span id="editingUserName" style="text-decoration: underline;"></span>.
                        </p>
                        <p style="font-size: 0.85rem; color: #0c4a6e; margin: 5px 0 0 0;">
                            Please ensure all updated information is accurate before saving changes.
                        </p>
                    </div>
                </div>

                <form method="POST">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label>First Name <span class="required">*</span></label>
                        <input type="text" name="first_name" id="editFirstName" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label>Last Name <span class="required">*</span></label>
                        <input type="text" name="last_name" id="editLastName" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" id="editMiddleName" class="form-control">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" id="editEmail" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label>Phone</label>
                        <input type="text" name="phone" id="editPhone" class="form-control">
                    </div>
                    
                    <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                        <button type="submit" name="edit_user" class="btn btn-primary">
                            <i class="fa-solid fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h3 id="confirmTitle">Confirm Action</h3>
                <button type="button" class="modal-close" onclick="closeConfirmModal()">&times;</button>
            </div>
            <div class="modal-body text-center">
                <div id="modalIconContainer" class="status-icon-circle"><i id="modalIcon" class="fa-solid"></i></div>
                <p id="confirmMessage" style="color: #64748b; font-size: 1rem; line-height: 1.6; font-weight: 500;"></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary w-50" onclick="closeConfirmModal()">Cancel</button>
                <button class="btn btn-primary w-50" id="confirmActionBtn" style="border:none; font-weight: 700;">Confirm</button>
            </div>
        </div>
    </div>

    <div id="duplicateUserModal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header" style="background-color: var(--yellow-50);">
                <h3><i class="fa-solid fa-triangle-exclamation"></i> Duplicate Account</h3>
            </div>
            <div class="modal-body">
                <p class="fs-large" style="color: var(--gray-700);">
                    An account with this Faculty ID already exists in the system.
                </p>
                <p class="fs-small" style="color: var(--gray-600); margin-top: 1rem;">
                    Please check the "View All Accounts" tab to find the existing user. Duplicate accounts cannot be created.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="closeModal('duplicateUserModal')">OK</button>
            </div>
        </div>
    </div>

    <div id="qrRegenModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header" style="background-color: #fffbeb; border-bottom: 1px solid #fde68a;">
            <h3 style="color: #92400e;"><i class="fa-solid fa-triangle-exclamation"></i> Security Warning</h3>
            <button type="button" class="modal-close" onclick="closeModal('qrRegenModal')">&times;</button>
        </div>
        <div class="modal-body text-center">
            <div class="status-icon-circle" style="background: #fef3c7; color: #d97706;">
                <i class="fa-solid fa-sync-alt"></i>
            </div>
            <p style="color: #1e293b; font-weight: 700; margin-top: 15px;">Regenerating for <span id="qrTargetName">---</span>?</p>
            <div style="background: #fff7ed; border: 1px solid #ffedd5; padding: 12px; border-radius: 8px; margin-top: 10px;">
                <p style="color: #9a3412; font-size: 0.9rem; line-height: 1.5; margin: 0;">
                    <strong>Notice:</strong> Regenerating the QR code will <strong>invalidate</strong> the QR Code sent in the email. Make sure to use the one in your account &rarr; <strong>My Profile QR Code</strong>, from here on out after regenerating. Thank you!
                </p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary w-50" onclick="closeModal('qrRegenModal')">Cancel</button>
            <button class="btn btn-warning w-50" id="confirmQrBtn" style="font-weight: 700;">Regenerate Now</button>
        </div>
    </div>
</div>

    <div id="doubleConfirmModal" class="modal">
    <div class="modal-content" style="border-top: 5px solid #ef4444;">
        <div class="modal-header">
            <h3 style="color: #ef4444;"><i class="fa-solid fa-triangle-exclamation"></i> Critical Confirmation</h3>
            <button type="button" class="modal-close" onclick="closeDoubleConfirmModal()">&times;</button>
        </div>
        <div class="modal-body text-center">
            <div class="status-icon-circle bg-danger-light"><i class="fa-solid fa-trash-can"></i></div>
            <p style="color: #64748b; font-size: 0.95rem; line-height: 1.5;">This action <strong>cannot be undone</strong>. The account and all associated data will be removed forever.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary w-50" onclick="closeDoubleConfirmModal()">Go Back</button>
            <button class="btn btn-danger w-50" onclick="executeDeleteAction()" style="background:#ef4444; border:none; font-weight: 700;">Delete Forever</button>
        </div>
    </div>
</div>
    
    <div id="operationStatusModal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header"><h3 id="statusModalTitle">Status</h3><button type="button" class="modal-close" onclick="closeModal('operationStatusModal')">&times;</button></div>
            <div class="modal-body text-center">
                <div id="statusIconContainer" class="status-icon-circle"><i id="statusIcon" class="fa-solid"></i></div>
                <p id="statusModalMessage" style="color: #64748b; font-weight: 500;"></p>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-primary" style="min-width: 160px; border-radius: 50px;" onclick="closeModal('operationStatusModal')">Got it!</button></div>
        </div>
    </div>
</div>

<div id="downloadConfirmModal" class="modal">
    <div class="modal-content modal-small" style="border-top: 5px solid #2563eb; border-radius: 16px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-file-csv"></i> Download Template</h3>
            <button type="button" class="modal-close" onclick="closeModal('downloadConfirmModal')">&times;</button>
        </div>
        <div class="modal-body text-center" style="padding: 2rem;">
            <div style="background: #eff6ff; color: #2563eb; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1.5rem;">
                <i class="fa-solid fa-download"></i>
            </div>
            <p style="color: #1e293b; font-weight: 700; font-size: 1.1rem;">Ready to download?</p>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 8px;">This will download the official <strong>bpc_template.csv</strong> file. Ensure you follow the column order exactly for a successful bulk import.</p>
        </div>
        <div class="modal-footer" style="gap: 12px;">
            <button type="button" class="btn btn-secondary w-50" onclick="closeModal('downloadConfirmModal')">Cancel</button>
            
            <button type="button" class="btn btn-primary w-50" onclick="handleDownloadAndClose()" 
                    style="background: #10b981; color: white; font-weight: 700; height: 45px; border-radius: 10px; border: none; cursor: pointer;">
                <i class="fa-solid fa-download" style="margin-right: 8px;"></i> Download Now
            </button>
        </div>
    </div>
</div>

</div>
<form id="archive-form" method="POST" action="create_account.php?tab=view" style="display:none;">
    <?php csrf_field(); ?>
    <input type="hidden" name="user_id" id="archive-id">
    <input type="hidden" name="archive_user" value="1">
</form>

<form id="restore-form" method="POST" action="create_account.php?tab=view" style="display:none;">
    <?php csrf_field(); ?>
    <input type="hidden" name="user_id" id="restore-id">
    <input type="hidden" name="restore_user" value="1">
</form>

<script>
const csrfToken = "<?= $_SESSION['csrf_token'] ?? '' ?>";

let pendingAction = null;
let deleteUserId = null;
let deleteUserName = null;

function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) { 
        modal.style.display = 'flex'; 
        document.body.style.overflow = 'hidden'; 
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) { 
        modal.style.display = 'none'; 
        document.body.style.overflow = 'auto'; 
    }
}

function handleDownloadAndClose() {
    window.location.href = 'create_account.php?action=download_template';
    setTimeout(() => {
        closeModal('downloadConfirmModal');
    }, 500);
}

function openArchivedModal() { openModal('archivedModal'); }
function closeArchivedModal() { closeModal('archivedModal'); }
function closeConfirmModal() { closeModal('confirmModal'); }
function closeDoubleConfirmModal() { closeModal('doubleConfirmModal'); }

function triggerUniversalConfirm(config) {
    setModalStyle(config.type);
    document.getElementById('confirmTitle').innerText = config.title;
    document.getElementById('confirmMessage').textContent = config.message;
    
    const confirmBtn = document.getElementById('confirmActionBtn');
    confirmBtn.onclick = function() {
        config.action();
        if (config.type !== 'delete_trigger') {
            closeModal('confirmModal');
        }
    };
    openModal('confirmModal');
}

function setModalStyle(type) {
    const container = document.getElementById('modalIconContainer');
    const icon = document.getElementById('modalIcon');
    const btn = document.getElementById('confirmActionBtn');
    
    if (type === 'archive') {
        container.className = 'status-icon-circle bg-warning-light';
        icon.className = 'fa-solid fa-box-archive';
        btn.className = 'btn btn-warning w-50';
    } else if (type === 'restore') {
        container.className = 'status-icon-circle bg-success-light';
        icon.className = 'fa-solid fa-rotate-left';
        btn.className = 'btn btn-success w-50';
    } else if (type === 'delete' || type === 'delete_trigger') {
        container.className = 'status-icon-circle bg-danger-light';
        icon.className = 'fa-solid fa-trash';
        btn.className = 'btn btn-danger w-50';
    }
}

function confirmDownload() {
    openModal('downloadConfirmModal');
}

function submitHiddenForm(data) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'create_account.php?tab=view';
    
    const csrf = document.createElement('input');
    csrf.type = 'hidden'; csrf.name = 'csrf_token'; csrf.value = csrfToken;
    form.appendChild(csrf);

    for (const key in data) {
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = key; input.value = data[key];
        form.appendChild(input);
    }
    document.body.appendChild(form);
    form.submit();
}

function editUser(user) {
    const fn = user.first_name || '';
    const ln = user.last_name || '';
    document.getElementById('editingUserName').textContent = fn + " " + ln;
    document.getElementById('editUserId').value = user.id || '';
    document.getElementById('editFirstName').value = fn;
    document.getElementById('editLastName').value = ln;
    document.getElementById('editMiddleName').value = user.middle_name || '';
    document.getElementById('editEmail').value = user.email || '';
    document.getElementById('editPhone').value = user.phone || '';
    openModal('editUserModal');
}

function confirmArchive(userId, userName) {
    triggerUniversalConfirm({
        type: 'archive',
        title: 'Confirm Archive',
        message: `Are you sure you want to archive ${userName}?`,
        action: function() { submitHiddenForm({ user_id: userId, archive_user: 1 }); }
    });
}

function confirmRestore(userId, userName) {
    triggerUniversalConfirm({
        type: 'restore',
        title: 'Confirm Restore',
        message: `Restore access for ${userName}?`,
        action: function() { submitHiddenForm({ user_id: userId, restore_user: 1 }); }
    });
}

function confirmDelete(userId, userName) {
    deleteUserId = userId;
    deleteUserName = userName;
    triggerUniversalConfirm({
        type: 'delete_trigger',
        title: 'Confirm Delete',
        message: `Permanently delete ${userName}? This cannot be undone.`,
        action: function() {
            closeModal('confirmModal');
            setTimeout(() => {
                const doubleMsg = document.getElementById('doubleConfirmMessage');
                if (doubleMsg) doubleMsg.textContent = `Are you absolutely sure you want to delete ${userName}?`;
                openModal('doubleConfirmModal');
            }, 300);
        }
    });
}

function executeDeleteAction() {
    if (deleteUserId) {
        submitHiddenForm({ user_id: deleteUserId, delete_user: 1 });
    }
}

function confirmQrRegen(userId, userName) {
    document.getElementById('qrTargetName').textContent = userName;
    openModal('qrRegenModal');
    
    const regenBtn = document.getElementById('confirmQrBtn');
    
    regenBtn.onclick = function() {
        regenBtn.disabled = true;
        regenBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

        const fd = new FormData();
        fd.append('user_id', userId);
        fd.append('csrf_token', csrfToken);

        fetch('create_account.php?action=regenerate_qr', { 
            method: 'POST', 
            body: fd 
        })
        .then(res => res.json())
        .then(data => {
            closeModal('qrRegenModal');
            if(data.success) {
                document.getElementById('statusIcon').className = 'fa-solid fa-circle-check';
                document.getElementById('statusModalTitle').textContent = "QR Successfully Updated";
                document.getElementById('statusModalMessage').innerHTML = `
                    <div style="text-align: left; font-size: 0.95rem;">
                        <p>A new token has been generated for <strong>${userName}</strong>.</p>
                        <p style="color: #ef4444; font-weight: 600;">Reminder: The old QR code is now inactive.</p>
                    </div>`;
                openModal('operationStatusModal');
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => alert("System Error: Could not update the QR token."))
        .finally(() => {
            regenBtn.disabled = false;
            regenBtn.innerHTML = 'Regenerate Now';
        });
    };
}

document.addEventListener('DOMContentLoaded', function() {
    const flashMessage = <?= json_encode($flashMessage ?? '') ?>;
    const flashType = <?= json_encode($flashType ?? '') ?>;
    
    if (flashMessage && flashMessage !== "") {
        if (flashType === 'duplicate') { 
            openModal('duplicateUserModal'); 
        } else {
            const iconContainer = document.getElementById('statusIconContainer');
            const icon = document.getElementById('statusIcon');
            const title = document.getElementById('statusModalTitle');
            const message = document.getElementById('statusModalMessage');

            message.textContent = flashMessage;

            if (flashType === 'success') {
                iconContainer.className = 'status-icon-circle bg-success-light';
                icon.className = 'fa-solid fa-circle-check';
                title.textContent = 'Success';
                title.style.color = '#065f46';
            } else if (flashType === 'error') {
                iconContainer.className = 'status-icon-circle bg-danger-light';
                icon.className = 'fa-solid fa-circle-xmark';
                title.textContent = 'Error';
                title.style.color = '#991b1b';
            }
            openModal('operationStatusModal');
        }
    }
});

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        closeModal(event.target.id);
    }
};

window.showTab = function(event, tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById(tab + 'Tab').classList.add('active');
    if (event) event.target.closest('.tab-btn').classList.add('active');
};

function displayFileName(input) {
    const display = document.getElementById('file-name-display');
    const text = document.getElementById('file-name-text');
    
    if (input.files && input.files[0]) {
        const fileName = input.files[0].name;
        text.textContent = fileName;
        display.style.display = 'flex';
        input.closest('.csv-upload-wrapper').style.borderColor = '#10b981';
        input.closest('.csv-upload-wrapper').style.background = '#f0fdf4';
    }
}

function clearSelectedFile() {
    const input = document.getElementById('csv_file_input');
    const display = document.getElementById('file-name-display');
    const wrapper = input.closest('.csv-upload-wrapper');
    
    input.value = '';
    display.style.display = 'none';

    wrapper.style.borderColor = '#cbd5e1';
    wrapper.style.background = '#f8fafc';
}

</script>
<?php require_once __DIR__ . '/partials/footer.php'; ?>