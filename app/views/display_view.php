<?php
$currentYear = date("Y");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BPC Attendance Display</title>
    
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/display.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
<style>
.qr-access-btn {
    position: fixed;
    bottom: 80px; 
    right: 30px;
    width: 70px;
    height: 70px;
    background: #f59e0b;
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

.qr-access-btn:hover {
    background: #d97706;
    transform: scale(1.1) rotate(15deg);
    box-shadow: 0 8px 25px rgba(0,0,0,0.4);
}

.qr-access-btn:active {
    transform: scale(0.95);
}

.modal-overlay {
    position: fixed; 
    inset: 0; 
    background: rgba(0, 0, 0, 0.85);
    z-index: 100000; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    backdrop-filter: blur(10px);
}

.pin-card {
    background: white; 
    padding: 2.5rem; 
    border-radius: 20px; 
    width: 90%; 
    max-width: 380px;
    text-align: center; 
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
}

.pin-header i { 
    font-size: 2.5rem; 
    color: #6366f1; 
    margin-bottom: 1rem; 
}

.pin-header h3 { 
    font-size: 1.5rem; 
    font-weight: 800; 
    color: #1e293b; 
    margin: 0; 
}

.pin-header p { 
    color: #64748b; 
    font-size: 0.9rem; 
    margin-top: 5px; 
}

#adminPinInput {
    width: 100%; 
    font-size: 2rem; 
    text-align: center; 
    letter-spacing: 12px;
    padding: 12px; 
    border: 2px solid #e2e8f0; 
    border-radius: 12px; 
    margin-top: 20px;
    outline: none;
}

.pin-error { 
    color: #ef4444; 
    font-size: 0.85rem; 
    font-weight: 600; 
    margin-top: 10px; 
}

.pin-actions { 
    display: flex; 
    gap: 12px; 
    margin-top: 25px; 
}

.pin-actions button { 
    flex: 1; 
    padding: 12px; 
    border-radius: 10px; 
    font-weight: 700; 
    cursor: pointer; 
    border: none; 
}

.btn-cancel { 
    background: #f1f5f9; 
    color: #475569; 
}

.btn-verify { 
    background: #6366f1; 
    color: white; 
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
        
        <div class="default-state" id="defaultState">
            <div class="logo-icon">
                <i class="fa-solid fa-fingerprint"></i>
            </div>
            <h1>Welcome to BPC</h1>
            <p>Please scan your fingerprint</p>
        </div>

        <div class="scan-card" id="scanCard">
            <div class="icon-badge" id="scanIcon"></div>
            <div class="user-name" id="scanName">---</div>
            <div class="scan-status" id="scanStatus">---</div>
            <div class="time-date">
                <span id="scanTime">--:-- --</span> | <span id="scanDate">---</span>
            </div>
        </div>
    </div>

    <div id="pinModal" class="modal-overlay" style="display: none;">
        <div class="pin-card">
            <div class="pin-header">
                <i class="fa-solid fa-lock"></i>
                <h3>Admin Access</h3>
                <p>Please enter PIN to switch to QR Mode</p>
            </div>
            <div class="pin-input-group">
                <input type="password" id="adminPinInput" placeholder="••••" maxlength="4" autocomplete="off">
                <div class="pin-error" id="pinError" style="display: none;">Incorrect PIN. Try again.</div>
            </div>
            <div class="pin-actions">
                <button onclick="closePinModal()" class="btn-cancel">CANCEL</button>
                <button onclick="verifyPinAndRedirect()" class="btn-verify">VERIFY</button>
            </div>
        </div>
    </div>

    <button type="button" class="qr-access-btn" onclick="openPinModal()" title="Emergency QR Scanner">
        <i class="fa-solid fa-qrcode"></i>
    </button>

<footer class="display-footer">
    &copy; Bulacan Polytechnic College (<?php echo $currentYear; ?>)
</footer>
    
<script>
const scanCard = document.getElementById('scanCard');
const scanIcon = document.getElementById('scanIcon');
const scanName = document.getElementById('scanName');
const scanStatus = document.getElementById('scanStatus');
const scanTime = document.getElementById('scanTime');
const scanDate = document.getElementById('scanDate');
const defaultStateP = document.getElementById('defaultState').querySelector('p');
const currentTimeDisplay = document.getElementById('currentTime');
const currentDateDisplay = document.getElementById('currentDate');
        
let hideCardTimer;

function updateClock() {
    const now = new Date();
    const timeOptions = { hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true };
    const dateOptions = { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' };

    currentTimeDisplay.innerHTML = `<i class="fa-regular fa-clock"></i>${now.toLocaleTimeString('en-US', timeOptions)}`;
    currentDateDisplay.innerHTML = `<i class="fa-regular fa-calendar-days"></i>${now.toLocaleDateString('en-US', dateOptions)}`;
}
    setInterval(updateClock, 1000);
    updateClock();

    const slideshowBg = document.getElementById('slideshowBg');
    const slideshowImages = [
        'img/background.png',
        'img/bpc.jpg',
        'img/bpc1.jpg',
        'img/bpc2.jpg',
        'img/bpc3.jpg',
        'img/bpc4.jpg',
        'img/bpc5.jpg'
    ];
    let currentSlideIndex = 0;
    const slideDuration = 5000;

function loadSlideshow() {
    slideshowImages.forEach((imgSrc, index) => {
        const imgDiv = document.createElement('div');
            imgDiv.className = 'slideshow-image';
            imgDiv.style.backgroundImage = `url(${imgSrc})`;
            imgDiv.setAttribute('data-index', index);
            slideshowBg.appendChild(imgDiv);
        });
    if (slideshowImages.length > 0) {
        document.querySelector('.slideshow-image[data-index="0"]').classList.add('active');
    }
}

function nextSlide() {
const currentSlide = document.querySelector(`.slideshow-image.active`);
    if (currentSlide) {
        currentSlide.classList.remove('active');
    }

    currentSlideIndex = (currentSlideIndex + 1) % slideshowImages.length;
        const nextSlide = document.querySelector(`.slideshow-image[data-index="${currentSlideIndex}"]`);
        if (nextSlide) {
            nextSlide.classList.add('active');
        }
}
        
        if (slideshowImages.length > 1) {
            setInterval(nextSlide, slideDuration);
        }
        loadSlideshow();

function showScanEvent(data) {
    clearTimeout(hideCardTimer);

    scanName.textContent = data.name;
    scanStatus.textContent = data.status;
            
    let scanTimeObj = new Date(); 
    scanTime.textContent = scanTimeObj.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    scanDate.textContent = scanTimeObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

    scanIcon.className = 'icon-badge';
    scanStatus.className = 'scan-status';

    if (data.is_warning) {
        scanIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
        scanIcon.classList.add('warning');
        scanStatus.classList.add('warning');
    } 
    else if (data.status.toLowerCase().includes('time in')) {
        scanIcon.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket"></i>';
        scanIcon.classList.add('time-in');
        scanStatus.classList.add('time-in');
    } 
    else if (data.status.toLowerCase().includes('time out')) {
        scanIcon.innerHTML = '<i class="fa-solid fa-arrow-right-from-bracket"></i>';
        scanIcon.classList.add('time-out');
        scanStatus.classList.add('time-out');
    } 
    else {
        scanIcon.innerHTML = '<i class="fa-solid fa-times-circle"></i>';
        scanIcon.classList.add('error');
        scanStatus.classList.add('error');
    }

    scanCard.classList.add('show');

    hideCardTimer = setTimeout(() => {
        scanCard.classList.remove('show');
    }, 4000);
    }

function recordAttendance(userId) {
    console.log("📤 Recording attendance for user ID:", userId);
    const apiUrl = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '/api/record_attendance.php');

    fetch(apiUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ user_id: userId })
    })
    
    .then(async response => {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error(text || "Server Error");
        }
        })
    .then(data => {
        if (data.success && data.data) {
            showScanEvent(data.data);
        } else {
            showScanEvent({
                name: "System Error",
                status: data.message || "Invalid Response",
                is_warning: true 
            });
            }
        })
    .catch(err => {
        console.error("❌ Debug Details:", err);
        showScanEvent({
            name: "Critical Error",
            status: err.message.substring(0, 20),
            is_warning: true
            });
        });
    }

