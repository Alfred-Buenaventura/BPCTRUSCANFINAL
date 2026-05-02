<?php 
require_once __DIR__ . '/partials/header.php';

if (!function_exists('renderScheduleTable')) {
    function renderScheduleTable($schedules, $isAdmin, $showActions = true, $showCheckbox = false, $uid = null) {
        echo '<table class="user-approved-table">';
        echo '<colgroup>';
        if ($showCheckbox) echo '<col style="width: 45px;">';
        echo '    <col style="width: 120px;">';             
        echo '    <col style="width: auto;">';          
        echo '    <col style="width: 140px;">';              
        echo '    <col style="width: 220px;">';            
        echo '    <col style="width: 180px;">';     
        if ($isAdmin && $showActions) echo '<col style="width: 110px;">';
        echo '</colgroup>';
        echo '<thead><tr>';
        if ($showCheckbox) {
            echo '<th><input type="checkbox" class="select-all-pending-rows" onchange="toggleUserGroup(this, \''.$uid.'\')"></th>';
        }
        echo '<th>Day</th><th>Subject / Duty</th><th>Type</th><th>Time Schedule</th><th>Room</th>';
        if ($isAdmin && $showActions) echo '<th>Actions</th>';
        echo '</tr></thead><tbody>';

        if (!empty($schedules)) {
            $dayOrder = [
                'Monday'    => 1,
                'Tuesday'   => 2,
                'Wednesday' => 3,
                'Thursday'  => 4,
                'Friday'    => 5,
                'Saturday'  => 6,
                'Sunday'    => 7
            ];

            usort($schedules, function($a, $b) use ($dayOrder) {
                $da = $dayOrder[$a['day_of_week']] ?? 8;
                $db = $dayOrder[$b['day_of_week']] ?? 8;
                
                if ($da !== $db) {
                    return $da - $db;
                }
                return strtotime($a['start_time']) - strtotime($b['start_time']);
            });

            foreach ($schedules as $sched) {
                $type = $sched['type'] ?? 'Class';
                $isOffice = ($type === 'Office');
                $typeClass = $isOffice ? 'office' : 'class';
                $typeIcon = $isOffice ? 'fa-building' : 'fa-chalkboard-user';

                echo '<tr>';
                if ($showCheckbox) {
                    echo '<td><input type="checkbox" name="selected_schedules[]" value="'.$sched['id'].'" class="pending-row-checkbox check-user-'.$uid.'"></td>';
                }

                echo '<td><span class="day-badge">' . $sched['day_of_week'] . '</span></td>';
                echo '<td style="font-weight: 600; color: #1e293b;">' . htmlspecialchars($sched['subject']) . '</td>';
                echo '<td><span class="type-pill '.$typeClass.'"><i class="fa-solid '.$typeIcon.'"></i> '.htmlspecialchars($type).'</span></td>';
                echo '<td class="col-time-blue"><i class="fa-regular fa-clock" style="margin-right: 5px; opacity: 0.6;"></i>' . 
                     date('g:i A', strtotime($sched['start_time'])) . ' - ' . 
                     date('g:i A', strtotime($sched['end_time'])) . '</td>';
                
                echo '<td>' . htmlspecialchars($sched['room']) . '</td>';

                if ($isAdmin && $showActions) {
                    echo '<td>';
                    echo '<button type="button" class="btn-icon" onclick="openEditModal(' . 
                        $sched['id'] . ', ' . 
                        $sched['user_id'] . ', \'' . 
                        $sched['day_of_week'] . '\', \'' . 
                        htmlspecialchars($sched['subject'], ENT_QUOTES) . '\', \'' . 
                        date('H:i', strtotime($sched['start_time'])) . '\', \'' . 
                        date('H:i', strtotime($sched['end_time'])) . '\', \'' . 
                        htmlspecialchars($sched['room'], ENT_QUOTES) . '\', \'' . 
                        $type . 
                        '\')"><i class="fa-solid fa-pen"></i></button> ';
                    echo '<button type="button" class="btn-icon danger" onclick="openDeleteModal(' . $sched['id'] . ', ' . $sched['user_id'] . ')"><i class="fa-solid fa-trash"></i></button>';
                    echo '</td>';
                }
                echo '</tr>';
            }
        } else {
            $totalCols = ($isAdmin && $showActions) ? 6 : 5;
            if ($showCheckbox) $totalCols++;
            echo '<tr><td colspan="'.$totalCols.'" style="text-align:center; padding: 2.5rem; color: #64748b;">
                    <i class="fa-solid fa-calendar-xmark" style="display:block; font-size:2rem; margin-bottom:10px; opacity:0.3;"></i>
                    No schedules found.
                  </td></tr>';
        }
        echo '</tbody></table>';
    }
}
?>
<link rel="stylesheet" href="css/schedule.css">

