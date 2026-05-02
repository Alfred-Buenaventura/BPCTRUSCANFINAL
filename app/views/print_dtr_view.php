<?php
// set up the initial user details here
$middleInitial = !empty($user['middle_name']) ? ' ' . strtoupper(substr($user['middle_name'], 0, 1)) . '.' : '';
$fullName = strtoupper($user['last_name'] . ', ' . $user['first_name'] . $middleInitial);
$facultyId = $user['faculty_id'];
$isAdmin = (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin');

// month and year helpers for the report
$mName = $monthName ?? 'Month';
$yVal = $year ?? date('Y');
$mVal = date('m', strtotime("$mName 1, $yVal"));

// schedule engine to handle time windows for each day
$schedWindows = [
    'Monday'=>[], 'Tuesday'=>[], 'Wednesday'=>[], 'Thursday'=>[], 'Friday'=>[], 'Saturday'=>[], 'Sunday'=>[]
];

if (isset($schedules) && is_array($schedules)) {
    foreach ($schedules as $s) {
        $rawStart = $s['start_time'] ?? $s['time_start'] ?? '00:00';
        $rawEnd   = $s['end_time'] ?? $s['time_end'] ?? '00:00';
        $start = strtotime(date('H:i:00', strtotime($rawStart)));
        $end = strtotime(date('H:i:00', strtotime($rawEnd)));
        $dayKey = ucfirst(strtolower($s['day_of_week'] ?? $s['day'] ?? ''));
        if ($start && $end && isset($schedWindows[$dayKey])) {
            $schedWindows[$dayKey][] = ['start' => $start, 'end' => $end];
        }
    }
}

function calculateIntersection($logIn, $logOut, $windows) {
    if (!$logIn || !$logOut || $logOut <= $logIn) return 0;
    $cleanIn = strtotime(date('H:i:00', $logIn));
    $cleanOut = strtotime(date('H:i:00', $logOut));
    $totalMins = 0;
    foreach ($windows as $win) {
        $overlapStart = max($cleanIn, $win['start']);
        $overlapEnd = min($cleanOut, $win['end']);
        if ($overlapEnd > $overlapStart) {
            $totalMins += ($overlapEnd - $overlapStart) / 60;
        }
    }
    return $totalMins;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DTR - <?= htmlspecialchars($fullName) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/print.css">
    <link rel="stylesheet" href="css/responsive.css?v=<?= time(); ?>">
    <style>
        /* sidebar control styling */
        .print-controls {
            position: fixed; top: 20px; right: 20px;
            display: flex; flex-direction: column; gap: 10px;
            z-index: 10000; background: white; padding: 15px;
            border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border: 1px solid #e2e8f0; width: 190px;
        }
        #viewIndicator {
            font-size: 11px; font-weight: 800; color: #065f46;
            background: #d1fae5; padding: 6px; border-radius: 4px;
            text-align: center; text-transform: uppercase; margin-bottom: 5px;
        }

        /* making sure the table data is centered */
        .attendance-table td {
            white-space: nowrap; padding: 0 !important; 
            position: relative; height: 25px; border: 1px solid black;
            text-align: center; vertical-align: middle;
        }
        .data-wrapper { 
            width: 100%; height: 100%; 
            display: flex; align-items: center; justify-content: center; 
        }
        .is-hidden { visibility: hidden !important; }

        .dtr-input {
            width: 100% !important; height: 100% !important; border: none !important;
            background: transparent; text-align: center; font-family: inherit;
            font-size: 10.5px; outline: none;
        }
        body.edit-mode .dtr-input { cursor: text; }
        body.edit-mode .dtr-input:focus { background: #fffbeb !important; }
        .row-changed { background-color: #fef3c7 !important; }

        .holiday-text {
            font-size: 8px; font-weight: bold; text-transform: uppercase;
            color: #d32f2f; background-color: #fff1f1; text-align: center;
        }

        /* print settings for standard 8.5 x 11 paper */
        @media print {
            @page { size: letter portrait; margin: 0.2in; }
            body { background: white !important; margin: 0 !important; padding: 0 !important; }
            .print-controls { display: none !important; }
            .dtr-container-wrapper { 
                display: flex !important; flex-direction: row !important; 
                justify-content: center !important; gap: 0.15in !important; 
                padding: 0 !important; width: 100% !important; 
            }
            .dtr-container { 
                width: 3.9in !important; 
                height: 9.8in !important; 
                border: 1px solid black !important; padding: 8px !important; 
                box-shadow: none !important; display: flex !important; flex-direction: column !important;
            }
            .attendance-table td { height: 18px !important; font-size: 8pt !important; } 
            .dtr-footer-section { margin-top: 15px !important; padding-top: 5px !important; }
            .is-hidden { visibility: hidden !important; }
        }
    </style>
</head>

<body class="dtr-body">
    <div class="print-controls">
        <div id="viewIndicator">Viewing: Split DTR</div>
        <button type="button" class="btn-dtr" onclick="applyDTRFilter('both')">Default View</button>
        <button type="button" class="btn-dtr" onclick="applyDTRFilter('p1')">Days 1-15 (Both)</button>
        <button type="button" class="btn-dtr" onclick="applyDTRFilter('p2')">Days 16-31 (Both)</button>
        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 5px 0;">
        <?php if ($isAdmin): ?>
            <button type="button" id="editToggleBtn" class="btn-dtr" onclick="toggleEditMode()" style="background: #6366f1; color: white;">Enable Edit</button>
            <button type="button" id="saveDTRBtn" class="btn-dtr" onclick="saveAllChanges()" style="background: #10b981; color: white; display: none;">Save Changes</button>
        <?php endif; ?>
        <button type="button" class="btn-dtr btn-dtr-success" onclick="window.print();">Print (8.5x11)</button>
    </div>

    <div class="dtr-container-wrapper">
        <?php for ($copy = 0; $copy < 2; $copy++): 
            $initialLabel = ($copy === 0) ? "$mName 1-15, $yVal" : "$mName 16-$lastDay, $yVal";
        ?>
        <div class="dtr-container" id="dtr-box-<?= $copy ?>">
            <div class="dtr-header" style="text-align: center;">
                <h3 style="margin:0; font-size: 8pt;">CS Form 48</h3>
                <h2 style="margin:2px 0; font-size: 11pt;">DAILY TIME RECORD</h2>
            </div>

            <table class="info-table" style="width: 100%; border-collapse: collapse; margin-top: 5px;">
                <tr><td class="value" style="border-bottom: 1px solid black; font-weight: bold; text-align: center; text-transform: uppercase;"><?= htmlspecialchars($fullName) ?></td></tr>
                <tr><td style="text-align: center; font-size: 6pt;">(Name)</td></tr>
                <tr><td class="faculty-id-row" style="text-align: center; font-size: 7.5pt; font-weight: 700;">FACULTY ID: <?= htmlspecialchars($facultyId) ?></td></tr>
                <tr><td style="text-align: center; padding-top: 2px; font-size: 8pt;">For the month of <strong class="cutoff-label"><?= htmlspecialchars($initialLabel) ?></strong></td></tr>
            </table>

            <table class="attendance-table" style="width: 100%; border-collapse: collapse; margin-top: 8px;">
                <thead>
                    <tr><th rowspan="2" style="width: 12%;">Day</th><th colspan="2">A.M.</th><th colspan="2">P.M.</th><th colspan="2">Total</th></tr>
                    <tr><th>Arr</th><th>Dep</th><th>Arr</th><th>Dep</th><th>Hrs</th><th>Min</th></tr>
                </thead>
                <tbody>
                    <?php
                        $boxMinsTotal = 0;
                        for ($day = 1; $day <= 31; $day++):
                            $rec = $dtrRecords[$day] ?? null;
                            $pTag = ($day <= 15) ? 'data-p1' : 'data-p2';

                            $isHiddenByDefault = ($copy === 0 && $day > 15) || ($copy === 1 && $day <= 15);
                            $hideClass = $isHiddenByDefault ? "is-hidden" : "";

                            if ($day > $lastDay) {
                                echo "<tr><td style='border: 1px solid black; font-weight: bold;'>$day</td><td colspan='6' style='background: #f9fafb;'></td></tr>";
                                continue;
                            }

                            $timestamp = strtotime("$yVal-$mVal-" . str_pad($day, 2, '0', STR_PAD_LEFT));
                            $dayOfWeek = date('l', $timestamp);
                            $isHoliday = ($rec && !empty($rec['remarks']));

                            // logic to make sure arrival and departure times show up right
                            $ai_raw = ($rec && !empty($rec['am_in']) && $rec['am_in'] != '00:00:00') ? $rec['am_in'] : null;
                            $ao_raw = ($rec && !empty($rec['am_out']) && $rec['am_out'] != '00:00:00') ? $rec['am_out'] : null;
                            $pi_raw = ($rec && !empty($rec['pm_in']) && $rec['pm_in'] != '00:00:00') ? $rec['pm_in'] : null;
                            $po_raw = ($rec && !empty($rec['pm_out']) && $rec['pm_out'] != '00:00:00') ? $rec['pm_out'] : null;

                            $ai = ($ai_raw) ? date('g:i', strtotime($ai_raw)) : '';
                            // skip showing am departure if it matches the arrival time
                            $ao = ($ao_raw && $ao_raw !== $ai_raw) ? date('g:i', strtotime($ao_raw)) : '';
                            $pi = ($pi_raw) ? date('g:i', strtotime($pi_raw)) : '';
                            // only showing pm departure if it's different from arrival to fix that 10:05 bug
                            $po = ($po_raw && $po_raw !== $pi_raw) ? date('g:i', strtotime($po_raw)) : '';

                            // calculating the intersection of logs and schedule windows
                            $dayMins = 0;
                            if (!$isHoliday && ($ai || $ao || $pi || $po)) {
                                $rawIn  = $ai_raw ?? $pi_raw;
                                $rawOut = $po_raw ?? $ao_raw;
                                $dayMins = calculateIntersection(strtotime($rawIn), strtotime($rawOut), $schedWindows[$dayOfWeek]);
                            }

                            if (!$isHiddenByDefault) { $boxMinsTotal += $dayMins; }
                        ?>
                        <tr class="day-row" data-dayname="<?= $dayOfWeek ?>" data-mins="<?= $dayMins ?>">
                            <td style="font-weight: bold; border: 1px solid black;"><?= $day ?></td>
                            <?php if ($isHoliday): ?>
                                <td colspan="4" class="holiday-text" style="border: 1px solid black;">
                                    <div class="data-wrapper"><?= htmlspecialchars($rec['remarks']) ?></div>
                                </td>
                            <?php else: ?>
                                <td><div class="data-wrapper <?= $pTag ?> <?= $hideClass ?>"><input type="text" class="dtr-input" value="<?= $ai ?>" oninput="markChanged(this, <?= $day ?>)" data-field="am_in" placeholder="--:--" readonly></div></td>
                                <td><div class="data-wrapper <?= $pTag ?> <?= $hideClass ?>"><input type="text" class="dtr-input" value="<?= $ao ?>" oninput="markChanged(this, <?= $day ?>)" data-field="am_out" placeholder="--:--" readonly></div></td>
                                <td><div class="data-wrapper <?= $pTag ?> <?= $hideClass ?>"><input type="text" class="dtr-input" value="<?= $pi ?>" oninput="markChanged(this, <?= $day ?>)" data-field="pm_in" placeholder="--:--" readonly></div></td>
                                <td><div class="data-wrapper <?= $pTag ?> <?= $hideClass ?>"><input type="text" class="dtr-input" value="<?= $po ?>" oninput="markChanged(this, <?= $day ?>)" data-field="pm_out" placeholder="--:--" readonly></div></td>
                            <?php endif; ?>
                            <td class="day-hrs" style="border: 1px solid black;"><div class="data-wrapper <?= $pTag ?> <?= $hideClass ?>"><?= ($dayMins > 0) ? floor($dayMins/60) : '' ?></div></td>
                            <td class="day-min" style="border: 1px solid black;"><div class="data-wrapper <?= $pTag ?> <?= $hideClass ?>"><?= ($dayMins > 0) ? round($dayMins%60) : '' ?></div></td>
                        </tr>
                        <?php endfor; ?>

                    <tr class="total-row" style="font-weight: bold;">
                        <td colspan="5" style="text-align: right; border: 1px solid black; font-size: 8pt; padding-right: 5px;">Total</td>
                        <td class="grand-hrs" style="text-align: center; border: 1px solid black; font-size: 8pt;"><?= floor($boxMinsTotal / 60) ?></td>
                        <td class="grand-min" style="text-align: center; border: 1px solid black; font-size: 8pt;"><?= round($boxMinsTotal % 60) ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="dtr-footer-section" style="font-size: 7.2pt; font-family: 'Times New Roman', serif;">
                <p style="text-align: justify; line-height: 1.1; margin: 10px 0 10px 0;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I certify on my honor that the above is a true and correct report of the hours of work performed, record of which was made daily at the time of arrival and departure from office.</p>
                
                <div style="margin-top: 15px; border-bottom: 1px solid black; width: 85%; margin-left: auto; margin-right: auto; text-align: center; font-weight: bold; text-transform: uppercase;">
                    <?= htmlspecialchars($fullName) ?>
                </div>
                <div style="text-align: center; font-size: 6pt; margin-bottom: 10px;">(Signature of Employee)</div>

                <div style="text-align: center; width: 80%; margin: 0 auto;">
                    <div style="font-weight: bold; text-decoration: underline; text-transform: uppercase;"><?= htmlspecialchars($settings['dtr_in_charge_name'] ?? '____________________') ?></div>
                    <div style="font-size: 7pt;">In Charge</div>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <script>
        const schedWindowsJS = <?= json_encode($schedWindows) ?>;
        let changedDays = new Set();
        let isEditMode = false;

        // toggle for editing mode
        function toggleEditMode() {
            isEditMode = !isEditMode;
            document.body.classList.toggle('edit-mode', isEditMode);
            const btn = document.getElementById('editToggleBtn');
            const saveBtn = document.getElementById('saveDTRBtn');
            const inputs = document.querySelectorAll('.dtr-input');
            btn.innerHTML = isEditMode ? '<i class="fa-solid fa-xmark"></i> Cancel Edit' : 'Enable Edit';
            btn.style.background = isEditMode ? '#ef4444' : '#6366f1';
            inputs.forEach(i => i.readOnly = !isEditMode);
            if (saveBtn) saveBtn.style.display = (isEditMode && changedDays.size > 0) ? 'block' : 'none';
        }

        // switching between dtr views
        function applyDTRFilter(mode) {
            document.querySelectorAll('.data-wrapper[class*="data-p"]').forEach(w => w.classList.add('is-hidden'));
            
            if (mode === 'p1') {
                document.querySelectorAll('.data-p1').forEach(w => w.classList.remove('is-hidden'));
            } else if (mode === 'p2') {
                document.querySelectorAll('.data-p2').forEach(w => w.classList.remove('is-hidden'));
            } else {
                const boxes = [document.getElementById('dtr-box-0'), document.getElementById('dtr-box-1')];
                boxes[0].querySelectorAll('.data-p1').forEach(w => w.classList.remove('is-hidden'));
                boxes[1].querySelectorAll('.data-p2').forEach(w => w.classList.remove('is-hidden'));
            }
            recalculateTotals();
        }

        // flags a row as changed and updates the hours
        function markChanged(input, day) {
            const row = input.closest('tr');
            const dayName = row.getAttribute('data-dayname');
            const windows = schedWindowsJS[dayName] || [];

            const toTs = (v, field) => {
                if (!v || !v.includes(':')) return null;
                let [h, m] = v.split(':').map(Number);
                if (field.startsWith('pm') && h < 12) h += 12;
                const d = new Date(); d.setHours(h, m, 0, 0);
                return d.getTime() / 1000;
            };

            const valAi = toTs(row.querySelector('[data-field="am_in"]').value, 'am_in');
            const valAo = toTs(row.querySelector('[data-field="am_out"]').value, 'am_out');
            const valPi = toTs(row.querySelector('[data-field="pm_in"]').value, 'pm_in');
            const valPo = toTs(row.querySelector('[data-field="pm_out"]').value, 'pm_out');

            let logIn = valAi || valPi, logOut = valPo || valAo;
            let dayMins = 0;

            if (logIn && logOut && logOut > logIn) {
                windows.forEach(win => {
                    let start = Math.max(logIn, win.start), end = Math.min(logOut, win.end);
                    if (end > start) dayMins += (end - start) / 60;
                });
            }

            row.setAttribute('data-mins', dayMins);
            const hrsWrap = row.querySelector('.day-hrs div');
            const minWrap = row.querySelector('.day-min div');
            if (hrsWrap) hrsWrap.innerText = dayMins > 0 ? Math.floor(dayMins/60) : '';
            if (minWrap) minWrap.innerText = dayMins > 0 ? Math.round(dayMins%60) : '';

            row.classList.add('row-changed');
            changedDays.add(day);
            if (isEditMode) document.getElementById('saveDTRBtn').style.display = 'flex';
            recalculateTotals();
        }

        // update the grand totals at the bottom
        function recalculateTotals() {
            for (let i = 0; i < 2; i++) {
                const box = document.getElementById(`dtr-box-${i}`);
                let total = 0;
                box.querySelectorAll('.day-row').forEach(row => {
                    const wrap = row.querySelector('.data-wrapper:not(.holiday-text div)');
                    if (wrap && !wrap.classList.contains('is-hidden')) {
                        total += parseFloat(row.getAttribute('data-mins')) || 0;
                    }
                });
                box.querySelector('.grand-hrs').innerText = Math.floor(total/60);
                box.querySelector('.grand-min').innerText = Math.round(total%60);
            }
        }

        // sending batch updates to the server
        async function saveAllChanges() {
            const payload = { faculty_id: '<?= $facultyId ?>', month: '<?= $mVal ?>', year: '<?= $yVal ?>', updates: [] };
            document.querySelectorAll('.row-changed').forEach(row => {
                const day = row.cells[0].innerText.trim();
                payload.updates.push({
                    day: day,
                    am_in: row.querySelector('[data-field="am_in"]')?.value || '',
                    am_out: row.querySelector('[data-field="am_out"]')?.value || '',
                    pm_in: row.querySelector('[data-field="pm_in"]')?.value || '',
                    pm_out: row.querySelector('[data-field="pm_out"]')?.value || ''
                });
            });
            const res = await fetch('api.php?action=update_dtr_batch', { method: 'POST', body: JSON.stringify(payload) });
            if ((await res.json()).success) location.reload();
        }
    </script>
</body>
</html>