// WebSocket Connection
function connectWebSocket() {
    const socket = new WebSocket("ws://127.0.0.1:8080/");
        let reconnectTimeout;
        let isConnected = false;

        socket.onopen = () => {
            console.log("✅ Display connected to bridge successfully");
            isConnected = true;
            defaultStateP.textContent = "Please scan your fingerprint";
                
            if (reconnectTimeout) {
                clearTimeout(reconnectTimeout);
            }  
            socket.send(JSON.stringify({ command: "sync_templates" }));
            socket.send(JSON.stringify({ command: "verify_start" }));
            };

            socket.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    if (data.type === "verification_success") {
                        recordAttendance(data.user_id);
                    }
                    else if (data.type === "verification_fail") {
                        showScanEvent({
                            name: "Scan Failed",
                            status: "Finger not recognized",
                        });
                    }
                    else if (data.status === "info") {
                        if (data.message && data.message.includes("Verification active")) {
                            defaultStateP.textContent = "Please scan your fingerprint";
                        }
                    }
                    else if (data.status === "error") {
                        defaultStateP.textContent = "Scanner Error: " + data.message;
                    }
                } catch (e) {
                    console.error("❌ Error parsing WebSocket message:", e);
                }
            };

            socket.onerror = (err) => {
                defaultStateP.textContent = "Scanner service disconnected";
                isConnected = false;
            };

            socket.onclose = (event) => {
                isConnected = false;
                defaultStateP.textContent = "Connection lost. Retrying...";
                reconnectTimeout = setTimeout(() => {
                    connectWebSocket();
                }, 5000);
            };
            
            return socket;
        }

        document.addEventListener('DOMContentLoaded', () => {
            connectWebSocket();
        });

function openPinModal() {
    document.getElementById('pinModal').style.display = "flex";
    document.getElementById('adminPinInput').value = "";
    document.getElementById('pinError').style.display = "none";
    setTimeout(() => document.getElementById('adminPinInput').focus(), 100);
}

function closePinModal() {
    document.getElementById('pinModal').style.display = "none";
}

// Remove the '1111' or '1234' fallback.
// If it's empty, the PIN didn't load from the DB.
const adminPin = "<?php echo (!empty($terminal_pin)) ? addslashes($terminal_pin) : ''; ?>";

function verifyPinAndRedirect() {
    const input = document.getElementById('adminPinInput').value;
    
    // Check if the dynamic PIN actually loaded
    if (adminPin === "") {
        console.error("Access PIN failed to load from database.");
        alert("System Error: Terminal PIN not found.");
        return;
    }

    if (input === adminPin) {
        window.location.href = 'qr_kiosk.php';
    } else {
        document.getElementById('pinError').style.display = "block";
        document.getElementById('adminPinInput').value = "";
    }
}

document.getElementById('adminPinInput')?.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') verifyPinAndRedirect();
});

</script>
</body>
</html>