<div class="main-body schedule-management">
    <div class="info-card-header">
        <div style="background: rgba(255,255,255,0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="fa-solid fa-calendar-days"></i>
        </div>
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">My Duty Schedules</h2>
            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem;">Manage and monitor approved and pending duty sessions.</p>
        </div>
    </div>

    <div class="info-guide-wrapper" style="margin-top: 2rem; margin-bottom: 1rem; padding: 0 5px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 20px;">
            <div style="background: #f1f5f9; color: #64748b; width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <div style="flex: 1;">
                <p style="margin: 0; font-size: 0.92rem; color: #475569; line-height: 1.6;">
                    <span style="font-weight: 700; color: #1e293b; margin-right: 5px;">Planning Note:</span>
                    Define standard <span style="color: #6366f1; font-weight: 600;">Work Hours</span> or <span style="color: #6366f1; font-weight: 600;">Office Duties</span> to automate attendance.
                </p>
            </div>
        </div>
    </div>

    <?php if (!$isAdmin): ?>
    <div class="filter-export-section card" style="border-radius: 12px; margin-bottom: 2rem;">
        <div class="card-header" style="background: #059669; color: white; border-radius: 12px 12px 0 0; padding: 1rem 1.5rem;">
            <h3 style="margin:0; font-size: 1.1rem;"><i class="fa-solid fa-calendar-check"></i> Filter Options</h3>
        </div>
        <div class="card-body" style="padding: 1.5rem;">
            <form method="GET">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; align-items: flex-end;">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control">
                            <option value="">-- All Categories --</option>
                            <option value="Academic" <?= ($filters['category'] ?? '') == 'Academic' ? 'selected' : '' ?>>Academic</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" style="width: 100%; background: #059669; border: none; padding: 10px; font-weight: 700; border-radius: 8px;">Apply Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($error): ?> <div class="alert alert-error"><?= htmlspecialchars($error) ?></div> <?php endif; ?>
    <?php if ($success): ?> <div class="alert alert-success"><?= htmlspecialchars($success) ?></div> <?php endif; ?>

    <div class="card" id="schedule-card">
        <div class="card-header card-header-flex">
            <div>
                <h3>Schedule Management</h3>
                <p>Monitor duty schedules</p>
            </div>
            <?php if (!$isAdmin): ?>
                <button class="btn btn-primary btn-sm" onclick="openAddModal()"><i class="fa-solid fa-plus"></i> Add Schedule</button>
            <?php endif; ?>
        </div>

        <div class="tabs" style="padding: 0 20px; margin-top: 15px;">
            <button class="tab-btn active" id="btn-approved" onclick="showScheduleTab(event, 'manage')">
                <i class="fa-solid fa-check-double"></i> Approved
            </button>
            <?php if ($isAdmin): ?>
            <button class="tab-btn" id="btn-pending" onclick="showScheduleTab(event, 'pending')">
                <i class="fa-solid fa-hourglass-half"></i> Pending 
                <span class="badge" style="background:#ef4444; color:#fff; border-radius:10px; padding:2px 8px; font-size:0.75rem;"><?= $data['pendingCount'] ?? 0 ?></span>
            </button>
            <?php endif; ?>
        </div>

        <div id="manageTab" class="tab-content active">
    <div class="card-body">
        <?php if ($isAdmin): ?>
            <?php if (!empty($data['groupedApprovedSchedules'])): ?>
                <div class="user-schedule-accordion">
                    <?php foreach ($data['groupedApprovedSchedules'] as $uid => $userData): ?>
                        <div class="user-schedule-group">
                            <button type="button" class="user-schedule-header" onclick="toggleScheduleGroup(this)">
                                <div class="user-info-col">
                                    <div class="user-avatar-small"><?= strtoupper(substr($userData['user_info']['first_name'],0,1)) ?></div>
                                    <div style="display: flex; flex-direction: column; gap: 5px;">
                                        <span class="user-name"><?= htmlspecialchars($userData['user_info']['first_name'] . ' ' . $userData['user_info']['last_name']) ?></span>
                                        <div style="display: flex; gap: 8px;">
                                            <span class="badge-id"><i class="fa-solid fa-id-badge"></i> <?= htmlspecialchars($userData['user_info']['faculty_id']) ?></span>
                                            <span class="badge-hours"><i class="fa-solid fa-clock"></i> <?= number_format($userData['stats']['total_hours'], 1) ?>h Weekly</span>
                                        </div>
                                    </div>
                                </div>
                                <i class="fa-solid fa-chevron-down schedule-group-icon"></i>
                            </button>
                            <div class="user-schedule-body">
                                <?php renderScheduleTable($userData['daily_schedules'], true, true, false); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <?php if (!empty($data['myGroupedSchedule']['daily_schedules'])): ?>
                <div class="user-schedule-group">
                    <button type="button" class="user-schedule-header" onclick="toggleScheduleGroup(this)">
                        <div class="user-info-col">
                            <div class="user-avatar-small"><?= strtoupper(substr($_SESSION['first_name'], 0, 1)) ?></div>
                            <div style="display: flex; flex-direction: column; gap: 5px;">
                                <span class="user-name">My Approved Schedule</span>
                                <span class="badge-hours"><i class="fa-solid fa-clock"></i> <?= number_format($data['myGroupedSchedule']['stats']['total_hours'], 1) ?>h Approved</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down schedule-group-icon"></i>
                    </button>
                    <div class="user-schedule-body">
                        <?php renderScheduleTable($data['myGroupedSchedule']['daily_schedules'], false, false, false); ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div id="pendingTab" class="tab-content">
    <div class="card-body">
        <?php if ($isAdmin): ?>
            <?php if (!empty($data['groupedPendingSchedules'])): ?>
                <form method="POST" id="bulkActionForm">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="bulk_action_type" id="bulkActionInput">

                    <div style="background: #f8fafc; padding: 15px 25px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; border: 1px solid #e2e8f0; border-top: 4px solid #10b981;">
                        <div style="display: flex; align-items: center; gap: 10px; color: #475569;">
                            <i class="fa-solid fa-layer-group"></i>
                            <span style="font-size: 0.85rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Bulk Processing</span>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button type="button" class="btn btn-success btn-sm" onclick="handleBulkAction('approve')" style="background: #10b981; border: none; font-weight: 700; padding: 8px 20px;">
                                <i class="fa-solid fa-check-double"></i> Approve Selected
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="handleBulkAction('decline')" style="font-weight: 700; padding: 8px 20px;">
                                <i class="fa-solid fa-times"></i> Decline Selected
                            </button>
                        </div>
                    </div>

                    <div class="user-schedule-accordion">
                        <?php foreach ($data['groupedPendingSchedules'] as $uid => $userData): ?>
                            <div class="user-schedule-group" style="border-left: 4px solid #f59e0b; margin-bottom: 15px;">
                                <div class="user-schedule-header" onclick="toggleScheduleGroup(this)" style="background: #fffbeb;">
                                    <div class="user-info-col">
                                        <div class="user-avatar-small" style="background: #f59e0b;"><?= strtoupper(substr($userData['user_info']['first_name'],0,1)) ?></div>
                                        <div>
                                            <span class="user-name"><?= htmlspecialchars($userData['user_info']['first_name'] . ' ' . $userData['user_info']['last_name']) ?></span>
                                            <div class="user-id-text" style="font-size: 0.75rem; color: #64748b;">ID: <?= htmlspecialchars($userData['user_info']['faculty_id']) ?></div>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-chevron-down schedule-group-icon"></i>
                                </div>
                                <div class="user-schedule-body active" style="max-height: none; display: block; padding: 15px;">
                                    <?php renderScheduleTable($userData['daily_schedules'], true, false, true); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            <?php else: ?>
                <p class="text-center" style="padding: 3rem; color: #64748b;">No pending schedule requests found.</p>
            <?php endif; ?>
            <?php else: ?>
                <?php renderScheduleTable($data['myPendingSchedule'] ?? [], false, false, false); ?>
            <?php endif; ?>
    </div>
