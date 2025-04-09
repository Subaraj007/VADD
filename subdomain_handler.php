<?php
require_once 'Database.php';

// Extract storeId and deviceId from URL
$uri = $_SERVER['REQUEST_URI']; 
$parts = explode('/', trim($uri, '/'));  // ['Base_VADD', 'S0100', 'TV1']
$storeId = $parts[1] ?? '';
$deviceId = $parts[2] ?? '';

if (empty($storeId) || empty($deviceId)) {
    die("Invalid URL format. Expected: /Base_VADD/storeId/deviceId");
}

// Get fingerprint (you might want to get this from cookies or local storage)
$uniqueId = $_COOKIE['device_fingerprint'] ?? '';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Get all approved media for the device
    $stmt = $conn->prepare("
        SELECT MediaPath 
        FROM approvedevices 
        WHERE StoreId = ? AND DeviceName = ? AND UniqueId = ? AND IsActive = 1
    ");
    $stmt->bind_param("sss", $storeId, $deviceId, $uniqueId);
    $stmt->execute();
    $result = $stmt->get_result();

    $videoList = [];

    while ($row = $result->fetch_assoc()) {
        if (!empty($row['MediaPath'])) {
            $videoList[] = $row['MediaPath'];
        }
    }

            // If no custom videos, use default video
        if (empty($videoList)) {
            $videoPathPrefix = '/Base_VADD/videolar'; // Absolute path
            $defaultVideo = '1.mp4'; // Default video filename
            
            // Check if default video exists
            $videoDir = 'videolar';
            $defaultVideoPath = $videoDir . '/' . $defaultVideo;
            
            if (file_exists($defaultVideoPath)) {
                $videoList[] = $videoPathPrefix . '/' . $defaultVideo; // Use full URL path
            }
            // No need for shuffle since there's only one video
        
        shuffle($videoList); // Optional
    }

    $mediaPathJson = json_encode($videoList, JSON_UNESCAPED_SLASHES);


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
                background-color: black;
            }
            #video-player {
                width: 100vw;
                height: 100vh;
                object-fit: contain;
                background-color: black;
            }
        </style>
    </head>
    <body>
        <video id="video-player" autoplay muted></video>

        <script>
            const videos = <?= $mediaPathJson ?>;
            let currentVideoIndex = 0;
            const videoPlayer = document.getElementById('video-player');

            function playNextVideo() {
                if (videos.length === 0) return;

                videoPlayer.src = videos[currentVideoIndex];
                videoPlayer.load();
                videoPlayer.play().catch(e => console.error("Video play error:", e));

                currentVideoIndex = (currentVideoIndex + 1) % videos.length;
            }

            videoPlayer.onended = playNextVideo;
            playNextVideo();
            console.log("Videos array:", videos);

        </script>
    </body>
    </html>
    <?php
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
