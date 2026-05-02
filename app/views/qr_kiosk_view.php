<?php
$currentYear = date("Y");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BPC QR Attendance Kiosk</title>
    
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/display.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        /* Shared kiosk styles */
        .qr-kiosk-wrapper {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.5);
            max-width: 650px;
            width: 90%;
            z-index: 5;
            transition: all 0.5s ease;
            margin-top: 4rem;
        }

        #reader {
            width: 100%;
            height: 470px;
            background: #747474;
            border-radius: 15px;
            overflow: hidden;
            border: 4px solid #f1f5f9;
        }

        /* Floating button to return to Fingerprint Mode */
        .fingerprint-access-btn {
            position: fixed;
            bottom: 80px; 
            right: 30px;
            width: 70px;
            height: 70px;
            background: #059669;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .fingerprint-access-btn:hover {
            background: #047857;
            transform: scale(1.1) rotate(-15deg);
        }

        /* Result Card Overlay (Match display_view.php) */
        .scan-card {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -40%) scale(0.95);
            background: white;
            border-radius: 14px;
            box-shadow: 0 20px 50px rgba(161, 161, 161, 0.3); 
            width: 80%;
            max-width: 500px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 4rem;
            border: 6px solid #10b981; 
            z-index: 20;
        }

        .scan-card.show {
            opacity: 1;
            visibility: visible;
            transform: translate(-50%, -50%) scale(1); 
        }

        #otpSection input {
            font-size: 2.5rem;
            text-align: center;
            padding: 10px;
            width: 80%;
            border: 2px solid #6366f1;
            border-radius: 12px;
            margin-bottom: 20px;
            letter-spacing: 10px;
        }
    </style>