</div>

<div id="editScheduleModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header"><h3>Edit Schedule</h3><span class="close-btn" onclick="closeModal('editScheduleModal')">&times;</span></div>
        <form method="POST">
            <?php csrf_field(); ?>
            <div class="modal-body">
                <input type="hidden" name="edit_schedule" value="1">
                <input type="hidden" name="schedule_id_edit" id="editScheduleId">
                <input type="hidden" name="user_id_edit" id="editUserId">
                <div class="form-group"><label>Day</label><select name="day_of_week_edit" id="editDay" class="form-control"><option>Monday</option><option>Tuesday</option><option>Wednesday</option><option>Thursday</option><option>Friday</option><option>Saturday</option></select></div>
                <div class="form-group"><label>Subject</label><input type="text" name="subject_edit" id="editSubject" class="form-control"></div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                    <div class="form-group"><label>Start</label><input type="time" name="start_time_edit" id="editStartTime" class="form-control"></div>
                    <div class="form-group"><label>End</label><input type="time" name="end_time_edit" id="editEndTime" class="form-control"></div>
                </div>
                <div class="form-group"><label>Room</label><input type="text" name="room_edit" id="editRoom" class="form-control"></div>
                <div class="form-group"><label>Type</label><select name="type_edit" id="editType" class="form-control"><option value="Class">Class</option><option value="Office">Office</option></select></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('editScheduleModal')">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
        </form>
    </div>
