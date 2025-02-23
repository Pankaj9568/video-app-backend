<?php
require 'config.php';

// Fetch approved videos
$stmt = $pdo->prepare("SELECT video_id, title, description, video_url, created_at FROM videos WHERE status = 'approved' ORDER BY created_at DESC");
$stmt->execute();
$approvedVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Video Sharing App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .video-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px;
        }
        .video-card {
            width: 100%;
            max-width: 600px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .video-card iframe {
            width: 100%;
            height: 315px;
            border: none;
        }
        .video-info {
            padding: 10px;
        }
        .video-info h3 {
            margin: 0;
            font-size: 18px;
        }
        .video-info p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }
        .loading {
            text-align: center;
            padding: 20px;
            font-size: 16px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="video-container" id="video-container">
        <?php if (count($approvedVideos) > 0): ?>
            <?php foreach ($approvedVideos as $video): ?>
                <div class="video-card">
                    <!-- Embed YouTube Video -->
                    <?php
                    // Extract YouTube video ID from the URL
                    $videoUrl = $video['video_url'];
                    if (preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $videoUrl, $matches)) {
                        $youtubeVideoId = $matches[4];
                        $embedUrl = "https://www.youtube.com/embed/$youtubeVideoId?fs=0"; // Disable fullscreen
                    ?>
                        <iframe src="<?php echo $embedUrl; ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                    <?php } else { ?>
                        <p>Invalid YouTube URL.</p>
                    <?php } ?>
                    <div class="video-info">
                        <h3><?php echo $video['title']; ?></h3>
                        <p><?php echo $video['description']; ?></p>
                        <p>Uploaded on: <?php echo $video['created_at']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No approved videos to display.</p>
        <?php endif; ?>
    </div>
</body>
</html>