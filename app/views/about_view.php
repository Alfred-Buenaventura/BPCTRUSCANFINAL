<?php
$pageTitle = 'About Us';
$pageSubtitle = 'Learn more about the Biometric Attendance Monitoring System | BPC TruScan';
require_once __DIR__ . '/partials/header.php'; 
?>

<div class="main-body">
    
    <div class="info-card-header" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; align-items: center; gap: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div style="background: rgba(255,255,255,0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="fa-solid fa-circle-info"></i>
        </div>
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">System Information</h2>
            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem;">
                Learn more about the technology and team behind the BPC TruScan Attendance System.
            </p>
        </div>
    </div>

    <div class="info-guide-wrapper" style="margin-bottom: 2.5rem; padding: 0 5px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="background: #f1f5f9; color: #64748b; width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fa-solid fa-lightbulb"></i>
            </div>
            <div style="flex: 1;">
                <p style="margin: 0; font-size: 0.92rem; color: #475569; line-height: 1.6;">
                    <span style="font-weight: 700; color: #1e293b; margin-right: 5px;">Did you know?</span> 
                    BPC TruScan uses secure biometric encryption to ensure that attendance data is 
                    <span style="color: #6366f1; font-weight: 600;">tamper-proof</span> and 
                    accurately reflects official working hours for the institution.
                </p>
            </div>
        </div>
    </div>

    <div class="card" style="border-top: 6px solid #10b981; margin-bottom: 2rem;">
        <div class="card-body" style="text-align: center; padding: 50px 30px;">
            <div style="width: 100px; height: 100px; background: #ecfdf5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; border: 2px dashed #10b981;">
                <i class="fa-solid fa-fingerprint" style="font-size: 48px; color: #10b981;"></i>
            </div>
            <h2 style="font-size: 28px; font-weight: 800; color: #1e293b; margin-bottom: 8px; letter-spacing: -0.025em;">BPC TruScan</h2>
            <p style="font-size: 16px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 24px;">Biometric Attendance Management</p>
            <p style="color: #475569; max-width: 650px; margin: 0 auto; line-height: 1.7; font-size: 1rem;">
                A comprehensive attendance tracking system specifically tailored for Bulacan Polytechnic College. 
                Our platform automates the recording of faculty and staff logs, simplifying the generation of 
                staff <span style="color: #6366f1; font-weight: 600;">Attendance Records</span> while maintaining high security through biometric authentication.
            </p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 24px; margin-bottom: 2rem;">
        
        <div class="card" style="border-top: 6px solid #6366f1; background: #ffffff;">
            <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #f1f5f9; padding: 20px 25px;">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #1e293b !important;">
                    <i class="fa-solid fa-star" style="color: #6366f1; margin-right: 8px;"></i> Key Features
                </h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div style="display: flex; gap: 15px; align-items: flex-start;">
                        <div style="width: 42px; height: 42px; background: #eff6ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa-solid fa-fingerprint" style="font-size: 1.1rem; color: #2563eb;"></i>
                        </div>
                        <div>
                            <h4 style="font-weight: 700; color: #1e293b !important; margin-bottom: 3px; font-size: 0.95rem;">Biometric Security</h4>
                            <p style="font-size: 0.85rem; color: #64748b !important; line-height: 1.5;">Enterprise-grade fingerprint encryption for 100% verifiable logs.</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 15px; align-items: flex-start;">
                        <div style="width: 42px; height: 42px; background: #ecfdf5; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa-solid fa-file-invoice" style="font-size: 1.1rem; color: #059669;"></i>
                        </div>
                        <div>
                            <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 3px; font-size: 0.95rem;">Automated CS Form 48</h4>
                            <p style="font-size: 0.85rem; color: #64748b; line-height: 1.5;">One-click DTR generation following Civil Service standards.</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 15px; align-items: flex-start;">
                        <div style="width: 42px; height: 42px; background: #fffbeb; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa-solid fa-calendar-check" style="font-size: 1.1rem; color: #d97706;"></i>
                        </div>
                        <div>
                            <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 3px; font-size: 0.95rem;">Smart Scheduling</h4>
                            <p style="font-size: 0.85rem; color: #64748b; line-height: 1.5;">Dynamic duty tracking for both Class Sessions and Office Units.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="border-top: 6px solid #f59e0b;">
            <div class="card-header" style="background: white; border-bottom: 1px solid #f1f5f9; padding: 20px 25px;">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #1e293b;">
                    <i class="fa-solid fa-server" style="color: #f59e0b; margin-right: 8px;"></i> System Specs
                </h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <div style="display: flex; flex-direction: column; gap: 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #e2e8f0; padding-bottom: 10px;">
                        <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Build Version</span>
                        <span style="font-family: monospace; background: #f1f5f9; padding: 3px 8px; border-radius: 4px; font-weight: 700; color: #1e293b;">v1.2.4-PRO</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #e2e8f0; padding-bottom: 10px;">
                        <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Latest Update</span>
                        <span style="font-size: 0.9rem; font-weight: 700; color: #1e293b;">January 2026</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #e2e8f0; padding-bottom: 10px;">
                        <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Core Engine</span>
                        <span style="font-size: 0.9rem; font-weight: 700; color: #1e293b;">PHP 8.2 / MySQLi</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Deployment</span>
                        <span style="font-size: 0.9rem; font-weight: 700; color: #1e293b;">On-Premise Server</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card" style="border-top: 6px solid #10b981; margin-bottom: 2rem; background: #ffffff;">
        <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #f1f5f9; padding: 20px 25px; text-align: center;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #1e293b !important;">
                <i class="fa-solid fa-code" style="color: #10b981; margin-right: 8px;"></i> Project Development Team
            </h3>
        </div>
        <div class="card-body" style="padding: 40px 30px;">
            <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 15px;">
                <?php 
                $team = ["Alfred Prime Buenaventura", "Justine Martin", "Gilbert Samudio", "Charlymark Mendoza", "James Adrian Manicad"];
                foreach ($team as $member): ?>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 50px; padding: 10px 25px; display: flex; align-items: center; gap: 10px;">
                        <div style="width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></div>
                        <span style="font-weight: 700; color: #1e293b !important; font-size: 0.9rem;"><?= $member ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <p style="text-align: center; margin-top: 30px; color: #94a3b8; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                Bulacan Polytechnic College • Group 10
            </p>
        </div>
    </div>

    <?php if (!Helper::isAdmin()): ?>
    <div class="card" style="background: #f8fafc; border: 1px dashed #cbd5e1; box-shadow: none;">
        <div class="card-body" style="text-align: center; padding: 40px;">
            <h3 style="margin-bottom: 10px; font-weight: 800; color: #1e293b !important;">Technical Support</h3>
            <p style="color: #64748b !important; max-width: 500px; margin: 0 auto 25px; font-size: 0.95rem;">
                Experiencing issues with your fingerprint registration or attendance logs?
            </p>
            <a href="contact.php" class="btn btn-primary" style="padding: 12px 35px; font-weight: 700; border-radius: 50px;">Open Support Ticket</a>
        </div>
    </div>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/partials/footer.php'; ?>