</div>

<div id="addScheduleModal" class="modal">
    <div class="modal-content" style="max-width: 1200px; border-top: 6px solid #10b981;">
        <div class="modal-header" style="background: #10b981; color: #fff;">
            <h3>Create Duty Schedule</h3>
            <span class="close-btn" onclick="closeModal('addScheduleModal')" style="color:#fff;">&times;</span>
        </div>
        <form method="POST" id="addScheduleForm">
            <?php csrf_field(); ?>
            <div class="modal-body" style="background: #f1f5f9; padding: 25px;">
                <input type="hidden" name="add_schedule" value="1">
                <div id="schedule-entry-list"></div>
                <button type="button" class="btn btn-secondary" onclick="addScheduleRow()" style="width:100%; border: 2px dashed #cbd5e1; background: #fff; color: #64748b; height: 50px;">
                    <i class="fa-solid fa-plus-circle"></i> Add Another Duty Session
                </button>
            </div>
            <div class="modal-footer" style="background: #fff;"><button type="submit" class="btn btn-primary" style="background:#10b981; padding: 12px 40px;">Review & Confirm</button></div>
        </form>
    </div>
</div>

<div id="deleteScheduleModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 480px; border-top: 6px solid #ef4444; transition: all 0.3s ease;">
        <div class="modal-header" style="border-bottom: 1px solid #f1f5f9;">
            <h3 style="font-weight: 800; letter-spacing: -0.5px; color: #1e293b;">Confirm Removal</h3>
            <span class="close-btn" onclick="closeModal('deleteScheduleModal')">&times;</span>
        </div>

        <form method="POST">
            <?php csrf_field(); ?>
            <input type="hidden" name="delete_schedule" value="1">
            <input type="hidden" name="schedule_id_delete" id="deleteScheduleId">
            <input type="hidden" name="user_id_delete" id="deleteUserId"> <div class="modal-body text-center" style="padding: 2.5rem 2rem;">
                <div style="background: #fef2f2; color: #ef4444; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.5rem; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.1);">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                
                <h3 style="color: #1e293b; font-weight: 800; font-size: 1.4rem; margin-bottom: 0.8rem;">Delete this Record?</h3>
                <p style="color: #64748b; font-size: 0.95rem; line-height: 1.6;">
                    Are you sure you want to remove this duty session? This action is permanent and will be reflected immediately in the faculty member's attendance logs.
                </p>

                <div id="deleteDetailBox" style="margin-top: 20px; padding: 12px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; font-size: 0.85rem; color: #475569; display: none;">
                    </div>
            </div>

            <div class="modal-footer" style="background: #f8fafc; padding: 15px 25px; display: flex; gap: 12px; border-top: 1px solid #f1f5f9;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteScheduleModal')" style="flex: 1; font-weight: 600; height: 45px; border-radius: 10px;">
                    Keep Record
                </button>
                <button type="submit" class="btn btn-danger" style="flex: 1; background: #ef4444; border: none; font-weight: 700; height: 45px; border-radius: 10px; transition: opacity 0.2s;">
                    Yes, Delete
                </button>
            </div>
        </form>
    </div>
</div>

<div id="conflictWarningModal" class="modal-overlay" style="display:none;">
    <div class="system-modal-card">
        <div class="system-modal-header bg-danger text-white">
            <div class="header-content"><span>Schedule Conflict Detected</span></div>
            <button class="modal-close-icon" onclick="closeModal('conflictWarningModal')">&times;</button>
        </div>
        <div class="system-modal-body">
            <p class="instruction-label text-danger">The slot you are trying to assign is already occupied:</p>
            <div class="conflict-info-grid">
                <div class="info-block"><label>Member</label><div class="info-value"><span id="conflictUser"></span></div></div>
                <div class="info-block"><label>Existing Schedule</label><div class="info-value"><span id="conflictDay"></span> | <span id="conflictTime"></span></div></div>
                <div class="info-block"><label>Room</label><div class="info-value"><span id="conflictRoom"></span></div></div>
            </div>
        </div>
        <div class="system-modal-footer">
            <button class="btn-system-secondary" onclick="closeModal('conflictWarningModal')">Close</button>
        </div>
    </div>
