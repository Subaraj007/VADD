<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Video Access</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@fingerprintjs/fingerprintjs@3/dist/fp.min.js"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Message container -->
    <div id="message"></div>
    
    <!-- Registration Form -->
    <div id="registration-form" style="display: none;">
        <h4 class="text-center mb-3">Please Register Your Device</h4>
        <form id="customer-registration">
            <div class="mb-3">
                <label for="shop-name" class="form-label">Shop Name</label>
                <input type="text" class="form-control" id="shop-name" required>
            </div>
            <div class="mb-3">
                <label for="postal-code" class="form-label">Postal Code</label>
                <input type="text" class="form-control" id="postal-code" required>
            </div>
            <div class="mb-3">
                <label for="device-number" class="form-label">Device Number</label>
                <input type="text" class="form-control" id="device-number" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Submit</button>
        </form>
    </div>
        
        <!-- Fullscreen video container -->
        <div id="video-container" style="display: none;">
            <video id="play-video" autoplay muted></video>
        </div>
    </div>

    <script>
        async function getFingerprint() {
            const fp = await FingerprintJS.load();
            const result = await fp.get();
            return result.visitorId;
        }

        async function verifyDevice() {
            const fingerprint = await getFingerprint();
            const response = await fetch("verify.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ fingerprint })
            });

            const data = await response.json();
            const messageDiv = document.getElementById("message");

            switch (data.status) {
                case "Authorized":
                    messageDiv.style.display = "none"; // Hide message for clean fullscreen
                    document.getElementById("video-container").style.display = "block";
                    enterFullscreen();
                    nextVideo();
                    break;
                case "Unauthorized":
                    messageDiv.innerHTML = `<p class='text-danger'>${data.message || "Access Denied"}</p>`;
                    break;
                case "NewDevice":
                    messageDiv.innerHTML = `<p class='text-warning'>${data.message || "Please register your device"}</p>`;
                    document.getElementById("registration-form").style.display = "block";
                    setupRegistrationForm(fingerprint);
                    break;
                case "Unregistered":
                    messageDiv.innerHTML = `<p class='text-warning'>Please complete your registration</p>`;
                    document.getElementById("registration-form").style.display = "block";
                    setupRegistrationForm(fingerprint);
                    break;
                default:
                    messageDiv.innerHTML = `<p class='text-danger'>Error: ${data.message || "Unknown error"}</p>`;
            }
        }

        function enterFullscreen() {
            const elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.webkitRequestFullscreen) { /* Safari */
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) { /* IE11 */
                elem.msRequestFullscreen();
            }
        }

        function setupRegistrationForm(fingerprint) {
            document.getElementById("customer-registration").addEventListener("submit", async function(e) {
                e.preventDefault();
                
                const shopName = document.getElementById("shop-name").value;
                const postalCode = document.getElementById("postal-code").value;
                const deviceNumber = document.getElementById("device-number").value;
                
                const response = await fetch("register_device.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        fingerprint,
                        shop_name: shopName,
                        postal_code: postalCode,
                        device_number: deviceNumber
                    })
                });
                
                const data = await response.json();
                if (data.status === "success") {
                    alert("Registration successful! Your access will be granted after approval.");
                    window.location.reload();
                } else {
                    alert("Registration failed: " + (data.message || "Unknown error"));
                }
            });
        }

        document.addEventListener("DOMContentLoaded", verifyDevice);

        var videos = <?php
            $video_files = [];
            $video_folder = "videolar";

            if (is_dir($video_folder)) {
                foreach (scandir($video_folder) as $video) {
                    if ($video !== "." && $video !== "..") {
                        $video_files[] = $video_folder . "/" . $video;
                    }
                }
            }
            shuffle($video_files);
            echo json_encode($video_files);
        ?>;

        var currentVideo = 0;
        function nextVideo() {
            var videoPlayer = document.getElementById("play-video");
            videoPlayer.src = videos[currentVideo];
            videoPlayer.play();
            currentVideo = (currentVideo + 1) % videos.length;
            videoPlayer.addEventListener('ended', nextVideo, false);
        }

        // Auto-resize video to fullscreen dimensions
        function resizeVideo() {
            const video = document.getElementById("play-video");
            video.style.width = window.innerWidth + 'px';
            video.style.height = window.innerHeight + 'px';
        }

        window.addEventListener('resize', resizeVideo);
        window.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                // If user exits fullscreen, re-enter it
                enterFullscreen();
            }
        });
    </script>
</body>
</html>