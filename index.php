<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@fingerprintjs/fingerprintjs@3/dist/fp.min.js?ver=<?= time() ?>"></script>
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        #registration-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        #video-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: black;
            z-index: 1000;
        }
        #play-video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .hidden {
            display: none !important;
        }
    </style>
</head>
<body>
    <div id="registration-container">
        <div id="registration-form">
            <h4 class="text-center mb-4">Please Register Your Device</h4>
            <form id="device-registration">
                <div class="mb-3">
                    <label for="shop-select" class="form-label">Shop Name</label>
                    <select class="form-select" id="shop-select" required>
                        <option value="">Select your shop</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="device-select" class="form-label">Device Name</label>
                    <select class="form-select" id="device-select" required disabled>
                        <option value="">Select your device</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register Device</button>
            </form>
        </div>
        <div id="status-message" class="mt-3"></div>
    </div>

    <div id="video-container">
        <video id="play-video" autoplay muted></video>
    </div>

    <script>
        // Global variables
        let currentVideoIndex = 0;
        let videoPlaylist = [];
        let deviceApprovalCheckInterval = null;

        async function getFingerprint() {
            try {
                const fp = await FingerprintJS.load();
                const result = await fp.get();
                return result.visitorId;
            } catch (error) {
                console.error("Fingerprint error:", error);
                return "error-"+Math.random().toString(36).slice(2);
            }
        }

        async function loadShops() {
            try {
                const response = await fetch('get_shops.php');
                return await response.json();
            } catch (error) {
                console.error('Error loading shops:', error);
                return [];
            }
        }

        async function loadDevices(storeId) {
            try {
                const response = await fetch(`get_devices.php?storeId=${storeId}`);
                return await response.json();
            } catch (error) {
                console.error('Error loading devices:', error);
                return [];
            }
        }

        async function registerDevice(uniqueId, deviceId, storeId) {
            try {
                const response = await fetch('register_device.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        UniqueId: uniqueId,
                        DeviceId: deviceId,
                        StoreId: storeId 
                    })
                });
                return await response.json();
            } catch (error) {
                console.error('Registration error:', error);
                return { status: 'error', message: 'Network error' };
            }
        }

        async function checkApprovalStatus(uniqueId) {
            try {
                const response = await fetch('verify.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ UniqueId: uniqueId })
                });
                return await response.json();
            } catch (error) {
                console.error('Approval check error:', error);
                return { status: 'error' };
            }
        }

        function showStatusMessage(type, message) {
            const statusDiv = document.getElementById('status-message');
            statusDiv.innerHTML = `
                <div class="alert alert-${type}">
                    ${message}
                </div>
            `;
        }

        function hideRegistrationForm() {
            document.getElementById('registration-form').classList.add('hidden');
        }

        function playNextVideo() {
            const videoPlayer = document.getElementById('play-video');
            if (videoPlaylist.length === 0) return;

            videoPlayer.src = videoPlaylist[currentVideoIndex];
            videoPlayer.play()
                .catch(error => console.error("Error playing video:", error));

            videoPlayer.onended = () => {
                currentVideoIndex = (currentVideoIndex + 1) % videoPlaylist.length;
                playNextVideo();
            };
        }

        function startVideoPlayback(videos) {
            videoPlaylist = videos;
            if (videoPlaylist.length > 0) {
                document.getElementById('video-container').style.display = 'block';
                document.getElementById('registration-container').classList.add('hidden');
                playNextVideo();
            }
        }

        async function handleApprovedDevice(storeId, deviceId, uniqueId) {
            // Set cookie before redirect (30 day expiry)
            document.cookie = `device_fingerprint=${encodeURIComponent(uniqueId)}; path=/; max-age=2592000`;
            // Redirect to subdomain URL
            window.location.href = `http://localhost/Final_VADD/${storeId}/${deviceId}`;
        }

        document.addEventListener('DOMContentLoaded', async () => {
            const uniqueId = await getFingerprint();
            console.log('Device fingerprint:', uniqueId);

            // Check initial status
            const status = await checkApprovalStatus(uniqueId);
            
            if (status.status === 'Authorized') {
                // Device is approved - redirect to subdomain
                handleApprovedDevice(status.storeId, status.deviceId, uniqueId);
                return;
            } else if (status.status === 'Pending') {
                // Device registered but pending approval
                hideRegistrationForm();
                showStatusMessage('warning', 'Device registered successfully! Waiting for approval.');
                
                // Set cookie while waiting for approval
                document.cookie = `device_fingerprint=${encodeURIComponent(uniqueId)}; path=/; max-age=2592000`;
                
                // Start checking for approval periodically
                deviceApprovalCheckInterval = setInterval(async () => {
                    const newStatus = await checkApprovalStatus(uniqueId);
                    if (newStatus.status === 'Authorized') {
                        clearInterval(deviceApprovalCheckInterval);
                        handleApprovedDevice(newStatus.storeId, newStatus.deviceId, uniqueId);
                    }
                }, 10000); // Check every 10 seconds
                return;
            }

            // Load shops for new registration
            const shops = await loadShops();
            const shopSelect = document.getElementById('shop-select');
            
            shops.forEach(shop => {
                const option = document.createElement('option');
                option.value = shop.StoreId;
                option.textContent = shop.StoreName;
                shopSelect.appendChild(option);
            });

            // Handle shop selection change
            shopSelect.addEventListener('change', async (e) => {
                if (e.target.value) {
                    const devices = await loadDevices(e.target.value);
                    const deviceSelect = document.getElementById('device-select');
                    
                    deviceSelect.innerHTML = '<option value="">Select your device</option>';
                    deviceSelect.disabled = false;
                    
                    devices.forEach(device => {
                        const option = document.createElement('option');
                        option.value = device.Id;
                        option.textContent = device.DeviceName;
                        deviceSelect.appendChild(option);
                    });
                }
            });

            // Handle form submission
            document.getElementById('device-registration').addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const shopId = document.getElementById('shop-select').value;
                const deviceId = document.getElementById('device-select').value;
                
                if (!shopId || !deviceId) {
                    showStatusMessage('danger', 'Please select both shop and device');
                    return;
                }
                
                const result = await registerDevice(uniqueId, deviceId, shopId);
                
                if (result.status === 'success') {
                    // Set cookie immediately after successful registration
                    document.cookie = `device_fingerprint=${encodeURIComponent(uniqueId)}; path=/; max-age=2592000`;
                    
                    hideRegistrationForm();
                    showStatusMessage('success', 'Device registered successfully! Waiting for approval.');
                    
                    // Start checking for approval periodically
                    deviceApprovalCheckInterval = setInterval(async () => {
                        const newStatus = await checkApprovalStatus(uniqueId);
                        if (newStatus.status === 'Authorized') {
                            clearInterval(deviceApprovalCheckInterval);
                            handleApprovedDevice(newStatus.storeId, newStatus.deviceId, uniqueId);
                        }
                    }, 10000); // Check every 10 seconds
                } else {
                    showStatusMessage('danger', `Error: ${result.message || 'Registration failed'}`);
                }
            });
        });
    </script>
</body>
</html>