</div>

<div id="successActionModal" class="modal">
    <div class="modal-content" style="max-width: 480px; border-top: 6px solid #10b981;">
        <div class="modal-header">
            <h3 style="font-weight: 800; letter-spacing: -0.5px;">System Notification</h3>
            <span class="close-btn" onclick="closeModal('successActionModal')">&times;</span>
        </div>

        <div class="modal-body text-center" style="padding: 2.5rem 2rem;">
            <div style="background: #ecfdf5; color: #10b981; width: 75px; height: 75px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.5rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h3 style="color: #1e293b; font-weight: 800; font-size: 1.5rem; margin-bottom: 0.5rem;">Action Successful</h3>
            <p id="successModalMessage" style="color: #64748b; font-size: 1rem; line-height: 1.5;"></p>
        </div>

        <div class="modal-footer" style="background: #f8fafc; padding: 15px 25px;">
            <button type="button" class="btn btn-primary" onclick="closeModal('successActionModal')" 
                style="width: 100%; background: #10b981; border: none; font-weight: 700; height: 48px; border-radius: 10px; font-size: 1rem; transition: all 0.2s ease;">
                Continue
            </button>
        </div>
    </div>
</div>

<div id="bulkSummaryModal" class="modal">
    <div class="modal-content" id="bulkModalCard" style="max-width: 700px; border-top: 6px solid #10b981; transition: all 0.3s ease;">
        <div class="modal-header">
            <h3 id="bulkSummaryTitle" style="font-weight: 800; letter-spacing: -0.5px;">Review Action</h3>
            <span class="close-btn" onclick="closeModal('bulkSummaryModal')">&times;</span>
        </div>
        <div class="modal-body" style="padding: 25px;">
            <div id="bulkThemeBadge" style="display: inline-block; padding: 4px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; margin-bottom: 15px;"></div>
            
            <div id="bulkSummaryList" style="max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; margin-bottom: 20px;"></div>

            <div id="declineFeedbackSection" style="display: none; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 800; color: #ef4444; text-transform: uppercase; margin-bottom: 8px;">
                    <i class="fa-solid fa-comment-dots"></i> Administrative Feedback (Optional)
                </label>
                <textarea id="bulkDeclineReason" class="form-control" rows="3" 
                    placeholder="Provide a reason for declining (e.g., 'Room Conflict', 'Incomplete Hours')..." 
                    style="width: 100%; border-radius: 10px; border: 1px solid #fca5a5; padding: 12px; font-size: 0.9rem; resize: none;"></textarea>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                <p style="margin: 0; font-size: 0.85rem; color: #64748b; line-height: 1.5;">
                    <i class="fa-solid fa-circle-info" style="margin-right: 5px;"></i>
                    <strong>Note:</strong> Confirming will update the database and send detailed logs to all affected faculty members.
                </p>
            </div>
        </div>
        <div class="modal-footer" style="background: #f8fafc; padding: 15px 25px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('bulkSummaryModal')" style="font-weight: 600;">Cancel</button>
            <button type="button" id="confirmBulkBtn" class="btn" style="font-weight: 700; min-width: 180px; transition: all 0.3s ease;">Confirm & Process</button>
        </div>
    </div>
</div>

<div id="submissionSummaryModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 600px; border-top: 6px solid #6366f1;">
        <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <h3 style="font-weight: 800; color: #1e293b;">Review Your Request</h3>
            <span class="close-btn" onclick="closeModal('submissionSummaryModal')">&times;</span>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 15px;">Please verify the following sessions before submitting for administrative approval:</p>
            <div id="summaryList" style="background: #f1f5f9; border-radius: 12px; padding: 15px; border: 1px solid #e2e8f0; max-height: 350px; overflow-y: auto;">
                </div>
        </div>
        <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; gap: 10px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('submissionSummaryModal')" style="flex: 1;">Edit Changes</button>
            <button type="button" id="finalConfirmBtn" class="btn btn-primary" style="flex: 1; background: #6366f1; border: none; font-weight: 700;">Submit for Approval</button>
        </div>
    </div>
</div>

<script>
const rawRooms = <?= json_encode($rooms ?? $data['rooms'] ?? []) ?>;

const locationData = {
    'Class': rawRooms.filter(r => /\d/.test(r.name) || /Lab/i.test(r.name)),
    'Office': rawRooms.filter(r => !/\d/.test(r.name) && !/Lab/i.test(r.name))
};

