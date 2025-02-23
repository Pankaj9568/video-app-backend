<?php
require 'config.php';

// Fetch approved videos with counts for likes, comments, and shares
$query = "
    SELECT 
        v.video_id, 
        v.title, 
        v.description, 
        v.video_url, 
        v.created_at,
        COUNT(DISTINCT l.like_id) AS likes_count,
        COUNT(DISTINCT c.comment_id) AS comments_count,
        COUNT(DISTINCT s.share_id) AS shares_count
    FROM videos v
    LEFT JOIN likes l ON v.video_id = l.video_id
    LEFT JOIN comments c ON v.video_id = c.video_id
    LEFT JOIN shares s ON v.video_id = s.video_id
    WHERE v.status = 'approved'
    GROUP BY v.video_id
    ORDER BY v.created_at DESC
";
$stmt = $pdo->prepare($query);
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
        .video-stats {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            font-size: 14px;
            color: #555;
        }
        .video-stats span {
            display: flex;
            align-items: center;
            gap: 5px;
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
                    <?php
                    // Check if the video URL is from YouTube
                    if (preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $video['video_url'], $matches)) {
                        $youtubeVideoId = $matches[4];
                        $embedUrl = "https://www.youtube.com/embed/$youtubeVideoId?fs=0"; // Disable fullscreen
                    ?>
                        <!-- YouTube Embed -->
                        <iframe src="<?php echo $embedUrl; ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    <?php } else { ?>
                        <!-- Local Video -->
                        <video controls>
                            <source src="<?php echo $video['video_url']; ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    <?php } ?>
                    <div class="video-info">
                        <h3><?php echo $video['title']; ?></h3>
                        <p><?php echo $video['description']; ?></p>
                        <p>Uploaded on: <?php echo $video['created_at']; ?></p>
                        <div class="video-stats">
                            <span>❤️ <?php echo $video['likes_count']; ?> Likes</span>
                            <span>💬 <?php echo $video['comments_count']; ?> Comments</span>
                            <span>🔗 <?php echo $video['shares_count']; ?> Shares</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No approved videos to display.</p>
        <?php endif; ?>
    </div>
    <div class="loading" id="loading">Loading videos...</div>

    <!-- Include the JavaScript file -->
    <script src="loadVideos.js"></script>
</body>
</html>