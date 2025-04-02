<?php
require_once 'Database.php';

// Get parameters from URL
$storeId = $_GET['storeId'] ?? '';
$deviceId = $_GET['deviceId'] ?? '';

// Get fingerprint from cookie (set this in your JavaScript after registration)
$uniqueId = $_COOKIE['device_fingerprint'] ?? '';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Verify device approval
    $stmt = $conn->prepare("
        SELECT MediaPath 
        FROM approvedevices 
        WHERE StoreId = ? 
        AND DeviceName = ? 
        AND UniqueId = ? 
        AND IsActive = 1
    ");
    $stmt->bind_param("sss", $storeId, $deviceId, $uniqueId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Device not authorized. Please contact support.");
    }

    $device = $result->fetch_assoc();
    $mediaContent = json_decode($device['MediaPath'] ?? '', true) ?: [];

    // Fallback to default videos if no custom path
    if (empty($mediaContent)) {
        $videoDir = 'videolar';
        if (is_dir($videoDir)) {
            foreach (scandir($videoDir) as $file) {
                if (!in_array($file, ['.', '..'])) {
                    $mediaContent[] = $videoDir.'/'.$file;
                }
            }
        }
    }

    // Shuffle if you want random playback
    shuffle($mediaContent);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Video Display</title>
    <style>
        body, html { margin:0; padding:0; overflow:hidden; background:#000; }
        #video-player { 
            width:100vw; height:100vh; 
            object-fit:contain; 
        }
    </style>
</head>
<body>
    <video id="video-player" autoplay muted></video>
    
    <script>
        const videos = <?= json_encode($mediaContent) ?>;
        let currentVideo = 0;
        const player = document.getElementById('video-player');
        
        function playNext() {
            if (videos.length === 0) {
                console.error("No videos available");
                return;
            }
            
            player.src = videos[currentVideo];
            player.play()
                .catch(e => {
                    console.error("Playback failed:", e);
                    setTimeout(playNext, 2000);
                });
            
            currentVideo = (currentVideo + 1) % videos.length;
        }
        
        player.addEventListener('ended', playNext);
        player.addEventListener('error', () => setTimeout(playNext, 2000));
        
        // Start playback
        playNext();
    </script>
</body>
</html>
<?php
} catch (Exception $e) {
    die("System error. Please try again later.");
}
?>