function openEditModal(id, uid, day, subject, start, end, room, type) {
    console.log("Opening Edit Modal for ID:", id);
    const modal = document.getElementById('editScheduleModal');
    if (!modal) return;

    document.getElementById('editScheduleId').value = id;
    document.getElementById('editUserId').value = uid;
    document.getElementById('editDay').value = day;
    document.getElementById('editSubject').value = subject;
    document.getElementById('editStartTime').value = start;
    document.getElementById('editEndTime').value = end;
    document.getElementById('editRoom').value = room;
    document.getElementById('editType').value = type || 'Class';

    openModal('editScheduleModal');
}

function showScheduleTab(e, tab) {
    console.log("Switching to: " + tab);
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    
    document.getElementById(tab + 'Tab').classList.add('active');
    e.currentTarget.classList.add('active');
}

function toggleScheduleGroup(btn) {
    const body = btn.nextElementSibling;
    const icon = btn.querySelector('.schedule-group-icon');
    if (!body) return;

    if (body.style.maxHeight && body.style.maxHeight !== '0px') {
        body.style.maxHeight = '0px';
        if (icon) icon.style.transform = 'rotate(0deg)';
    } else {
        body.style.maxHeight = body.scrollHeight + "px";
        if (icon) icon.style.transform = 'rotate(180deg)';
    }
}

function openModal(id) { 
    const m = document.getElementById(id);
    if(m) { m.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
}
function closeModal(id) { 
    const m = document.getElementById(id);
    if(m) { m.style.display = 'none'; document.body.style.overflow = 'auto'; }
}

function openDeleteModal(schedId, userId) {
    document.getElementById('deleteScheduleId').value = schedId;
    if (document.getElementById('deleteUserId')) {
        document.getElementById('deleteUserId').value = userId;
    }

    const btn = event.currentTarget;
    const row = btn.closest('tr');
    const detailBox = document.getElementById('deleteDetailBox');
    
    if (row && detailBox) {
        const day = row.cells[0].textContent.trim();
        const subject = row.cells[1].textContent.trim();
        detailBox.style.display = 'block';
        detailBox.innerHTML = `<i class="fa-solid fa-calendar-day" style="margin-right:5px; opacity:0.5;"></i> ${day} — <strong>${subject}</strong>`;
    }

    openModal('deleteScheduleModal');
}

const roomList = <?= json_encode($data['rooms'] ?? []) ?>;

function addScheduleRow() {
    const list = document.getElementById('schedule-entry-list');
    const div = document.createElement('div');
    div.className = 'schedule-entry-row';

    div.innerHTML = `
        <div class="form-group">
            <label>Day</label>
            <select name="day_of_week[]" class="form-control" required>
                <option>Monday</option><option>Tuesday</option><option>Wednesday</option>
                <option>Thursday</option><option>Friday</option><option>Saturday</option>
            </select>
        </div>
        <div class="form-group">
            <label>Type</label>
            <select name="type[]" class="form-control" onchange="updateRoomOptions(this)" required>
                <option value="">Select...</option>
                <option value="Class">Class</option>
                <option value="Office">Office</option>
            </select>
        </div>
        <div class="form-group">
            <label>Subject / Duty</label>
            <input type="text" name="subject[]" class="form-control" placeholder="e.g. IT 101" required>
        </div>
        <div class="form-group">
            <label>Start</label>
            <input type="time" name="start_time[]" class="form-control" required>
        </div>
        <div class="form-group">
            <label>End</label>
            <input type="time" name="end_time[]" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Room</label>
            <select name="room[]" class="form-control" required>
                <option value="">Select Type First...</option>
            </select>
        </div>
        <button type="button" class="btn-remove-row" onclick="this.parentElement.remove()" title="Remove">
            <i class="fa-solid fa-times"></i>
        </button>
    `;
    list.appendChild(div);
}

function toggleUserGroup(master, uid) {
    document.querySelectorAll('.check-user-' + uid).forEach(cb => cb.checked = master.checked);
}

function updateRoomOptions(typeSelect) {
    const type = typeSelect.value;
    const row = typeSelect.closest('.schedule-entry-row');
    const roomSelect = row.querySelector('select[name="room[]"]');
    const filteredRooms = locationData[type] || [];
    
    roomSelect.innerHTML = '<option value="">Select Location...</option>';
    filteredRooms.forEach(room => {
        const opt = document.createElement('option');
        opt.value = room.name; opt.textContent = room.name;
        roomSelect.appendChild(opt);
    });
}

function showScheduleTab(e, tab) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(tab + 'Tab').classList.add('active');
    if(e) e.currentTarget.classList.add('active');
}

