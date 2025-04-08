<?php
require_once 'Database.php';

// Extract storeId and deviceId from URL
$uri = $_SERVER['REQUEST_URI']; 
$parts = explode('/', trim($uri, '/'));  // ['Base_VADD', 'S0100', 'TV1']
$storeId = $parts[1]; // 'S0100'
$deviceId = $parts[2]; // 'TV1'


if (empty($storeId) || empty($deviceId)) {
    die("Invalid URL format. Expected: /Base_VADD/storeId/deviceId");
}

// Get fingerprint (you might want to get this from cookies or local storage)
$uniqueId = $_COOKIE['device_fingerprint'] ?? '';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Verify the device is approved
    $stmt = $conn->prepare("
        SELECT MediaPath 
        FROM approvedevices 
        WHERE StoreId = ? AND DeviceName = ? AND UniqueId = ? AND IsActive = 1
    ");
    $stmt->bind_param("sss", $storeId, $deviceId, $uniqueId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Device not authorized or not found");
    }

    $device = $result->fetch_assoc();
    $mediaPath = $device['MediaPath'];

    // If no custom media path, use default videos
    if (empty($mediaPath)) {
        $videoDir = 'videolar';
        $videos = [];
        
        if (is_dir($videoDir)) {
            foreach (scandir($videoDir) as $file) {
                if ($file !== '.' && $file !== '..') {
                    $videos[] = $videoDir . '/' . $file;
                }
            }
        }
        
        // Shuffle videos if needed
        shuffle($videos);
        $mediaPath = json_encode($videos);
    }

    // Output the video player page
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Video Display</title>
        <style>
            body, html {
                margin: 0;
                padding: 0;
                overflow: hidden;
            }
            #video-player {
                width: 100vw;
                height: 100vh;
                object-fit: contain;
            }
        </style>
    </head>
    <body>
        <video id="video-player" autoplay muted loop></video>
        
        <script>
            const videos = <?= $mediaPath ?>;
            let currentVideoIndex = 0;
            const videoPlayer = document.getElementById('video-player');
            
            function playNextVideo() {
                if (videos.length === 0) return;
                
                videoPlayer.src = videos[currentVideoIndex];
                videoPlayer.play()
                    .catch(e => console.error("Video play error:", e));
                
                currentVideoIndex = (currentVideoIndex + 1) % videos.length;
                videoPlayer.onended = playNextVideo;
            }
            
            // Start playback
            playNextVideo();
        </script>
    </body>
    </html>
    <?php
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>