</head>
<body class="display-body">

    <div class="time-date-bar">
        <div id="currentDate"><i class="fa-regular fa-calendar-days"></i>---</div>
        <div id="currentTime"><i class="fa-regular fa-clock"></i>--:-- --</div>
    </div>

    <div class="slideshow-bg" id="slideshowBg"></div>

    <div class="display-container">
        <div class="qr-kiosk-wrapper" id="scannerUI">
            <div style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-qrcode" style="font-size: 4rem; color: #f59e0b; margin-bottom: 10px;"></i>
                <h1 style="font-size: 2.8rem; font-weight: 800; color: #1e293b; margin: 0;">QR Kiosk</h1>
                <p style="font-size: 1.2rem; color: #64748b; margin-top: 5px;">Place your code in front of the camera</p>
            </div>
            
            <div id="reader"></div>
        </div>

        <div class="scan-card" id="attendanceModal">
            <div class="icon-badge" id="modalIcon"></div>
            <div class="user-name" id="modalName">---</div>
            <div class="scan-status" id="modalStatus">---</div>
            
            <div id="otpSection" style="display:none; width: 100%;">
                <p style="color: #64748b; margin-bottom: 15px;">Check email for verification code</p>
                <input type="text" id="otpInput" placeholder="••••••" maxlength="6">
                <button onclick="verifyOTP()" class="btn-close-modal" style="background: #6366f1; width: 100%;">Verify</button>
            </div>

            <div class="time-date" id="modalTimeDetails">
                <span id="modalTime">--:-- --</span> | <span id="modalDate">---</span>
            </div>
            
            <button type="button" onclick="closeAttendanceModal()" class="btn-close-modal" style="margin-top: 2rem;">Close</button>
        </div>
    </div>

    <a href="display.php?key=<?= urlencode(KIOSK_SECRET_KEY) ?>" class="fingerprint-access-btn" title="Switch to Fingerprint">
        <i class="fa-solid fa-fingerprint"></i>
    </a>

    <footer class="display-footer">
        &copy; Bulacan Polytechnic College (<?php echo $currentYear; ?>)
    </footer>

    <audio id="beepSuccess" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>
    <audio id="beepError" src="https://assets.mixkit.co/active_storage/sfx/2571/2571-preview.mp3" preload="auto"></audio>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        // --- Shared UI Logic (Clock & Slideshow) ---
        function updateClock() {
            const now = new Date();
            const timeOptions = { hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true };
            const dateOptions = { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' };

            document.getElementById('currentTime').innerHTML = `<i class="fa-regular fa-clock"></i>${now.toLocaleTimeString('en-US', timeOptions)}`;
            document.getElementById('currentDate').innerHTML = `<i class="fa-regular fa-calendar-days"></i>${now.toLocaleDateString('en-US', dateOptions)}`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        const slideshowBg = document.getElementById('slideshowBg');
        const slideshowImages = ['img/background.png', 'img/bpc.jpg', 'img/bpc1.jpg', 'img/bpc2.jpg', 'img/bpc3.jpg', 'img/bpc4.jpg', 'img/bpc5.jpg'];
        let currentSlideIndex = 0;

        function loadSlideshow() {
            slideshowImages.forEach((imgSrc, index) => {
                const imgDiv = document.createElement('div');
                imgDiv.className = 'slideshow-image';
                imgDiv.style.backgroundImage = `url(${imgSrc})`;
                imgDiv.setAttribute('data-index', index);
                slideshowBg.appendChild(imgDiv);
            });
            document.querySelector('.slideshow-image[data-index="0"]').classList.add('active');
        }

        function nextSlide() {
            document.querySelector(`.slideshow-image.active`).classList.remove('active');
            currentSlideIndex = (currentSlideIndex + 1) % slideshowImages.length;
            document.querySelector(`.slideshow-image[data-index="${currentSlideIndex}"]`).classList.add('active');
        }
        loadSlideshow();
        setInterval(nextSlide, 5000);

        // --- QR Scanning Logic ---
        var html5QrcodeScanner;
        var autoCloseTimer;
        var currentUserId = null;

        function closeAttendanceModal() {
            if (autoCloseTimer) clearTimeout(autoCloseTimer);
            document.getElementById('attendanceModal').classList.remove('show');
            document.getElementById('otpSection').style.display = "none";
            document.getElementById('otpInput').value = "";
            document.getElementById('scannerUI').style.opacity = "1";
            if (html5QrcodeScanner) html5QrcodeScanner.render(onScanSuccess);
        }

        function showAttendanceModal(data, isOtpRequest = false) {
            const modal = document.getElementById('attendanceModal');
            const icon = document.getElementById('modalIcon');
            const name = document.getElementById('modalName');
            const status = document.getElementById('modalStatus');
            
            document.getElementById('scannerUI').style.opacity = "0.2";
            name.textContent = data.user_name || "Faculty Member";
            status.textContent = data.message;
            
            const now = new Date();
            document.getElementById('modalTime').textContent = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            document.getElementById('modalDate').textContent = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

            icon.className = 'icon-badge';
            status.className = 'scan-status';
            document.getElementById('modalTimeDetails').style.display = isOtpRequest ? "none" : "block";

            if (data.success) {
                if (!isOtpRequest) document.getElementById('beepSuccess').play().catch(e=>{});
                icon.innerHTML = isOtpRequest ? '<i class="fa-solid fa-envelope-shield"></i>' : '<i class="fa-solid fa-check"></i>';
                const type = data.message.toLowerCase().includes('out') ? 'time-out' : 'time-in';
                icon.classList.add(isOtpRequest ? 'error' : type);
                status.classList.add(isOtpRequest ? 'error' : type);
            } else {
                document.getElementById('beepError').play().catch(e=>{});
                icon.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                icon.classList.add('error');
                status.classList.add('error');
            }

            modal.classList.add('show');
            if (!isOtpRequest) autoCloseTimer = setTimeout(closeAttendanceModal, 6000);
            else setTimeout(() => document.getElementById('otpInput').focus(), 100);
        }

        function onScanSuccess(decodedText) {
            html5QrcodeScanner.clear();
            fetch('api/record_attendance_qr.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ qr_token: decodedText })
            })
            .then(r => r.json())
            .then(data => {
                if (data.requires_otp) {
                    currentUserId = data.user_id;
                    document.getElementById('otpSection').style.display = "block";
                    showAttendanceModal(data, true);
                } else showAttendanceModal(data);
            })
            .catch(err => showAttendanceModal({ success: false, message: "Server Error" }));
        }

        function verifyOTP() {
            const otp = document.getElementById('otpInput').value;
            if(!otp) return;
            fetch('api/verify_otp.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: currentUserId, otp_code: otp })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('otpSection').style.display = "none";
                    showAttendanceModal(data);
                } else alert(data.message);
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 15, qrbox: 300 });
            html5QrcodeScanner.render(onScanSuccess);
        });
    </script>
</body>
</html>