function openAddModal() {
    const list = document.getElementById('schedule-entry-list');
    if(list) {
        list.innerHTML = '';
        addScheduleRow();
        openModal('addScheduleModal');
    }
}

function handleBulkAction(type) {
    const checkedBoxes = document.querySelectorAll('input[name="selected_schedules[]"]:checked');
    if (checkedBoxes.length === 0) {
        document.getElementById('modalTitle').textContent = "No Selection Detected";
        document.getElementById('modalMessage').innerHTML = `
            <div style="text-align: center; padding: 10px;">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 3rem; color: #f59e0b; margin-bottom: 15px;"></i>
                <p style="font-weight: 600; color: #1e293b;">Please select at least one schedule session to proceed with the bulk action.</p>
            </div>`;
        
        const confirmBtn = document.getElementById('confirmActionBtn');
        confirmBtn.textContent = "Understood";
        confirmBtn.style.background = "#64748b";
        confirmBtn.onclick = function() { closeModal('genericConfirmModal'); };
        
        openModal('genericConfirmModal');
        return;
    }

    const isApprove = (type === 'approve');
    const themeColor = isApprove ? "#10b981" : "#ef4444";
    const themeBg = isApprove ? "#ecfdf5" : "#fef2f2";

    const card = document.getElementById('bulkModalCard');
    const badge = document.getElementById('bulkThemeBadge');
    const confirmBtn = document.getElementById('confirmBulkBtn');
    
    card.style.borderTopColor = themeColor;
    badge.style.background = themeBg;
    badge.style.color = themeColor;
    badge.textContent = `${type}ing ${checkedBoxes.length} sessions`;
    
    confirmBtn.style.background = themeColor;
    confirmBtn.textContent = isApprove ? "Confirm Approval" : "Confirm Decline";
    document.getElementById('bulkSummaryTitle').textContent = isApprove ? "Schedule Summary Review" : "Schedule Summary Review";

    const groups = {};
    checkedBoxes.forEach(cb => {
        const row = cb.closest('tr');
        const groupContainer = cb.closest('.user-schedule-group');
        const name = groupContainer.querySelector('.user-name').textContent.trim();

        const facultyIdElement = groupContainer.querySelector('.user-id-text');
        const facultyId = facultyIdElement ? facultyIdElement.textContent.trim() : "ID: Unknown";
        
        const day = row.cells[1].textContent.trim();
        const subject = row.cells[2].textContent.trim();
        const timeStr = row.cells[4].textContent.trim();

        const [start, end] = timeStr.split(' - ');
        const hours = (new Date(`2000/01/01 ${end}`) - new Date(`2000/01/01 ${start}`)) / (1000 * 60 * 60);

        if (!groups[name]) {
            groups[name] = { id: facultyId, sessions: [], totalHours: 0 };
        }
        groups[name].sessions.push({ day, subject, timeStr });
        groups[name].totalHours += hours;
    });

    let html = '';
    for (const [name, data] of Object.entries(groups)) {
        html += `
            <div style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                <div style="background: #f1f5f9; padding: 12px 15px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0;">
                    <div>
                        <div style="font-weight: 800; color: #1e293b; font-size: 0.95rem;">${name}</div>
                        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">${data.id}</div>
                    </div>
                    <div style="text-align: right;">
                        <span style="display: block; font-size: 0.85rem; font-weight: 800; color: ${themeColor};">${data.totalHours.toFixed(1)} hrs</span>
                        <span style="font-size: 0.7rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">${data.sessions.length} sessions</span>
                    </div>
                </div>
                <div style="padding: 5px 15px; background: #fff;">
                    ${data.sessions.map(s => `
                        <div style="padding: 8px 0; border-bottom: 1px solid #f8fafc; font-size: 0.85rem; color: #475569; display: flex; gap: 10px;">
                            <span style="font-weight: 700; color: #1e293b; min-width: 80px;">${s.day}</span>
                            <span>${s.subject} <small style="color: #94a3b8; margin-left: 5px;">(${s.timeStr})</small></span>
                        </div>
                    `).join('')}
                </div>
            </div>`;
    }

    document.getElementById('bulkSummaryList').innerHTML = html;

    confirmBtn.onclick = function() {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
        document.getElementById('bulkActionInput').value = type;
        document.getElementById('bulkActionForm').submit();
    };

    openModal('bulkSummaryModal');
}

function toggleUserGroup(masterCheckbox, uid) {
    const checkboxes = document.querySelectorAll('.check-user-' + uid);
    checkboxes.forEach(cb => {
        cb.checked = masterCheckbox.checked;
    });
}

const addScheduleForm = document.getElementById('addScheduleForm');
if (addScheduleForm) {
    addScheduleForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const rows = Array.from(this.querySelectorAll('.schedule-entry-row'));
        let summaryHtml = '';

        rows.forEach(row => {
            const day = row.querySelector('[name="day_of_week[]"]').value;
            const subject = row.querySelector('[name="subject[]"]').value;
            const type = row.querySelector('[name="type[]"]').value;
            const start = row.querySelector('[name="start_time[]"]').value;
            const end = row.querySelector('[name="end_time[]"]').value;
            const room = row.querySelector('[name="room[]"]').value;

            summaryHtml += `
                <div style="border-bottom: 1px solid #cbd5e1; padding: 12px 0; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="color:#059669; font-size: 0.8rem; text-transform: uppercase;">${day}</strong>
                        <div style="font-weight: 700; color: #1e293b;">${subject} <small>(${type})</small></div>
                        <div style="font-size: 0.85rem; color: #64748b;">${convertTime(start)} - ${convertTime(end)}</div>
                    </div>
                    <div style="text-align: right; font-size: 0.8rem; font-weight: 600; color: #6366f1;">${room}</div>
                </div>`;
        });

        document.getElementById('summaryList').innerHTML = summaryHtml;
        openModal('submissionSummaryModal');

        document.getElementById('finalConfirmBtn').onclick = () => {
            document.getElementById('finalConfirmBtn').disabled = true;
            document.getElementById('finalConfirmBtn').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting...';
            
            const schedules = rows.map(row => ({
                day: row.querySelector('[name="day_of_week[]"]').value,
                start: row.querySelector('[name="start_time[]"]').value,
                end: row.querySelector('[name="end_time[]"]').value,
                room: row.querySelector('[name="room[]"]').value
            }));

            const fd = new FormData();
            fd.append('check_conflict', '1');
            schedules.forEach((s, i) => {
                fd.append(`schedules[${i}][day]`, s.day);
                fd.append(`schedules[${i}][start]`, s.start);
                fd.append(`schedules[${i}][end]`, s.end);
                fd.append(`schedules[${i}][room]`, s.room);
            });

            fetch('schedulecontroller/index', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.has_conflict) {
                        closeModal('submissionSummaryModal');
                        const c = data.conflict_details;
                        document.getElementById('conflictUser').textContent = c.first_name + ' ' + c.last_name;
                        document.getElementById('conflictDay').textContent = c.day_of_week;
                        document.getElementById('conflictTime').textContent = c.start_time + ' - ' + c.end_time;
                        document.getElementById('conflictRoom').textContent = c.room;
                        openModal('conflictWarningModal');
                        document.getElementById('finalConfirmBtn').disabled = false;
                        document.getElementById('finalConfirmBtn').innerText = 'Submit for Approval';
                    } else {
                        addScheduleForm.submit();
                    }
                })
                .catch(() => addScheduleForm.submit());
        };
    });
}

window.addEventListener('DOMContentLoaded', () => {
    <?php if (isset($_SESSION['success_modal'])): ?>
        const successModal = document.getElementById('successActionModal');
        if (successModal) {
            const messageElement = document.getElementById('successModalMessage');
            if (messageElement) {
                messageElement.textContent = "<?= $_SESSION['success_modal'] ?>";
            }
            openModal('successActionModal');
        }
        <?php unset($_SESSION['success_modal']); ?>
    <?php endif; ?>
});

function convertTime(time) {
    if (!time) return 'N/A';
    const [hours, minutes] = time.split(':');
    const h = hours % 12 || 12;
    const ampm = hours >= 12 ? 'PM' : 'AM';
    return `${h}:${minutes} ${ampm}`;
}

function confirmApproval(formElement, userName) {
    const title = "Confirm Approval";
    const message = `You are about to approve the entire schedule set for <strong>${userName}</strong>. This will notify them via email and dashboard.`;

    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').innerHTML = message;
    
    const confirmBtn = document.getElementById('confirmActionBtn');
    confirmBtn.className = "btn btn-success";
    confirmBtn.textContent = "Confirm Approval";
    
    confirmBtn.onclick = function() {
        formElement.submit();
    };
    
    openModal('genericConfirmModal');
}

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('select-all-pending-rows')) {
        const table = e.target.closest('table');
        const rowCheckboxes = table.querySelectorAll('.pending-row-checkbox');
        rowCheckboxes.forEach(cb => cb.checked = e.target.checked);
    }
});

window.onload = function() { console.log("Schedule Script Active"); };
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>